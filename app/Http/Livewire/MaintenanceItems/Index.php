<?php

namespace App\Http\Livewire\MaintenanceItems;

use App\Models\MaintenanceItem;
use Database\Seeders\MaintenanceItemSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tracked maintenance items (the planner's "set constants"), managed on the
 * Maintenance Schedules page.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $item_id;
    public $name;
    public $category;
    public $units = ['hours'];
    public $intervals = ['hours' => null, 'km' => null, 'days' => null];
    public $warnings = ['hours' => null, 'km' => null, 'days' => null];
    public $applies_to = ['horse', 'vehicle', 'trailer'];
    public $is_monitoring = false;
    public $description;
    public $status = 1;
    public $delete_id;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('maintenance_items', 'name')->ignore($this->item_id)->whereNull('deleted_at')],
            'category' => 'nullable|string|max:191',
            'units' => 'required|array|min:1',
            'units.*' => [Rule::in(array_keys(MaintenanceItem::UNITS))],
            'intervals.hours' => (in_array('hours', (array) $this->units) ? 'required' : 'nullable') . '|numeric|gt:0',
            'intervals.km' => (in_array('km', (array) $this->units) ? 'required' : 'nullable') . '|numeric|gt:0',
            'intervals.days' => (in_array('days', (array) $this->units) ? 'required' : 'nullable') . '|numeric|gt:0',
            'warnings.*' => 'nullable|numeric|min:0',
            'applies_to' => 'required|array|min:1',
            'applies_to.*' => [Rule::in(array_keys(MaintenanceItem::APPLIES_TO))],
            'is_monitoring' => 'boolean',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ];
    }

    /** Assets have no hour meter or odometer: untick them once Hours or Km is ticked. */
    public function updatedUnits()
    {
        if (array_diff((array) $this->units, ['days'])) {
            $this->applies_to = array_values(array_diff((array) $this->applies_to, ['asset']));
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetInputFields()
    {
        $this->reset(['item_id', 'name', 'category', 'description', 'delete_id', 'intervals', 'warnings']);
        $this->units = ['hours'];
        $this->applies_to = ['horse', 'vehicle', 'trailer'];
        $this->is_monitoring = false;
        $this->status = 1;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->dispatchBrowserEvent('show-maintenanceItemModal');
    }

    public function edit($id)
    {
        $this->resetInputFields();
        $item = MaintenanceItem::findOrFail($id);

        $this->item_id = $item->id;
        $this->name = $item->name;
        $this->category = $item->category;
        $this->units = $item->units();
        foreach (array_keys(MaintenanceItem::UNITS) as $unit) {
            $interval = $item->{"interval_{$unit}"};
            $warning = $item->{"warning_{$unit}"};
            $this->intervals[$unit] = $interval !== null ? MaintenanceItem::formatNumber($interval) : null;
            $this->warnings[$unit] = $warning !== null ? MaintenanceItem::formatNumber($warning) : null;
        }
        $this->applies_to = $item->appliesToList();
        $this->is_monitoring = (bool) $item->is_monitoring;
        $this->description = $item->description;
        $this->status = $item->status ? 1 : 0;

        $this->dispatchBrowserEvent('show-maintenanceItemModal');
    }

    public function save()
    {
        $this->validate(null, [
            'units.required' => 'Tick at least one of Hours, Km or Days.',
            'intervals.*.required' => 'Enter the interval for each unit ticked.',
        ]);

        // Assets have no hour meter or odometer.
        if (in_array('asset', (array) $this->applies_to) && array_diff((array) $this->units, ['days'])) {
            $this->addError('applies_to', 'Assets can only be tracked by Days. Untick Assets or track by Days only.');

            return;
        }

        $units = MaintenanceItem::parseUnits($this->units);
        $data = [
            'name' => strtoupper(trim($this->name)),
            'category' => $this->category,
            'interval_unit' => implode(',', $units),
            'applies_to' => MaintenanceItem::encodeAppliesTo((array) $this->applies_to),
            'is_monitoring' => (bool) $this->is_monitoring,
            'description' => $this->description,
            'status' => (bool) $this->status,
        ];
        foreach (array_keys(MaintenanceItem::UNITS) as $unit) {
            $on = in_array($unit, $units, true);
            $data["interval_{$unit}"] = $on ? $this->intervals[$unit] : null;
            $data["warning_{$unit}"] = $on && filled($this->warnings[$unit] ?? null) ? $this->warnings[$unit] : null;
        }

        if ($this->item_id) {
            // Unticking a type that already tracks the item would leave orphan schedules.
            $removed = array_diff(array_keys(MaintenanceItem::APPLIES_TO), $this->applies_to);
            $inUse = collect($removed)->filter(fn ($type) => \App\Models\InspectionSchedule::where('maintenance_item_id', $this->item_id)
                ->whereNotNull($type . '_id')->exists());
            if ($inUse->isNotEmpty()) {
                $this->addError('applies_to', 'Some ' . $inUse->map(fn ($t) => MaintenanceItem::APPLIES_TO[$t])->implode(', ')
                    . ' already track this item. Delete those schedules first, then untick them.');

                return;
            }

            MaintenanceItem::findOrFail($this->item_id)->update($data);
            $message = 'Tracked Item Updated Successfully!!';
        } else {
            MaintenanceItem::create($data + ['created_by' => Auth::id()]);
            $message = 'Tracked Item Created Successfully!!';
        }

        $this->dispatchBrowserEvent('hide-maintenanceItemModal');
        $this->resetInputFields();
        $this->emit('maintenanceItemsChanged');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $message]);
    }

    public function confirmDelete($id)
    {
        $this->delete_id = $id;
        $this->dispatchBrowserEvent('show-maintenanceItemDeleteModal');
    }

    public function destroy()
    {
        $item = MaintenanceItem::withCount(['schedules' => fn ($q) => $q->whereNull('deleted_at')])->find($this->delete_id);
        $this->dispatchBrowserEvent('hide-maintenanceItemDeleteModal');

        if (!$item) {
            return;
        }

        if ($item->schedules_count > 0) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => "{$item->name} is tracked on {$item->schedules_count} schedule(s). Remove those schedules or set the item Inactive instead.",
            ]);

            return;
        }

        $item->delete();
        $this->delete_id = null;
        $this->emit('maintenanceItemsChanged');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Tracked Item Deleted Successfully!!']);
    }

    public function loadStandardItems()
    {
        $created = (new MaintenanceItemSeeder())->run();
        $this->emit('maintenanceItemsChanged');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $created ? "{$created} standard item(s) added." : 'All standard items are already loaded.',
        ]);
    }

    public function render()
    {
        $search = trim((string) $this->search);

        $items = MaintenanceItem::query()
            ->withCount(['schedules' => fn ($q) => $q->whereNull('deleted_at')])
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15);

        return view('livewire.maintenance-items.index', ['items' => $items]);
    }
}
