<?php

namespace App\Http\Livewire\InspectionSchedules;

use App\Exports\MaintenancePlannerExport;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\InspectionSchedule;
use App\Models\MaintenanceItem;
use App\Models\ProblemCategory;
use App\Models\ServiceType;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Support\FleetIdentifier;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Inspection schedules (type "inspection") and Fleet Maintenance Schedules
 * (type "maintenance"). Maintenance schedules can track a MaintenanceItem
 * (THERMOSTATS @ 15000 HRS...) by hour meter, km or days and show what is
 * left per unit; the Maintenance Planner export is downloaded from here.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['maintenanceItemsChanged' => '$refresh'];

    public $search;
    public $type;

    // filters (maintenance)
    public $filterEquipment = '';
    public $filterItem = '';
    public $filterStatus = '';

    // create / edit form
    public $inspection_schedule_id;
    public $equipment = 'Horse';
    public $selectedHorse;
    public $selectedVehicle;
    public $selectedTrailer;
    public $selectedAsset;
    public $maintenance_item_id;
    public $problem_category_id;
    public $service_type_id;
    public $trigger_units = [];
    public $interval_hours;
    public $interval_km;
    public $interval_days;
    public $last_inspection_date;
    public $last_inspection_km;
    public $last_inspection_hours;
    public $next_due_date;
    public $next_due_km;
    public $next_due_hours;
    public $status = 'active';
    public $notes;

    // track item on fleet (bulk)
    public $bulk_item_id;
    public $bulk_equipment = 'Horse';
    public $bulk_ids = [];
    public $bulk_intervals = [];
    public $bulk_last_date;
    public $bulk_use_current = true;
    public $bulk_last_readings = [];

    // mark done
    public $done_id;
    public $done_date;
    public $done_reading_hours;
    public $done_reading_km;
    public $done_notes;

    // hour meter / odometer reading
    public $reading_equipment = 'Horse';
    public $reading_unit_id;
    public $reading_hours;
    public $reading_km;
    public $reading_mode = 'single';      // single | fleet
    public $reading_search = '';
    public $fleet_readings = [];          // [unit id => ['hours' => .., 'km' => ..]]

    // Inline edit of the picked unit's current readings in the Add/Edit form
    public $editingCurrentReading = false;
    public $current_hours;
    public $current_km;

    public $delete_id;

    public $searchAsset;
    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;

    public function mount($type)
    {
        $this->type = $type;
    }

    public function isMaintenance(): bool
    {
        return $this->type !== 'inspection';
    }

    public function updating($name)
    {
        if (in_array($name, ['search', 'filterEquipment', 'filterItem', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    public function updatedMaintenanceItemId($value)
    {
        $item = $value ? MaintenanceItem::find($value) : null;
        if (!$item) {
            return;
        }

        // Prefill the units counted and their intervals from the item's constants.
        $this->trigger_units = $item->units();
        foreach ($item->units() as $unit) {
            $this->{"interval_{$unit}"} = MaintenanceItem::formatNumber($item->intervalFor($unit));
        }
        $this->prefillLastDone();
    }

    public function updatedBulkItemId($value)
    {
        $item = $value ? MaintenanceItem::find($value) : null;
        $this->bulk_intervals = [];
        $this->bulk_last_readings = [];
        foreach ($item ? $item->units() : [] as $unit) {
            $this->bulk_intervals[$unit] = MaintenanceItem::formatNumber($item->intervalFor($unit));
        }
        if ($item && !$item->appliesTo($this->bulk_equipment)) {
            $this->bulk_equipment = ucfirst($item->appliesToList()[0] ?? 'horse');
        }
        $this->bulk_ids = [];
    }

    public function updatedBulkEquipment()
    {
        $this->bulk_ids = [];
    }

    private function resetInputFields()
    {
        $this->reset([
            'inspection_schedule_id', 'selectedHorse', 'selectedVehicle', 'selectedTrailer', 'selectedAsset',
            'maintenance_item_id', 'problem_category_id', 'service_type_id', 'trigger_units',
            'interval_hours', 'interval_km', 'interval_days', 'last_inspection_date', 'last_inspection_km',
            'last_inspection_hours', 'next_due_date', 'next_due_km', 'next_due_hours', 'notes',
            'editingCurrentReading', 'current_hours', 'current_km',
        ]);
        $this->status = 'active';
        $this->resetErrorBag();
    }

    // ---------------------------------------------------------------
    // Current hours / km of the unit picked in the Add/Edit form
    // ---------------------------------------------------------------

    public function updated($name)
    {
        // A different unit or equipment type: drop any half-done reading edit.
        if ($name === 'equipment' || str_starts_with($name, 'selected')) {
            $this->cancelCurrentReading();
        }

        if ($name === 'equipment' || str_starts_with($name, 'selected') || str_starts_with($name, 'trigger_units') || $name === 'maintenance_item_id') {
            $this->prefillLastDone();
        }
    }

    /**
     * Show the unit's current details as "last done" while those fields are
     * blank (next due is then last done + interval on save). Never overwrites
     * anything the user typed.
     */
    private function prefillLastDone(): void
    {
        $unit = $this->selectedReadingUnit();
        $units = MaintenanceItem::parseUnits($this->trigger_units);

        if (!$this->last_inspection_date) {
            $this->last_inspection_date = now()->toDateString();
        }
        if ($unit && in_array('hours', $units) && !filled($this->last_inspection_hours)) {
            $this->last_inspection_hours = InspectionSchedule::currentReading($unit, 'hours');
        }
        if ($unit && in_array('km', $units) && !filled($this->last_inspection_km)) {
            $km = InspectionSchedule::currentReading($unit, 'km');
            $this->last_inspection_km = $km !== null ? (int) round($km) : null;
        }
    }

    private function selectedReadingUnit()
    {
        $class = ['Horse' => Horse::class, 'Vehicle' => Vehicle::class, 'Trailer' => Trailer::class][$this->equipment] ?? null;
        $id = $this->{'selected' . $this->equipment} ?? null;

        return $class && $id ? $class::find($id) : null;
    }

    public function editCurrentReading()
    {
        $unit = $this->selectedReadingUnit();
        if (!$unit) {
            return;
        }
        $this->current_hours = InspectionSchedule::currentReading($unit, 'hours');
        $this->current_km = InspectionSchedule::currentReading($unit, 'km');
        $this->editingCurrentReading = true;
        $this->resetErrorBag(['current_hours', 'current_km']);
    }

    public function cancelCurrentReading()
    {
        $this->reset(['editingCurrentReading', 'current_hours', 'current_km']);
        $this->resetErrorBag(['current_hours', 'current_km']);
    }

    /** Saves to the horse/vehicle/trailer itself; lower figures allowed so wrong readings can be corrected. */
    public function saveCurrentReading()
    {
        $this->validate([
            'current_hours' => 'nullable|numeric|min:0',
            'current_km' => 'nullable|numeric|min:0',
        ]);

        $unit = $this->selectedReadingUnit();
        if (!$unit) {
            return;
        }

        $changes = [];
        if (filled($this->current_hours)) {
            $changes['hours'] = (string) (float) $this->current_hours;
        }
        if (filled($this->current_km)) {
            $changes['mileage'] = (string) (int) round((float) $this->current_km);
        }
        if ($changes) {
            $unit->forceFill($changes)->save();
        }

        $this->cancelCurrentReading();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Current reading updated for ' . InspectionSchedule::labelFor($unit) . '.']);
    }

    protected function rules()
    {
        return [
            'equipment' => 'required|in:Horse,Vehicle,Trailer,Asset',
            'selectedHorse' => 'required_if:equipment,Horse|nullable|integer',
            'selectedVehicle' => 'required_if:equipment,Vehicle|nullable|integer',
            'selectedTrailer' => 'required_if:equipment,Trailer|nullable|integer',
            'selectedAsset' => 'required_if:equipment,Asset|nullable|integer',
            'maintenance_item_id' => 'nullable|exists:maintenance_items,id',
            'trigger_units' => 'required|array|min:1',
            'trigger_units.*' => 'in:hours,km,days',
            'interval_hours' => (in_array('hours', (array) $this->trigger_units) ? 'required' : 'nullable') . '|numeric|gt:0',
            'interval_km' => (in_array('km', (array) $this->trigger_units) ? 'required' : 'nullable') . '|numeric|gt:0',
            'interval_days' => (in_array('days', (array) $this->trigger_units) ? 'required' : 'nullable') . '|numeric|gt:0',
            'last_inspection_date' => 'nullable|date',
            'last_inspection_km' => 'nullable|numeric|min:0',
            'last_inspection_hours' => 'nullable|numeric|min:0',
            'next_due_date' => 'nullable|date',
            'next_due_km' => 'nullable|numeric|min:0',
            'next_due_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ];
    }

    protected $messages = [
        'selectedHorse.required_if' => 'Select a horse.',
        'selectedVehicle.required_if' => 'Select a vehicle.',
        'selectedTrailer.required_if' => 'Select a trailer.',
        'selectedAsset.required_if' => 'Select an asset.',
        'trigger_units.required' => 'Tick at least one of Hours, Km or Days.',
        'interval_hours.required' => 'Enter the interval in hours.',
        'interval_km.required' => 'Enter the interval in km.',
        'interval_days.required' => 'Enter the interval in days.',
    ];

    public function scheduleNumber()
    {
        $company = Auth::user()->company ?? Auth::user()->employee->company ?? null;
        $words = explode(' ', (string) ($company->name ?? 'G'));
        $initials = ($words[0][0] ?? 'G') . ($words[1][0] ?? '');
        $prefix = $this->isMaintenance() ? 'MS' : 'IS';
        $next = (InspectionSchedule::withTrashed()->max('id') ?? 0) + 1;

        return $initials . $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // The form binds selectedHorse/Vehicle/Trailer/Asset; the table stores
    // horse_id/vehicle_id/trailer_id/asset_id. Only the chosen equipment type is kept.
    private function fillEquipment(InspectionSchedule $schedule)
    {
        $schedule->horse_id = $this->equipment == 'Horse' ? ($this->selectedHorse ?: null) : null;
        $schedule->vehicle_id = $this->equipment == 'Vehicle' ? ($this->selectedVehicle ?: null) : null;
        $schedule->trailer_id = $this->equipment == 'Trailer' ? ($this->selectedTrailer ?: null) : null;
        $schedule->asset_id = $this->equipment == 'Asset' ? ($this->selectedAsset ?: null) : null;
    }

    private function fillSchedule(InspectionSchedule $schedule)
    {
        $schedule->type = $this->type;
        $schedule->maintenance_item_id = $this->isMaintenance() ? ($this->maintenance_item_id ?: null) : null;
        $schedule->problem_category_id = $this->problem_category_id ?: null;
        $schedule->service_type_id = $this->service_type_id ?: null;
        $this->fillEquipment($schedule);
        $schedule->notes = $this->notes;
        $schedule->trigger_type = implode(',', MaintenanceItem::parseUnits($this->trigger_units));

        // Keep only the intervals the trigger counts in.
        $dims = $schedule->dimensions();
        $schedule->interval_hours = in_array('hours', $dims) && filled($this->interval_hours) ? $this->interval_hours : null;
        $schedule->interval_km = in_array('km', $dims) && filled($this->interval_km) ? $this->interval_km : null;
        $schedule->interval_days = in_array('days', $dims) && filled($this->interval_days) ? $this->interval_days : null;

        $schedule->last_inspection_date = $this->last_inspection_date ?: null;
        $schedule->last_inspection_km = filled($this->last_inspection_km) ? $this->last_inspection_km : null;
        $schedule->last_inspection_hours = filled($this->last_inspection_hours) ? $this->last_inspection_hours : null;
        $schedule->next_due_date = $this->next_due_date ?: null;
        $schedule->next_due_km = filled($this->next_due_km) ? $this->next_due_km : null;
        $schedule->next_due_hours = filled($this->next_due_hours) ? $this->next_due_hours : null;
        $schedule->status = $this->status ?: 'active';

        // Blank last done = the unit's current details (today / current reading).
        $schedule->fillBlankLastDone();

        // Blank next due = last done + interval.
        $calc = $schedule->replicate();
        $calc->recalculateNextDue();
        $schedule->next_due_date = $schedule->next_due_date ?: $calc->next_due_date;
        $schedule->next_due_km = $schedule->next_due_km ?? $calc->next_due_km;
        $schedule->next_due_hours = $schedule->next_due_hours ?? $calc->next_due_hours;
    }

    private function guardDuplicate(InspectionSchedule $schedule)
    {
        if ($schedule->asset_id && array_diff($schedule->dimensions(), ['days'])) {
            throw ValidationException::withMessages(['trigger_units' => 'Assets have no hour meter or odometer - track them by Days only.']);
        }

        if (!$schedule->maintenance_item_id) {
            return;
        }

        $item = MaintenanceItem::withTrashed()->find($schedule->maintenance_item_id);
        if ($item && !$item->appliesTo($schedule->equipmentType())) {
            throw ValidationException::withMessages([
                'maintenance_item_id' => "{$item->name} applies to {$item->applies_to_label} only.",
            ]);
        }

        $exists = InspectionSchedule::query()
            ->where('maintenance_item_id', $schedule->maintenance_item_id)
            ->where('horse_id', $schedule->horse_id)
            ->where('vehicle_id', $schedule->vehicle_id)
            ->where('trailer_id', $schedule->trailer_id)
            ->where('asset_id', $schedule->asset_id)
            ->when($schedule->id, fn ($q) => $q->where('id', '!=', $schedule->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'maintenance_item_id' => 'This unit already tracks that item. Edit the existing schedule instead.',
            ]);
        }
    }

    public function create()
    {
        $this->resetInputFields();
        $this->dispatchBrowserEvent('show-saveModal');
    }

    public function store()
    {
        $this->validate();

        $schedule = new InspectionSchedule;
        $schedule->created_by = Auth::id();
        $schedule->inspection_schedule_number = $this->scheduleNumber();
        $this->fillSchedule($schedule);
        $this->guardDuplicate($schedule);
        $schedule->save();

        $this->dispatchBrowserEvent('hide-saveModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Schedule Created Successfully!!']);
    }

    public function edit($id)
    {
        $this->resetInputFields();
        $schedule = InspectionSchedule::findOrFail($id);

        $this->inspection_schedule_id = $schedule->id;
        $this->maintenance_item_id = $schedule->maintenance_item_id;
        $this->service_type_id = $schedule->service_type_id;
        $this->problem_category_id = $schedule->problem_category_id;
        $this->selectedHorse = $schedule->horse_id;
        $this->selectedVehicle = $schedule->vehicle_id;
        $this->selectedTrailer = $schedule->trailer_id;
        $this->selectedAsset = $schedule->asset_id;
        $this->equipment = $schedule->equipmentType() ?? 'Horse';
        $this->trigger_units = InspectionSchedule::unitsFromTrigger($schedule->trigger_type);
        $this->interval_hours = $schedule->interval_hours;
        $this->interval_km = $schedule->interval_km;
        $this->interval_days = $schedule->interval_days;
        $this->last_inspection_date = optional($schedule->last_inspection_date)->toDateString();
        $this->last_inspection_km = $schedule->last_inspection_km;
        $this->last_inspection_hours = $schedule->last_inspection_hours;
        $this->next_due_date = optional($schedule->next_due_date)->toDateString();
        $this->next_due_km = $schedule->next_due_km;
        $this->next_due_hours = $schedule->next_due_hours;
        $this->status = strtolower((string) $schedule->status) ?: 'active';
        $this->notes = $schedule->notes;

        $this->dispatchBrowserEvent('show-updateModal');
    }

    public function update()
    {
        if (!$this->inspection_schedule_id) {
            return;
        }

        $this->validate();

        $schedule = InspectionSchedule::findOrFail($this->inspection_schedule_id);
        $this->fillSchedule($schedule);
        $this->guardDuplicate($schedule);
        $schedule->save();

        $this->dispatchBrowserEvent('hide-updateModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Schedule Updated Successfully!!']);
    }

    public function confirmDelete($id)
    {
        $this->delete_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy()
    {
        optional(InspectionSchedule::find($this->delete_id))->delete();
        $this->delete_id = null;
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Schedule Deleted Successfully!!']);
    }

    // ---------------------------------------------------------------
    // Track an item on many units at once
    // ---------------------------------------------------------------

    public function openBulk()
    {
        $this->reset(['bulk_item_id', 'bulk_ids', 'bulk_intervals', 'bulk_last_readings']);
        $this->bulk_equipment = 'Horse';
        $this->bulk_use_current = true;
        $this->bulk_last_date = now()->toDateString();
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-bulkTrackModal');
    }

    public function selectAllBulk()
    {
        $this->bulk_ids = $this->equipmentOptions($this->bulk_equipment)->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function storeBulk()
    {
        $this->validate([
            'bulk_item_id' => 'required|exists:maintenance_items,id',
            'bulk_equipment' => 'required|in:Horse,Vehicle,Trailer,Asset',
            'bulk_ids' => 'required|array|min:1',
            'bulk_last_date' => 'nullable|date',
        ], ['bulk_ids.required' => 'Tick at least one unit.']);

        $item = MaintenanceItem::findOrFail($this->bulk_item_id);
        if (!$item->appliesTo($this->bulk_equipment)) {
            throw ValidationException::withMessages(['bulk_equipment' => "{$item->name} applies to {$item->applies_to_label} only."]);
        }

        $units = $item->units();
        $errors = [];
        foreach ($units as $unit) {
            if (!is_numeric($this->bulk_intervals[$unit] ?? null) || (float) $this->bulk_intervals[$unit] <= 0) {
                $errors["bulk_intervals.{$unit}"] = 'Enter the ' . MaintenanceItem::UNITS[$unit] . ' interval.';
            }
            $manual = $this->bulk_last_readings[$unit] ?? null;
            if ($unit !== 'days' && !$this->bulk_use_current && filled($manual) && (!is_numeric($manual) || $manual < 0)) {
                $errors["bulk_last_readings.{$unit}"] = 'Enter a reading of 0 or more.';
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $column = ['Horse' => 'horse_id', 'Vehicle' => 'vehicle_id', 'Trailer' => 'trailer_id', 'Asset' => 'asset_id'][$this->bulk_equipment];
        $class = ['Horse' => Horse::class, 'Vehicle' => Vehicle::class, 'Trailer' => Trailer::class, 'Asset' => Asset::class][$this->bulk_equipment];

        $already = InspectionSchedule::where('maintenance_item_id', $item->id)->whereIn($column, $this->bulk_ids)->pluck($column)->all();
        $created = 0;

        DB::transaction(function () use ($item, $column, $class, $units, $already, &$created) {
            foreach ($class::whereIn('id', $this->bulk_ids)->get() as $equipment) {
                if (in_array($equipment->id, $already)) {
                    continue;
                }

                $schedule = new InspectionSchedule;
                $schedule->created_by = Auth::id();
                $schedule->inspection_schedule_number = $this->scheduleNumber();
                $schedule->type = 'maintenance';
                $schedule->maintenance_item_id = $item->id;
                $schedule->{$column} = $equipment->id;
                $schedule->trigger_type = implode(',', $units);
                $schedule->status = 'active';
                $schedule->last_inspection_date = $this->bulk_last_date ?: null;

                foreach ($units as $unit) {
                    $schedule->{"interval_{$unit}"} = $this->bulk_intervals[$unit];
                    if ($unit === 'days') {
                        continue;
                    }

                    $manual = $this->bulk_last_readings[$unit] ?? null;
                    $reading = $this->bulk_use_current
                        ? InspectionSchedule::currentReading($equipment, $unit)
                        : (filled($manual) ? (float) $manual : null);

                    if ($unit === 'hours') {
                        $schedule->last_inspection_hours = $reading;
                    } else {
                        $schedule->last_inspection_km = $reading !== null ? (int) round($reading) : null;
                    }
                }

                // Anything still blank (date cleared, no manual reading) = current details.
                $schedule->fillBlankLastDone();
                $schedule->recalculateNextDue();
                $schedule->save();
                $created++;
            }
        });

        $skipped = count($this->bulk_ids) - $created;
        $this->dispatchBrowserEvent('hide-bulkTrackModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "{$item->name} now tracked on {$created} unit(s)" . ($skipped ? ", {$skipped} already tracking it" : '') . '.',
        ]);
    }

    // ---------------------------------------------------------------
    // Mark done: resets the countdown from the done date/reading
    // ---------------------------------------------------------------

    public function openDone($id)
    {
        $schedule = InspectionSchedule::with('horse', 'vehicle', 'trailer', 'asset')->findOrFail($id);
        $equipment = $schedule->equipment();

        $this->done_id = $schedule->id;
        $this->done_date = now()->toDateString();
        $this->done_reading_hours = InspectionSchedule::currentReading($equipment, 'hours');
        $this->done_reading_km = InspectionSchedule::currentReading($equipment, 'km');
        $this->done_notes = null;
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-doneModal');
    }

    public function markDone()
    {
        $schedule = InspectionSchedule::with('horse', 'vehicle', 'trailer')->findOrFail($this->done_id);
        $dims = $schedule->dimensions();

        $this->validate([
            'done_date' => 'required|date|before_or_equal:today',
            'done_reading_hours' => (in_array('hours', $dims) ? 'required' : 'nullable') . '|numeric|min:0',
            'done_reading_km' => (in_array('km', $dims) ? 'required' : 'nullable') . '|numeric|min:0',
            'done_notes' => 'nullable|string',
        ]);

        $schedule->last_inspection_date = $this->done_date;
        if (in_array('hours', $dims)) {
            $schedule->last_inspection_hours = $this->done_reading_hours;
        }
        if (in_array('km', $dims)) {
            $schedule->last_inspection_km = (int) round((float) $this->done_reading_km);
        }
        if (filled($this->done_notes)) {
            $schedule->notes = trim(($schedule->notes ? $schedule->notes . "\n" : '')
                . Carbon::parse($this->done_date)->format('d/m/Y') . ': ' . $this->done_notes);
        }
        $schedule->recalculateNextDue();
        $schedule->save();

        // A higher reading than the unit's meter moves the meter up (increase-only, like the tracker sync).
        $this->raiseReadings($schedule->equipment(), $this->done_reading_hours, $this->done_reading_km);

        $this->dispatchBrowserEvent('hide-doneModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Schedule marked done - next due recalculated.']);
    }

    private function raiseReadings($equipment, $hours, $km): void
    {
        if (!$equipment || $equipment instanceof Asset) {
            return;
        }

        $changes = [];
        if (is_numeric($hours) && (float) $hours > (InspectionSchedule::currentReading($equipment, 'hours') ?? 0)) {
            $changes['hours'] = (string) (float) $hours;
        }
        if (is_numeric($km) && (float) $km > (InspectionSchedule::currentReading($equipment, 'km') ?? 0)) {
            $changes['mileage'] = (string) (int) round((float) $km);
        }
        if ($changes) {
            $equipment->forceFill($changes)->save();
        }
    }

    // ---------------------------------------------------------------
    // Hour meter / odometer reading for one unit
    // ---------------------------------------------------------------

    public function openReading()
    {
        $this->reset(['reading_unit_id', 'reading_hours', 'reading_km', 'reading_mode', 'reading_search', 'fleet_readings']);
        $this->reading_equipment = 'Horse';
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-readingModal');
    }

    public function updatedReadingEquipment()
    {
        $this->reset(['reading_unit_id', 'reading_hours', 'reading_km', 'reading_search', 'fleet_readings']);
        $this->resetErrorBag();
    }

    public function updatedReadingMode()
    {
        $this->resetErrorBag();
    }

    /**
     * Entire Fleet: save every row that has a new hour meter / odometer typed.
     * Blank = unchanged. A reading below the unit's current one is rejected
     * (likely a typo across a long list) — correct those one unit at a time.
     */
    public function saveFleetReadings()
    {
        $model = $this->readingModel();
        if (!$model) {
            return;
        }

        $rows = collect($this->fleet_readings)
            ->map(fn ($r) => ['hours' => trim((string) ($r['hours'] ?? '')), 'km' => trim((string) ($r['km'] ?? ''))])
            ->filter(fn ($r) => $r['hours'] !== '' || $r['km'] !== '');

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['fleet_readings' => 'Enter at least one new reading.']);
        }

        $units = $model::whereIn('id', $rows->keys())->get()->keyBy('id');
        $errors = [];
        $updates = [];

        foreach ($rows as $id => $row) {
            $unit = $units->get($id);
            if (!$unit) {
                continue;
            }
            $changes = [];
            foreach (['hours' => 'hours', 'km' => 'mileage'] as $dim => $column) {
                $value = $row[$dim];
                if ($value === '') {
                    continue;
                }
                if (!is_numeric($value) || (float) $value < 0) {
                    $errors["fleet_readings.{$id}.{$dim}"] = 'Enter 0 or more.';
                    continue;
                }
                $current = InspectionSchedule::currentReading($unit, $dim);
                if ($current !== null && (float) $value < $current) {
                    $errors["fleet_readings.{$id}.{$dim}"] = 'Below current (' . MaintenanceItem::formatNumber($current) . ').';
                    continue;
                }
                $changes[$column] = $dim === 'hours' ? (string) (float) $value : (string) (int) round((float) $value);
            }
            if ($changes) {
                $updates[$id] = $changes;
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors + ['fleet_readings' => 'Fix the highlighted readings — nothing was saved.']);
        }

        DB::transaction(function () use ($units, $updates) {
            foreach ($updates as $id => $changes) {
                $units[$id]->forceFill($changes)->save();
            }
        });

        $this->fleet_readings = [];
        $this->dispatchBrowserEvent('hide-readingModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Readings updated for ' . count($updates) . ' ' . strtolower($this->reading_equipment) . (count($updates) === 1 ? '' : 's') . '.',
        ]);
    }

    public function updatedReadingUnitId($id)
    {
        $model = $this->readingModel();
        $equipment = $model && $id ? $model::find($id) : null;
        $this->reading_hours = $equipment ? InspectionSchedule::currentReading($equipment, 'hours') : null;
        $this->reading_km = $equipment ? InspectionSchedule::currentReading($equipment, 'km') : null;
    }

    private function readingModel(): ?string
    {
        return ['Horse' => Horse::class, 'Vehicle' => Vehicle::class, 'Trailer' => Trailer::class][$this->reading_equipment] ?? null;
    }

    public function saveReading()
    {
        $this->validate([
            'reading_equipment' => 'required|in:Horse,Vehicle,Trailer',
            'reading_unit_id' => 'required|integer',
            'reading_hours' => 'nullable|numeric|min:0',
            'reading_km' => 'nullable|numeric|min:0',
        ], ['reading_unit_id.required' => 'Select a unit.']);

        $equipment = $this->readingModel()::findOrFail($this->reading_unit_id);
        $changes = [];
        if (filled($this->reading_hours)) {
            $changes['hours'] = (string) (float) $this->reading_hours;
        }
        if (filled($this->reading_km)) {
            $changes['mileage'] = (string) (int) round((float) $this->reading_km);
        }
        if ($changes) {
            $equipment->forceFill($changes)->save();
        }

        $this->dispatchBrowserEvent('hide-readingModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Reading updated for ' . InspectionSchedule::labelFor($equipment) . '.']);
    }

    // ---------------------------------------------------------------
    // Maintenance Planner report
    // ---------------------------------------------------------------

    public function exportPlanner()
    {
        $export = new MaintenancePlannerExport([
            'equipment' => $this->filterEquipment ?: null,
            'item_id' => $this->filterItem ?: null,
            'status' => $this->filterStatus ?: null,
        ]);

        return Excel::download($export, 'workshop_maintenance_planner_' . now()->format('Y_m_d') . '.xlsx');
    }

    // ---------------------------------------------------------------

    private function equipmentOptions(string $equipment, ?string $search = null)
    {
        $search = trim((string) $search);

        return match ($equipment) {
            'Horse' => Horse::selectable()->operational()->with('horse_make:id,name', 'horse_model:id,name')
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('registration_number', 'like', "%{$search}%")->orWhere('fleet_number', 'like', "%{$search}%")))
                ->get()->pipe(fn ($c) => FleetIdentifier::sortCollection($c)),
            'Vehicle' => Vehicle::selectable()->operational()->with('vehicle_make:id,name', 'vehicle_model:id,name')
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('registration_number', 'like', "%{$search}%")->orWhere('fleet_number', 'like', "%{$search}%")))
                ->get()->pipe(fn ($c) => FleetIdentifier::sortCollection($c)),
            'Trailer' => Trailer::selectable()->operational()
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('registration_number', 'like', "%{$search}%")->orWhere('fleet_number', 'like', "%{$search}%")))
                ->get()->pipe(fn ($c) => FleetIdentifier::sortCollection($c)),
            default => Asset::with('product:id,name')->where('disposed', 0)->where('status', 1)
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))))
                ->get()->sortBy('product.name')->values(),
        };
    }

    private function schedulesQuery()
    {
        $search = trim((string) $this->search);

        return InspectionSchedule::query()
            ->with(['horse', 'vehicle', 'trailer', 'asset.product', 'maintenance_item', 'service_type', 'problem_category', 'creator'])
            ->where('type', $this->type)
            ->when($this->isMaintenance() && $this->filterEquipment, function ($q) {
                $q->whereNotNull(['Horse' => 'horse_id', 'Vehicle' => 'vehicle_id', 'Trailer' => 'trailer_id', 'Asset' => 'asset_id'][$this->filterEquipment] ?? 'horse_id');
            })
            ->when($this->isMaintenance() && $this->filterItem, fn ($q) => $q->where('maintenance_item_id', $this->filterItem))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('inspection_schedule_number', 'like', "%{$search}%")
                        ->orWhere('trigger_type', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('maintenance_item', fn ($i) => $i->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('horse', fn ($h) => $h->where('registration_number', 'like', "%{$search}%")->orWhere('fleet_number', 'like', "%{$search}%"))
                        ->orWhereHas('vehicle', fn ($h) => $h->where('registration_number', 'like', "%{$search}%")->orWhere('fleet_number', 'like', "%{$search}%"))
                        ->orWhereHas('trailer', fn ($h) => $h->where('registration_number', 'like', "%{$search}%")->orWhere('fleet_number', 'like', "%{$search}%"));
                });
            })
            ->orderBy('id', 'desc');
    }

    public function render()
    {
        $equipmentList = $this->equipmentOptions($this->equipment, [
            'Horse' => $this->searchHorse, 'Vehicle' => $this->searchVehicle,
            'Trailer' => $this->searchTrailer, 'Asset' => $this->searchAsset,
        ][$this->equipment] ?? null);

        // Live hour meter / odometer of the unit picked in the Add/Edit form.
        $selectedReading = null;
        if ($unit = $this->selectedReadingUnit()) {
            $selectedReading = [
                'hours' => InspectionSchedule::currentReading($unit, 'hours'),
                'km' => InspectionSchedule::currentReading($unit, 'km'),
            ];
        }

        $query = $this->schedulesQuery();
        $summary = [];

        if ($this->isMaintenance()) {
            // Due status depends on each unit's live hours/mileage, so it is worked out in PHP.
            $all = $query->get();
            $statuses = $all->mapWithKeys(fn ($s) => [$s->id => strtolower((string) $s->status) === 'inactive' ? 'inactive' : $s->dueStatus()]);
            $summary = $statuses->countBy()->all();

            if ($this->filterStatus) {
                $all = $all->filter(fn ($s) => $statuses[$s->id] === $this->filterStatus)->values();
            }

            $page = $this->page ?? 1;
            $schedules = new LengthAwarePaginator($all->forPage($page, 15)->values(), $all->count(), 15, $page, ['path' => request()->url()]);
        } else {
            $schedules = $query->paginate(10);
        }

        return view('livewire.inspection-schedules.index', [
            'inspection_schedules' => $schedules,
            'summary' => $summary,
            'equipmentList' => $equipmentList,
            'selectedReading' => $selectedReading,
            'items' => $this->isMaintenance() ? MaintenanceItem::orderBy('name')->get() : collect(),
            'formItems' => $this->isMaintenance() ? MaintenanceItem::active()->forEquipment($this->equipment)->orderBy('name')->get() : collect(),
            'bulkItems' => $this->isMaintenance() ? MaintenanceItem::active()->orderBy('name')->get() : collect(),
            'bulkUnits' => $this->isMaintenance() ? $this->equipmentOptions($this->bulk_equipment) : collect(),
            'readingUnits' => $this->isMaintenance()
                ? $this->equipmentOptions($this->reading_equipment, $this->reading_mode === 'fleet' ? $this->reading_search : null)
                : collect(),
            'service_types' => ServiceType::orderBy('name', 'asc')->get(),
            'problem_categories' => ProblemCategory::orderBy('name', 'asc')->get(),
        ]);
    }
}
