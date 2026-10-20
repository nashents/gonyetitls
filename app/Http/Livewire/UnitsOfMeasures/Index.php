<?php

namespace App\Http\Livewire\UnitsOfMeasures;

use App\Models\UnitsOfMeasure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Trip Management > Master > UOMs. Users add/edit their own units; seeded
 * (is_system) units are read-only and can't be deleted.
 */
class Index extends Component
{
    public $name;
    public $abbreviation;
    public $description;
    public $unit_id;
    public $delete_id;
    public $search = '';

    protected function rules()
    {
        $unique = fn ($column) => Rule::unique('units_of_measures', $column)
            ->ignore($this->unit_id)
            ->whereNull('deleted_at');

        return [
            'name' => ['required', 'string', 'min:1', 'max:100', $unique('name')],
            'abbreviation' => ['required', 'string', 'max:20', $unique('abbreviation')],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updated($field)
    {
        if (in_array($field, ['name', 'abbreviation', 'description'])) {
            $this->validateOnly($field);
        }
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->abbreviation = '';
        $this->description = '';
        $this->unit_id = null;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->dispatchBrowserEvent('show-unitOfMeasureModal');
    }

    public function store()
    {
        $this->unit_id = null;
        $data = $this->validate();

        UnitsOfMeasure::create([
            'user_id' => Auth::id(),
            'name' => trim($data['name']),
            'abbreviation' => trim($data['abbreviation']),
            'description' => $data['description'] ? trim($data['description']) : null,
            'is_system' => false,
        ]);

        $this->dispatchBrowserEvent('hide-unitOfMeasureModal');
        $this->resetInputFields();
        $this->alert('success', 'Unit of Measure Created Successfully!!');
    }

    public function edit($id)
    {
        $unit = UnitsOfMeasure::findOrFail($id);
        if ($unit->is_system) {
            return $this->alert('warning', 'System units of measure cannot be edited.');
        }

        $this->resetInputFields();
        $this->unit_id = $unit->id;
        $this->name = $unit->name;
        $this->abbreviation = $unit->abbreviation;
        $this->description = $unit->description;
        $this->dispatchBrowserEvent('show-unitOfMeasureEditModal');
    }

    public function update()
    {
        $unit = UnitsOfMeasure::findOrFail($this->unit_id);
        if ($unit->is_system) {
            $this->dispatchBrowserEvent('hide-unitOfMeasureEditModal');
            return $this->alert('warning', 'System units of measure cannot be edited.');
        }

        $data = $this->validate();
        $unit->update([
            'name' => trim($data['name']),
            'abbreviation' => trim($data['abbreviation']),
            'description' => $data['description'] ? trim($data['description']) : null,
        ]);

        $this->dispatchBrowserEvent('hide-unitOfMeasureEditModal');
        $this->resetInputFields();
        $this->alert('success', 'Unit of Measure Updated Successfully!!');
    }

    public function confirmDelete($id)
    {
        $unit = UnitsOfMeasure::findOrFail($id);
        if ($unit->is_system) {
            return $this->alert('warning', 'System units of measure cannot be deleted.');
        }

        $this->delete_id = $unit->id;
        $this->dispatchBrowserEvent('show-unitOfMeasureDeleteModal');
    }

    public function delete()
    {
        $unit = UnitsOfMeasure::find($this->delete_id);
        $this->dispatchBrowserEvent('hide-unitOfMeasureDeleteModal');
        $this->delete_id = null;

        if (! $unit) {
            return;
        }
        if ($unit->is_system) {
            return $this->alert('warning', 'System units of measure cannot be deleted.');
        }
        if ($used = $unit->usageCount()) {
            return $this->alert('warning', "{$unit->name} is used on {$used} record(s) and cannot be deleted. Edit it instead.");
        }

        $unit->delete();
        $this->alert('success', 'Unit of Measure Deleted Successfully!!');
    }

    private function alert($type, $message)
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }

    public function render()
    {
        $units = UnitsOfMeasure::query()
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(fn ($q) => $q->where('name', 'like', $term)
                    ->orWhere('abbreviation', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->orderBy('name')
            ->get();

        return view('livewire.units-of-measures.index', [
            'units' => $units,
        ]);
    }
}
