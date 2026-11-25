<?php

namespace App\Services\Maintenance;

use App\Models\Asset;
use App\Models\Horse;
use App\Models\InspectionSchedule;
use App\Models\MaintenanceItem;
use App\Models\Trailer;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Data behind the Workshop Maintenance Planner export: every unit in scope
 * (tracking or not), the tracked items as columns, and each unit's
 * maintenance schedule per item with what is left as at today.
 *
 * Filters: equipment (Horse|Vehicle|Trailer|Asset), item_id, status
 * (overdue|due_soon|ok|no_reading|inactive).
 */
class MaintenancePlannerData
{
    public const EQUIPMENT = [
        'Horse' => ['horse_id', Horse::class],
        'Vehicle' => ['vehicle_id', Vehicle::class],
        'Trailer' => ['trailer_id', Trailer::class],
        'Asset' => ['asset_id', Asset::class],
    ];

    public Carbon $asAt;

    /** @var Collection<int, InspectionSchedule> all maintenance schedules in scope (status-filtered) */
    public Collection $schedules;

    /** @var Collection<int, MaintenanceItem> matrix columns */
    public Collection $items;

    /** @var array<int, array> matrix rows */
    public array $units = [];

    public function __construct(public array $filters = [])
    {
        $this->asAt = now()->startOfDay();
        $this->build();
    }

    public static function statusOf(InspectionSchedule $schedule, Carbon $asAt): string
    {
        return strtolower((string) $schedule->status) === 'inactive' ? 'inactive' : $schedule->dueStatus($asAt);
    }

    protected function build(): void
    {
        $equipment = $this->filters['equipment'] ?? null;
        $itemId = $this->filters['item_id'] ?? null;
        $status = $this->filters['status'] ?? null;

        $all = InspectionSchedule::query()
            ->maintenance()
            ->with(['horse.horse_make', 'horse.horse_model', 'vehicle.vehicle_make', 'vehicle.vehicle_model', 'trailer', 'asset.product', 'maintenance_item', 'service_type'])
            ->when($equipment, fn ($q) => $q->whereNotNull(self::EQUIPMENT[$equipment][0]))
            ->when($itemId, fn ($q) => $q->where('maintenance_item_id', $itemId))
            ->orderBy('id')
            ->get()
            ->filter(fn ($s) => $s->equipment() !== null)
            ->each(fn ($s) => $s->setAttribute('planner_status', self::statusOf($s, $this->asAt)));

        $this->schedules = $status ? $all->where('planner_status', $status)->values() : $all->values();

        $itemSchedules = $all->whereNotNull('maintenance_item_id');

        $this->items = $itemId
            ? MaintenanceItem::withTrashed()->whereKey($itemId)->get()
            : $itemSchedules->pluck('maintenance_item')->filter()->unique('id')
                ->sortBy(fn ($i) => sprintf('%s|%s', $i->category ?? 'ZZZ', $i->name))->values();

        // Equipment types to list: the filter, else every type that tracks an item.
        $types = $equipment ? [$equipment] : array_values(array_filter(array_keys(self::EQUIPMENT), function ($type) use ($itemSchedules) {
            $column = self::EQUIPMENT[$type][0];

            return $itemSchedules->whereNotNull($column)->isNotEmpty();
        }));

        // Units whose schedules match the status filter (null = no filter).
        $statusUnits = $status ? $this->schedules->map(fn ($s) => $s->equipmentType() . ':' . $s->equipment()->id)->unique()->flip() : null;

        foreach ($types as $type) {
            [$column] = self::EQUIPMENT[$type];
            $bySchedule = $itemSchedules->whereNotNull($column)->groupBy($column);

            foreach ($this->unitsOf($type, $bySchedule->keys()->all()) as $unit) {
                if ($statusUnits && !isset($statusUnits[$type . ':' . $unit->id])) {
                    continue;
                }

                $this->units[] = [
                    'type' => $type,
                    'unit' => $unit,
                    'label' => InspectionSchedule::labelFor($unit),
                    'make' => $this->makeModel($type, $unit),
                    'hours' => InspectionSchedule::currentReading($unit, 'hours'),
                    'km' => InspectionSchedule::currentReading($unit, 'km'),
                    'schedules' => ($bySchedule[$unit->id] ?? collect())->keyBy('maintenance_item_id'),
                ];
            }
        }
    }

    /** Active operational units of a type, plus any (e.g. archived) unit that still has a schedule. */
    protected function unitsOf(string $type, array $keepIds): Collection
    {
        $units = match ($type) {
            'Horse' => Horse::selectable($keepIds)->operational()->with('horse_make', 'horse_model')->get(),
            'Vehicle' => Vehicle::selectable($keepIds)->operational()->with('vehicle_make', 'vehicle_model')->get(),
            'Trailer' => Trailer::selectable($keepIds)->operational()->get(),
            default => Asset::with('product')->where(fn ($q) => $q->where(fn ($a) => $a->where('disposed', 0)->where('status', 1))->orWhereIn('id', $keepIds))->get(),
        };

        // Fleet units: natural sort on the company's preferred identifier (the
        // label may start with "(", so sorting on it would misorder units).
        return $type === 'Asset'
            ? $units->sortBy(fn ($u) => InspectionSchedule::labelFor($u), SORT_NATURAL | SORT_FLAG_CASE)->values()
            : \App\Support\FleetIdentifier::sortCollection($units);
    }

    protected function makeModel(string $type, $unit): string
    {
        return trim(match ($type) {
            'Horse' => ($unit->horse_make->name ?? '') . ' ' . ($unit->horse_model->name ?? ''),
            'Vehicle' => ($unit->vehicle_make->name ?? '') . ' ' . ($unit->vehicle_model->name ?? ''),
            'Trailer' => ($unit->make ?? '') . ' ' . ($unit->model ?? ''),
            default => ($unit->product->name ?? ''),
        });
    }

    /** "Horses, Trailers" / "All" etc. for the report header. */
    public function filterLines(): array
    {
        $item = ($this->filters['item_id'] ?? null) ? $this->items->first() : null;
        $status = $this->filters['status'] ?? null;

        return [
            'Equipment' => ($this->filters['equipment'] ?? null) ? $this->filters['equipment'] . 's' : 'All',
            'Tracked Item' => $item ? $item->label : 'All',
            'Status' => $status ? (InspectionSchedule::STATUS_LABELS[$status] ?? ucfirst($status)) : 'All',
        ];
    }
}
