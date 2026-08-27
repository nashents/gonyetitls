<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\ChargeType;
use Livewire\Component;

class ChargeTypes extends Component
{
    public $charge_type_id;
    public $name;
    public $description;
    public $is_locked = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function edit($id)
    {
        $chargeType = ChargeType::findOrFail($id);
        $this->charge_type_id = $chargeType->id;
        $this->name = $chargeType->name;
        $this->description = $chargeType->description;
        $this->is_locked = $chargeType->is_locked;
    }

    public function save()
    {
        $this->validate();

        if ($this->charge_type_id) {
            $existing = ChargeType::findOrFail($this->charge_type_id);

            if ($existing->is_locked) {
                $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge type is locked and cannot be edited.']);
                return;
            }
        }

        ChargeType::updateOrCreate(
            ['id' => $this->charge_type_id],
            [
                'name' => $this->name,
                'description' => $this->description,
                'is_locked' => $this->is_locked,
            ]
        );

        $this->reset(['charge_type_id', 'name', 'description', 'is_locked']);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge type saved.']);
    }

    public function delete($id)
    {
        $chargeType = ChargeType::findOrFail($id);

        if ($chargeType->is_locked) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge type is locked and cannot be deleted.']);
            return;
        }

        $chargeType->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge type removed.']);
    }

    public function render()
    {
        return view('livewire.freight.settings.charge-types', [
            'chargeTypes' => ChargeType::orderBy('name', 'asc')->get(),
        ]);
    }
}
