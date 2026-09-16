<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\FreightServiceType;
use Livewire\Component;

class ServiceTypes extends Component
{
    public $service_type_id;
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
        $serviceType = FreightServiceType::findOrFail($id);
        $this->service_type_id = $serviceType->id;
        $this->name = $serviceType->name;
        $this->description = $serviceType->description;
        $this->is_locked = $serviceType->is_locked;
    }

    public function save()
    {
        $this->validate();

        if ($this->service_type_id) {
            $existing = FreightServiceType::findOrFail($this->service_type_id);

            if ($existing->is_locked) {
                $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This service type is locked and cannot be edited.']);
                return;
            }
        }

        FreightServiceType::updateOrCreate(
            ['id' => $this->service_type_id],
            [
                'name' => $this->name,
                'description' => $this->description,
                'is_locked' => $this->is_locked,
            ]
        );

        $this->reset(['service_type_id', 'name', 'description', 'is_locked']);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Service type saved.']);
    }

    public function delete($id)
    {
        $serviceType = FreightServiceType::findOrFail($id);

        if ($serviceType->is_locked) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This service type is locked and cannot be deleted.']);
            return;
        }

        $serviceType->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Service type removed.']);
    }

    public function render()
    {
        return view('livewire.freight.settings.service-types', [
            'serviceTypes' => FreightServiceType::orderBy('name', 'asc')->get(),
        ]);
    }
}
