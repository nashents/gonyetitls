<?php

namespace App\Http\Livewire\Shifts;

use App\Models\Account;
use App\Models\Cargo;
use App\Models\Cluster;
use App\Models\Container;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\ExchangeRate;
use App\Models\Horse;
use App\Models\LoadingPoint;
use App\Models\Location;
use App\Models\Measurement;
use App\Models\OffloadingPoint;
use App\Models\Team;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Work;
use App\Rules\UniqueReference;
use App\Services\Cartrack\CartrackSyncService;
use App\Services\EzyTrack\EzyTrackSyncService;
use App\Services\Shifts\BulkShiftService;
use App\Support\RehandlingHourMeter;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Bulk Shifts (Shifts > Bulk Shifts): one shift per truck/vehicle, all on the
 * same date, type and purpose. Each card holds everything Add Shift captures -
 * driver, duty times, mileage, trips or rehandling works, comments and the
 * fuel order - prefilled from the defaults at the top, then saved together by
 * BulkShiftService.
 */
class BulkCreate extends Component
{
    const TRIP_DEFAULTS = ['haulage_type', 'cargo_id', 'weight', 'rate', 'measurement', 'from', 'to', 'loading_point_id', 'offloading_point_id'];
    const WORK_DEFAULTS = ['work_id', 'location_id', 'cargo_id', 'weight'];
    const FUEL_DEFAULTS = ['source', 'container_id', 'category', 'account_id', 'fuel_type', 'is_full_tank', 'allocation_to', 'currency_id', 'exchange_rate', 'unit_price'];

    // Shared shift details
    public $for = 'Trips';
    public $type;
    public $date;
    public $shift_start_time;
    public $shift_end_time;
    public $team_id;
    public $customer_id;

    // Defaults for new trucks
    public $tripsPerTruck = 1;
    public $tripDefaults = [];
    public $worksPerTruck = 1;
    public $workDefaults = [];
    public $fuelForAll = false;
    public $fuelDefaults = [];

    // Per truck: key, equipment, horse_id, vehicle_id, label, transporter_id, transporter_name, driver_id, trailer_ids,
    // shift_start_time, shift_end_time, open_mileage, close_mileage, live_source, calculated_mileage, comments,
    // depart_workshop_time, arrive_location_time, depart_location_time, arrive_workshop_time,
    // trips[], works[], fuel_order, fuel{}
    public $rows = [];

    // Truck picker
    public $equipment = 'Horse';
    public $truckSearch;
    public $pickerTransporterId;
    public $picked = [];

    public $company;

    protected $listeners = ['openBulkShifts' => 'open'];

    public function mount()
    {
        $this->company = optional(Auth::user()->employee)->company;
    }

    public function open()
    {
        $this->reset(['type', 'shift_start_time', 'shift_end_time', 'customer_id', 'rows', 'truckSearch', 'pickerTransporterId', 'picked', 'fuelForAll']);
        $this->for = 'Trips';
        $this->equipment = 'Horse';
        $this->date = now()->format('Y-m-d');
        $this->team_id = optional(optional(Auth::user()->employee)->teams)->first()?->id;
        $this->tripsPerTruck = 1;
        $this->worksPerTruck = 1;
        $this->tripDefaults = array_fill_keys(self::TRIP_DEFAULTS, '');
        $this->tripDefaults['weight'] = $this->company->default_weight ?? '';
        $this->tripDefaults['rate'] = $this->company->default_rate ?? '';
        $this->workDefaults = array_fill_keys(self::WORK_DEFAULTS, '');
        $this->fuelDefaults = array_merge(array_fill_keys(self::FUEL_DEFAULTS, ''), [
            'source' => 'station',
            'fuel_type' => 'Diesel',
            'currency_id' => $this->company->currency_id ?? '',
        ]);
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-bulkShiftsModal');
    }

    public function getCargoTypesProperty()
    {
        return Cargo::pluck('type', 'id');
    }

    public function isForeign($currencyId): bool
    {
        return $currencyId && $this->company && $currencyId != $this->company->currency_id;
    }

    private function defaultExchangeRate($currencyId)
    {
        if (! $this->isForeign($currencyId)) {
            return '';
        }

        return ExchangeRate::where('currency_id', $currencyId)->where('status', 1)->where('expiry', '>', Carbon::today())->value('exchange_rate') ?? '';
    }

    // ---- shared field changes ----

    public function updatedShiftStartTime($value)
    {
        foreach ($this->rows as $i => $row) {
            $this->rows[$i]['shift_start_time'] = $value;
        }
    }

    public function updatedShiftEndTime($value)
    {
        foreach ($this->rows as $i => $row) {
            $this->rows[$i]['shift_end_time'] = $value;
        }
    }

    // Switching Trips <-> Rehandling gives every truck the matching blank activity rows.
    public function updatedFor()
    {
        foreach ($this->rows as $i => $row) {
            $this->rows[$i]['trips'] = $this->for === 'Trips' ? $this->newTrips() : [];
            $this->rows[$i]['works'] = $this->for === 'Trips' ? [] : $this->newWorks();
            $this->rows[$i]['fuel']['allocation_to'] = $this->for === 'Trips' ? ($this->rows[$i]['fuel']['allocation_to'] ?? '') : '';
        }
        $this->resetErrorBag();
    }

    public function updatedFuelForAll($value)
    {
        foreach ($this->rows as $i => $row) {
            if ($value && ! $row['fuel_order']) {
                $this->rows[$i]['fuel_order'] = true;
                $this->rows[$i]['fuel'] = $this->newFuel($row);
            }
        }
    }

    public function updatedFuelDefaults($value, $key)
    {
        if ($key === 'container_id' && $value && ($container = Container::find($value))) {
            $this->fuelDefaults['currency_id'] = $container->currency_id ?: $this->fuelDefaults['currency_id'];
            $this->fuelDefaults['fuel_type'] = $container->fuel_type ? ucfirst(strtolower($container->fuel_type)) : ($this->fuelDefaults['fuel_type'] ?: 'Diesel');
            $this->fuelDefaults['exchange_rate'] = $this->defaultExchangeRate($this->fuelDefaults['currency_id']);
        } elseif ($key === 'currency_id') {
            $this->fuelDefaults['exchange_rate'] = $this->defaultExchangeRate($value);
        }
    }

    // ---- per-truck field changes ----

    public function updatedRows($value, $key)
    {
        $parts = explode('.', $key);
        $i = $parts[0];
        if (! isset($this->rows[$i])) {
            return;
        }

        if ($parts[1] === 'fuel_order' && $value) {
            $this->rows[$i]['fuel'] = $this->newFuel($this->rows[$i]);
        } elseif ($parts[1] === 'fuel') {
            $this->fuelChanged($i, $parts[2] ?? null, $value);
        } elseif ($parts[1] === 'trips' && ($parts[3] ?? null) === 'cargo_id') {
            $this->rows[$i]['trips'][$parts[2]]['measurement'] = '';
        } elseif ($parts[1] === 'trips' && ($parts[3] ?? null) === 'trip_ref') {
            $this->checkReferences();
        } elseif ($parts[1] === 'close_mileage' && ($this->rows[$i]['fuel_order'] ?? false)) {
            $this->rows[$i]['fuel']['mileage'] = $value;
        }
    }

    // Mirrors Shifts\Index: station sets currency + locks fuel type; source truck sets fuel type; qty x rate = total.
    private function fuelChanged($i, $field, $value): void
    {
        $fuel = &$this->rows[$i]['fuel'];

        if ($field === 'source') {
            $fuel['container_id'] = $value === 'truck' ? '' : $fuel['container_id'];
            $fuel['source_horse_id'] = $value === 'truck' ? $fuel['source_horse_id'] : '';
        } elseif ($field === 'container_id' && $value && ($container = Container::find($value))) {
            $fuel['currency_id'] = $container->currency_id ?: $fuel['currency_id'];
            $fuel['exchange_rate'] = $this->defaultExchangeRate($fuel['currency_id']);
            $fuel['fuel_type'] = $container->fuel_type ? ucfirst(strtolower($container->fuel_type)) : ($fuel['fuel_type'] ?: 'Diesel');
        } elseif ($field === 'source_horse_id' && $value && ($source = Horse::find($value))) {
            $fuel['fuel_type'] = $source->fuel_type ? ucfirst(strtolower($source->fuel_type)) : ($fuel['fuel_type'] ?: 'Diesel');
        } elseif ($field === 'currency_id') {
            $fuel['exchange_rate'] = $this->defaultExchangeRate($value);
        }

        if (in_array($field, ['quantity', 'unit_price'], true) && is_numeric($fuel['quantity']) && is_numeric($fuel['unit_price']) && $fuel['unit_price'] > 0) {
            $fuel['amount'] = round($fuel['quantity'] * $fuel['unit_price'], 2);
        }
    }

    // ---- trucks ----

    public function addPicked()
    {
        $existing = collect($this->rows)->pluck('key')->all();
        $model = $this->equipment === 'Vehicle' ? Vehicle::class : Horse::class;

        foreach ($model::with('transporter')->whereIn('id', $this->picked)->orderByIdentifier()->get() as $asset) {
            $key = $this->equipment[0] . $asset->id;
            if (! in_array($key, $existing, true)) {
                $this->addRow($asset, $key);
            }
        }

        $this->picked = [];
    }

    // Prefills as picking a horse/vehicle on Add Shift does: assigned driver, cluster trailers,
    // opening mileage from the asset, closing (and fuel) mileage live from Cartrack/EzyTrack.
    private function addRow($asset, string $key): void
    {
        $isHorse = $asset instanceof Horse;
        $live = $this->liveMileage($asset);

        $row = [
            'key' => $key,
            'equipment' => $isHorse ? 'Horse' : 'Vehicle',
            'horse_id' => $isHorse ? $asset->id : null,
            'vehicle_id' => $isHorse ? null : $asset->id,
            'label' => $asset->identifier_label,
            'transporter_id' => $asset->transporter_id,
            'transporter_name' => optional($asset->transporter)->name,
            'driver_id' => $isHorse ? (string) ($asset->assignment?->driver_id ?? '') : '',
            'trailer_ids' => $isHorse
                ? (Cluster::where('horse_id', $asset->id)->where('status', true)->first()?->trailers->pluck('id')->map(fn ($id) => (string) $id)->all() ?? [])
                : [],
            'shift_start_time' => $this->shift_start_time,
            'shift_end_time' => $this->shift_end_time,
            'open_mileage' => is_numeric($asset->mileage) ? $asset->mileage : '',
            'close_mileage' => $live['mileage'] ?? '',
            'live_source' => $live['source'] ?? null,
            'calculated_mileage' => '',
            'comments' => '',
            'depart_workshop_time' => '',
            'arrive_location_time' => '',
            'depart_location_time' => '',
            'arrive_workshop_time' => '',
            'trips' => $this->for === 'Trips' ? $this->newTrips() : [],
            'works' => $this->for === 'Trips' ? [] : $this->newWorks(),
            'fuel_order' => (bool) $this->fuelForAll,
            'fuel' => [],
        ];
        $row['fuel'] = $this->fuelForAll ? $this->newFuel($row) : [];

        $this->rows[] = $row;
    }

    private function liveMileage($asset): array
    {
        try {
            $snapshot = app(CartrackSyncService::class)->currentSnapshot($asset);
            if (! empty($snapshot['mileage'])) {
                return ['mileage' => $snapshot['mileage'], 'source' => 'Cartrack'];
            }
            $snapshot = app(EzyTrackSyncService::class)->currentSnapshot($asset);
            if (! empty($snapshot['mileage'])) {
                return ['mileage' => $snapshot['mileage'], 'source' => 'EzyTrack'];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return [];
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->resetErrorBag();
    }

    // Copies the previous truck's trips / works (minus refs) and fuel order setup.
    public function copyPrevious($index)
    {
        $prev = $this->rows[$index - 1] ?? null;
        if (! $prev || ! isset($this->rows[$index])) {
            return;
        }

        $this->rows[$index]['trips'] = array_map(fn ($t) => array_merge($t, ['key' => uniqid(), 'trip_ref' => '']), $prev['trips']);
        $this->rows[$index]['works'] = array_map(fn ($w) => array_merge($w, ['key' => uniqid()]), $prev['works']);
        $this->rows[$index]['calculated_mileage'] = $prev['calculated_mileage'];
        foreach (['depart_workshop_time', 'arrive_location_time', 'depart_location_time', 'arrive_workshop_time'] as $field) {
            $this->rows[$index][$field] = $prev[$field];
        }
        $this->rows[$index]['fuel_order'] = $prev['fuel_order'];
        if ($prev['fuel_order']) {
            $this->rows[$index]['fuel'] = array_merge($this->newFuel($this->rows[$index]), array_intersect_key($prev['fuel'], array_flip(self::FUEL_DEFAULTS)), [
                'quantity' => $prev['fuel']['quantity'],
                'amount' => $prev['fuel']['amount'],
            ]);
        }
    }

    // ---- activity rows ----

    private function newTrips(): array
    {
        return array_map(fn () => $this->newTrip(), array_fill(0, max(0, min(20, (int) $this->tripsPerTruck)), null));
    }

    private function newTrip(): array
    {
        return array_merge($this->tripDefaults, [
            'key' => uniqid('', true),
            'trip_ref' => '',
            'quantity' => '',
            'litreage' => '',
            'litreage_at_20' => '',
            'arrive_lp' => '', 'depart_lp' => '', 'arrive_op' => '', 'depart_op' => '',
            'starting_mileage' => '', 'ending_mileage' => '', 'starting_hours' => '', 'ending_hours' => '',
        ]);
    }

    private function newWorks(): array
    {
        return array_map(fn () => $this->newWork(), array_fill(0, max(0, min(20, (int) $this->worksPerTruck)), null));
    }

    private function newWork(): array
    {
        return array_merge($this->workDefaults, [
            'key' => uniqid('', true),
            'loads' => '',
            'start_time' => '', 'starting_hours' => '', 'starting_mileage' => '',
            'stop_time' => '', 'ending_hours' => '', 'ending_mileage' => '',
        ]);
    }

    private function newFuel(array $row): array
    {
        return array_merge($this->fuelDefaults, [
            'source' => $row['equipment'] === 'Horse' ? ($this->fuelDefaults['source'] ?: 'station') : 'station',
            'source_horse_id' => '',
            'deduct_from' => 'quantity',
            'allocation_to' => $this->for === 'Trips' ? $this->fuelDefaults['allocation_to'] : '',
            'quantity' => '',
            'amount' => '',
            'mileage' => is_numeric($row['close_mileage']) ? $row['close_mileage'] : $row['open_mileage'],
            'hours' => '',
            'comments' => '',
        ]);
    }

    public function addTrip($i)
    {
        $this->rows[$i]['trips'][] = $this->newTrip();
    }

    public function removeTrip($i, $t)
    {
        unset($this->rows[$i]['trips'][$t]);
        $this->rows[$i]['trips'] = array_values($this->rows[$i]['trips']);
    }

    public function addWork($i)
    {
        $this->rows[$i]['works'][] = $this->newWork();
    }

    public function removeWork($i, $w)
    {
        unset($this->rows[$i]['works'][$w]);
        $this->rows[$i]['works'] = array_values($this->rows[$i]['works']);
    }

    // ---- validation ----

    protected function rules()
    {
        $isTrips = $this->for === 'Trips';

        $rules = [
            'for' => 'required|in:Trips,Rehandling',
            'type' => 'required|in:Morning,Backshift,Night',
            'date' => 'required|date_format:Y-m-d',
            'team_id' => 'required|exists:teams,id',
            'customer_id' => 'required|exists:customers,id',
            'rows' => 'required|array|min:1',
            'rows.*.key' => 'distinct',
            'rows.*.transporter_id' => 'required|exists:transporters,id',
            'rows.*.driver_id' => 'required|distinct|exists:drivers,id',
            'rows.*.shift_start_time' => 'required|date_format:Y-m-d\TH:i',
            'rows.*.shift_end_time' => 'required|date_format:Y-m-d\TH:i|after:rows.*.shift_start_time',
            'rows.*.calculated_mileage' => 'nullable|numeric|min:0',
        ];

        foreach ($this->rows as $i => $row) {
            $p = "rows.$i.";

            if ($isTrips) {
                $rules[$p.'open_mileage'] = 'required|numeric|min:0';
                $rules[$p.'close_mileage'] = 'required|numeric|gte:'.$p.'open_mileage';

                foreach ($row['trips'] as $t => $trip) {
                    $tp = $p."trips.$t.";
                    $rules[$tp.'trip_ref'] = 'nullable|string|max:255';
                    $rules[$tp.'haulage_type'] = 'required|in:short_haul,long_haul';
                    $rules[$tp.'cargo_id'] = 'required|exists:cargos,id';
                    $rules[$tp.'from'] = 'required|exists:destinations,id';
                    $rules[$tp.'to'] = 'required|exists:destinations,id';
                    $rules[$tp.'loading_point_id'] = 'required|exists:loading_points,id';
                    $rules[$tp.'offloading_point_id'] = 'required|exists:offloading_points,id';
                    foreach (['weight', 'rate', 'quantity', 'litreage', 'litreage_at_20', 'starting_mileage', 'ending_mileage', 'starting_hours', 'ending_hours'] as $field) {
                        $rules[$tp.$field] = 'nullable|numeric|min:0';
                    }
                }
            } else {
                $rules[$p.'open_mileage'] = 'nullable|numeric|min:0';
                $rules[$p.'close_mileage'] = 'nullable|numeric|min:0';

                foreach ($row['works'] as $w => $work) {
                    $wp = $p."works.$w.";
                    $rules[$wp.'start_time'] = 'required';
                    $rules[$wp.'starting_hours'] = 'required|numeric|min:0';
                    $rules[$wp.'work_id'] = 'required|exists:works,id';
                    $rules[$wp.'location_id'] = 'required|exists:locations,id';
                    $rules[$wp.'stop_time'] = 'required';
                    $rules[$wp.'ending_hours'] = 'required|numeric|min:0';
                    foreach (['starting_mileage', 'ending_mileage', 'weight', 'loads'] as $field) {
                        $rules[$wp.$field] = 'nullable|numeric|min:0';
                    }
                }
            }

            if (! empty($row['fuel_order'])) {
                $fp = $p.'fuel.';
                $fromTruck = $row['equipment'] === 'Horse' && ($row['fuel']['source'] ?? '') === 'truck';
                $rules[$fp.'source'] = 'required|in:station,truck';
                $rules[$fp.($fromTruck ? 'source_horse_id' : 'container_id')] = $fromTruck
                    ? ['required', 'exists:horses,id', 'not_in:'.$row['horse_id']]
                    : 'required|exists:containers,id';
                $rules[$fp.'category'] = 'required|in:Customer,Self,Transporter';
                $rules[$fp.'account_id'] = 'nullable|exists:accounts,id';
                $rules[$fp.'fuel_type'] = 'required|in:Diesel,Petrol';
                $rules[$fp.'is_full_tank'] = 'required|in:0,1';
                $rules[$fp.'allocation_to'] = ($isTrips ? 'required' : 'nullable').'|in:long_haul,short_haul';
                $rules[$fp.'currency_id'] = 'nullable|exists:currencies,id';
                $rules[$fp.'exchange_rate'] = ($this->isForeign($row['fuel']['currency_id'] ?? null) ? 'required' : 'nullable').'|numeric|min:0';
                $rules[$fp.'quantity'] = 'required|numeric|gt:0';
                $rules[$fp.'unit_price'] = 'nullable|numeric|min:0';
                $rules[$fp.'amount'] = 'nullable|numeric|min:0';
                $rules[$fp.'mileage'] = ($isTrips ? 'required' : 'nullable').'|numeric|min:0';
                $rules[$fp.'hours'] = 'required|numeric|min:0';
            }
        }

        return $rules;
    }

    protected $messages = [
        'rows.required' => 'Add at least one truck.',
        'rows.*.key.distinct' => 'This truck is listed twice.',
        'rows.*.driver_id.distinct' => 'This driver is on another truck.',
        'rows.*.shift_end_time.after' => 'Must be after the start time.',
        'rows.*.close_mileage.gte' => 'Must be at least the opening mileage.',
        'rows.*.fuel.source_horse_id.not_in' => 'Pick a different truck.',
        'rows.*.fuel.allocation_to.required' => 'Allocation To is required for a Trips shift fuel order.',
        'rows.*.fuel.quantity.gt' => 'Enter the quantity.',
        'rows.*.*.required' => 'Required.',
        'rows.*.*.*.required' => 'Required.',
        'rows.*.*.*.*.required' => 'Required.',
    ];

    // Trip refs must be unique in the batch and against existing trips (as on Add Shift).
    private function referenceProblems(): array
    {
        $problems = [];
        $seen = [];

        foreach ($this->rows as $i => $row) {
            foreach ($row['trips'] ?? [] as $t => $trip) {
                $ref = trim((string) ($trip['trip_ref'] ?? ''));
                if ($ref === '') {
                    continue;
                }
                $field = "rows.$i.trips.$t.trip_ref";
                $normalised = mb_strtolower($ref);
                if (isset($seen[$normalised])) {
                    $problems[$field] = 'Entered more than once.';
                } elseif (UniqueReference::exists(Trip::class, 'trip_ref', $ref)) {
                    $problems[$field] = 'Already in use.';
                }
                $seen[$normalised] = true;
            }
        }

        return $problems;
    }

    private function checkReferences(): void
    {
        foreach (array_keys($this->getErrorBag()->messages()) as $key) {
            if (preg_match('/\.trip_ref$/', $key)) {
                $this->resetErrorBag($key);
            }
        }
        foreach ($this->referenceProblems() as $field => $message) {
            $this->addError($field, $message);
        }
    }

    public function store(BulkShiftService $service)
    {
        $this->validate();

        if ($this->for !== 'Trips') {
            // Machine hours can't exceed man hours: the hour-meter run must fit in Start -> Close time
            $meterProblems = [];
            foreach ($this->rows as $i => $row) {
                foreach ($row['works'] ?? [] as $w => $work) {
                    $problem = RehandlingHourMeter::problem(
                        $work['start_time'] ?? null,
                        $work['stop_time'] ?? null,
                        $work['starting_hours'] ?? null,
                        $work['ending_hours'] ?? null
                    );
                    if ($problem) {
                        $meterProblems["rows.$i.works.$w.ending_hours"] = $problem;
                    }
                }
            }
            if ($meterProblems) {
                throw ValidationException::withMessages($meterProblems);
            }
        }

        if ($problems = $this->referenceProblems()) {
            foreach ($problems as $field => $message) {
                $this->addError($field, $message);
            }
            return;
        }

        $isTrips = $this->for === 'Trips';
        $shifts = [];
        foreach ($this->rows as $row) {
            $shifts[] = array_merge($row, [
                'for' => $this->for,
                'type' => $this->type,
                'date' => $this->date,
                'team_id' => $this->team_id,
                'customer_id' => $this->customer_id,
                'currency_id' => $this->company->currency_id ?? 1,
                'exchange_rate' => null,
                'trips' => $isTrips ? $row['trips'] : [],
                'works' => $isTrips ? [] : $row['works'],
                'fuel' => $row['fuel_order'] ? $row['fuel'] : null,
            ]);
        }

        $created = $service->create($shifts, $this->company, Auth::user());

        $trips = $isTrips ? collect($this->rows)->sum(fn ($r) => count($r['trips'])) : 0;
        session()->flash('success', $created->count().' shifts created ('.$created->first()->shift_number.' to '.$created->last()->shift_number.')'
            .($trips ? " with $trips trip(s)" : '').'.');

        return redirect()->route('shifts.index');
    }

    // ---- view ----

    public function render()
    {
        $model = $this->equipment === 'Vehicle' ? Vehicle::class : Horse::class;
        $taken = collect($this->rows)->where('equipment', $this->equipment)->pluck($this->equipment === 'Vehicle' ? 'vehicle_id' : 'horse_id')->all();

        $assets = $model::selectable()
            ->with('transporter:id,name')
            ->where('archive', 0)
            ->whereNotIn('id', $taken)
            ->when($this->pickerTransporterId, fn ($q) => $q->where('transporter_id', $this->pickerTransporterId))
            ->when(filled($this->truckSearch), function ($q) {
                $term = '%'.$this->truckSearch.'%';
                $q->where(fn ($qq) => $qq->where('registration_number', 'like', $term)->orWhere('fleet_number', 'like', $term));
            })
            ->orderByIdentifier()
            ->limit(100)
            ->get();

        // Each truck picks from its own transporter's drivers/trailers; a prefilled one stays listed.
        $transporterIds = collect($this->rows)->pluck('transporter_id')->filter()->unique()->all();
        $driverIds = collect($this->rows)->pluck('driver_id')->filter()->all();
        $trailerIds = collect($this->rows)->pluck('trailer_ids')->flatten()->filter()->all();

        $drivers = Driver::with('employee:id,name,surname')
            ->where(fn ($q) => $q->whereIn('id', Driver::selectable()->whereIn('transporter_id', $transporterIds)
                ->whereHas('employee', fn ($e) => $e->where('archive', 0)->where('status', 1))->select('id'))
                ->orWhereIn('id', $driverIds))
            ->get()
            ->filter(fn ($driver) => $driver->employee)
            ->sortBy(fn ($driver) => $driver->employee->name.' '.$driver->employee->surname)
            ->groupBy('transporter_id');

        $trailers = $transporterIds || $trailerIds
            ? Trailer::where(fn ($q) => $q->whereIn('transporter_id', $transporterIds)->where('archive', 0)->orWhereIn('id', $trailerIds))->orderByIdentifier()->get()->groupBy('transporter_id')
            : collect();

        $fuelRows = collect($this->rows)->contains('fuel_order', true) || $this->fuelForAll;

        return view('livewire.shifts.bulk-create', [
            'assets' => $assets,
            'drivers' => $drivers,
            'trailers' => $trailers,
            'teams' => Team::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'transporters' => Transporter::where('authorization', 'approved')->orderBy('name')->get(['id', 'name']),
            'cargos' => Cargo::selectable()->orderBy('name')->get(['id', 'name', 'type']),
            'destinations' => Destination::with('country')->get()->sortBy('city')->sortBy('country.name'),
            'loadingPoints' => LoadingPoint::orderBy('name')->get(['id', 'name']),
            'offloadingPoints' => OffloadingPoint::orderBy('name')->get(['id', 'name']),
            'measurements' => Measurement::orderBy('name')->get(['name', 'cargo_type'])->groupBy('cargo_type'),
            'works' => $this->for === 'Trips' ? collect() : Work::orderBy('description')->get(['id', 'description']),
            'locations' => $this->for === 'Trips' ? collect() : Location::orderBy('name')->get(['id', 'name']),
            'containers' => Container::orderBy('name')->get(['id', 'name']),
            'currencies' => Currency::orderBy('name')->get(['id', 'name', 'symbol']),
            'accounts' => $fuelRows ? Account::active()->whereHas('account_type.account_type_group', fn ($q) => $q->where('name', 'Expenses'))->orderBy('name')->get(['id', 'name']) : collect(),
            'sourceHorses' => $fuelRows ? Horse::selectable()->where('archive', 0)->orderByIdentifier()->get() : collect(),
        ]);
    }
}
