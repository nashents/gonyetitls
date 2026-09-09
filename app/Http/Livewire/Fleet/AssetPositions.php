<?php

namespace App\Http\Livewire\Fleet;

use App\Models\AssetPositionLog;
use App\Models\Horse;
use App\Models\Vehicle;
use App\Services\Fleet\FleetPositionResolver;
use App\Services\Fleet\ReverseGeocoder;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * "Asset Positions" board: one row per fleet asset (Horse/truck OR Vehicle —
 * a trip can be pulled by either), combining fleet/trip identity (Trip,
 * driver, origin/destination, customer, cargo, references — all real Gonyeti
 * data) with live position + dwell-time/distance analytics sourced from
 * whichever tracking provider(s) the company has configured.
 *
 * Horses and Vehicles are two separate Eloquent models with no shared table,
 * so they're queried independently, tagged with `asset_type`, merged into
 * one collection, sorted/paginated in PHP (Eloquent can't natively paginate
 * a UNION across different models).
 *
 * Position comes from App\Services\Fleet\FleetPositionResolver (same
 * provider concerns App\Http\Livewire\Fleet\LiveMap uses, but keyed by an
 * "asset key" — "horse:{id}" / "vehicle:{id}"). Dwell-time/24h/48h distance
 * come from asset_position_logs, populated by the fleet:log-asset-positions
 * scheduled command — horses only for now, since that table has no
 * vehicle_id column yet.
 */
class AssetPositions extends Component
{
    use WithPagination;

    protected $listeners = ['tripNoteAdded' => '$refresh'];

    protected $paginationTheme = 'bootstrap';

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-custom';
    }

    public $search;
    public $perPage = 25;
    public $sortField = 'fleet_number';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search'        => ['except' => ''],
        'perPage'       => ['except' => 25],
        'sortField'     => ['except' => 'fleet_number'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    /** Toggles asc/desc when clicking the same column again, matching the standard sortable-header convention. */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function getTrackingEnabledProperty(): bool
    {
        return IntegrationGate::enabledForUserType('tracking');
    }

    protected function currentCompanyId(): ?int
    {
        $user = Auth::user();

        return optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
    }

    /** Shared nested eager-loads for the current trip — identical whether it's pulled by a Horse or a Vehicle. */
    protected function tripEagerLoads(): \Closure
    {
        return function ($q) {
            $q->withCount('trip_notes')->with([
                'latestNote.user:id,name,surname',
                'trailers.trailer_type:id,name',
                'driver.employee:id,name,surname',
                'loading_point:id,name',
                'offloading_point:id,name',
                'customer:id,name',
                'consignee:id,name',
                'cargo:id,name,type',
                'units_of_measure:id,name,abbreviation',
                'transport_orders:id,transport_order_number,custom_ref,cargo_details',
                'route:id,name',
                // Not column-restricted: latestOfMany()'s join makes a
                // restricted trip_id column ambiguous in the outer select
                // (SQLSTATE 23000 "trip_id ... ambiguous").
                'pod',
            ]);
        };
    }

    /** Same search rule applied to both the Horse and the Vehicle query. */
    protected function applySearch($query): void
    {
        if (! $this->search) {
            return;
        }

        $search = '%' . $this->search . '%';

        $query->where(function ($q) use ($search) {
            $q->where('registration_number', 'like', $search)
                ->orWhere('fleet_number', 'like', $search)
                ->orWhereHas('latestTrip', function ($tq) use ($search) {
                    $tq->where('trip_number', 'like', $search)
                        ->orWhereHas('driver.employee', function ($eq) use ($search) {
                            $eq->where('name', 'like', $search)->orWhere('surname', 'like', $search);
                        })
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('name', 'like', $search);
                        })
                        ->orWhereHas('offloading_point', function ($oq) use ($search) {
                            $oq->where('name', 'like', $search);
                        });
                });
        });
    }

    public function render()
    {
        $horseQuery = Horse::query()->where('archive', 0)->with([
            'transporter:id,name',
            'currentAssignment.driver.employee:id,name,surname',
            'latestTrip' => $this->tripEagerLoads(),
        ]);
        $this->applySearch($horseQuery);

        $vehicleQuery = Vehicle::query()->where('archive', 0)->with([
            'transporter:id,name',
            'latestTrip' => $this->tripEagerLoads(),
        ]);
        $this->applySearch($vehicleQuery);

        $assets = $horseQuery->get()->each(fn ($h) => $h->asset_type = 'horse')
            ->concat($vehicleQuery->get()->each(fn ($v) => $v->asset_type = 'vehicle'));

        $sortField = in_array($this->sortField, ['registration_number', 'fleet_number']) ? $this->sortField : 'fleet_number';
        $assets = $assets->sortBy(
            fn ($asset) => strtolower((string) $asset->{$sortField}),
            SORT_REGULAR,
            $this->sortDirection === 'desc'
        )->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $assets->forPage($page, $this->perPage)->values();
        $assetsPage = new LengthAwarePaginator($pageItems, $assets->count(), $this->perPage, $page, [
            'path'  => request()->url(),
            'query' => request()->query(),
        ]);

        $companyId = $this->currentCompanyId();

        $positions = $this->trackingEnabled
            ? app(FleetPositionResolver::class)->resolve($companyId)
            : collect();

        // Reverse-geocode only the positions actually visible on this page
        // (cached per-coordinate — see ReverseGeocoder), not the whole
        // company-wide set, to keep this to a handful of API calls per load.
        $pageAssetKeys = $pageItems->map(fn ($asset) => $asset->asset_type . ':' . $asset->id);
        $geocoder = app(ReverseGeocoder::class);
        $positions = $positions->map(function ($position) use ($pageAssetKeys, $geocoder) {
            if (! $pageAssetKeys->contains($position['asset_key'])) {
                return $position;
            }

            $position['address'] = $geocoder->resolve($position['lat'], $position['lng']);

            return $position;
        });

        // Map markers for every tracked asset company-wide (not just the
        // current page), grouped by Owner (transporter) for the legend/marker
        // colour — the real Gonyeti equivalent of the inspiration tool's
        // fleet-group legend.
        $horseLabels = Horse::whereIn('id', $positions->where('asset_type', 'horse')->pluck('local_id'))
            ->with('transporter:id,name')
            ->get(['id', 'registration_number', 'fleet_number', 'transporter_id'])
            ->keyBy('id');
        $vehicleLabels = Vehicle::whereIn('id', $positions->where('asset_type', 'vehicle')->pluck('local_id'))
            ->with('transporter:id,name')
            ->get(['id', 'registration_number', 'fleet_number', 'transporter_id'])
            ->keyBy('id');

        $mapMarkers = $positions->map(function ($position) use ($horseLabels, $vehicleLabels) {
            $labels = $position['asset_type'] === 'horse' ? $horseLabels : $vehicleLabels;
            $asset = $labels->get($position['local_id']);

            return [
                'label'       => $asset?->fleet_number ?: $asset?->registration_number ?: ('#' . $position['local_id']),
                'group'       => $asset?->transporter?->name ?: 'Other',
                'source'      => $position['source'],
                'latitude'    => $position['lat'],
                'longitude'   => $position['lng'],
                'last_update' => $position['observed_at'],
            ];
        })->values();

        // One batched query per asset type for every visible row's
        // dwell/distance history, instead of a query per row.
        $pageHorseIds = $pageItems->where('asset_type', 'horse')->pluck('id');
        $logsByHorse = AssetPositionLog::whereIn('horse_id', $pageHorseIds)
            ->where('recorded_at', '>=', now()->subDays(2))
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('horse_id');

        $pageVehicleIds = $pageItems->where('asset_type', 'vehicle')->pluck('id');
        $logsByVehicle = AssetPositionLog::whereIn('vehicle_id', $pageVehicleIds)
            ->where('recorded_at', '>=', now()->subDays(2))
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('vehicle_id');

        $analytics = $pageItems->mapWithKeys(function ($asset) use ($logsByHorse, $logsByVehicle) {
            $key = $asset->asset_type . ':' . $asset->id;
            $logsById = $asset->asset_type === 'horse' ? $logsByHorse : $logsByVehicle;

            $logs = $logsById->get($asset->id, collect());

            return [$key => $logs->isEmpty() ? null : AssetPositionLog::summarize($logs)];
        });

        return view('livewire.fleet.asset-positions', [
            'horses'     => $assetsPage,
            'positions'  => $positions,
            'analytics'  => $analytics,
            'mapMarkers' => $mapMarkers,
        ]);
    }
}
