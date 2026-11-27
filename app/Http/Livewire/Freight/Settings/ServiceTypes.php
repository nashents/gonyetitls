<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\FreightJob;
use App\Models\FreightRateCard;
use App\Models\FreightServiceType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

/**
 * Freight > Master > Service Types. Active types are offered on jobs, rate
 * cards and consolidation clients (FreightServiceType::selectable); inactive
 * ones stay on existing records. A type in use can't be deleted - set it
 * Inactive instead (same rule as Customs Offices).
 */
class ServiceTypes extends Component
{
    use FreightEmbeddedPagination;

    public $service_type_id;
    public $name;
    public $description;
    public $is_active = 1;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('freight_service_types', 'name')->ignore($this->service_type_id)->whereNull('deleted_at')],
            'is_active' => 'required|boolean',
        ];
    }

    protected $validationAttributes = ['is_active' => 'status'];

    public function edit($id)
    {
        $serviceType = FreightServiceType::findOrFail($id);
        $this->service_type_id = $serviceType->id;
        $this->name = $serviceType->name;
        $this->description = $serviceType->description;
        $this->is_active = $serviceType->is_active ? 1 : 0;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => trim($this->name),
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
        ];

        if ($this->service_type_id) {
            FreightServiceType::findOrFail($this->service_type_id)->update($data);
        } else {
            FreightServiceType::create($data + ['user_id' => Auth::id()]);
        }

        $this->resetForm();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Service type saved.']);
    }

    public function toggleActive($id)
    {
        $serviceType = FreightServiceType::findOrFail($id);
        $serviceType->update(['is_active' => !$serviceType->is_active]);

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $serviceType->name . ($serviceType->is_active ? ' activated.' : ' deactivated - no longer offered on new jobs.')]);
    }

    public function delete($id)
    {
        $serviceType = FreightServiceType::findOrFail($id);

        $jobs = FreightJob::withTrashed()->where('freight_service_type_id', $serviceType->id)->count();
        $cards = FreightRateCard::where('freight_service_type_id', $serviceType->id)->count();

        if ($jobs || $cards) {
            $used = collect([$jobs ? "{$jobs} freight job(s)" : null, $cards ? "{$cards} rate card(s)" : null])->filter()->implode(' and ');
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => "{$serviceType->name} is used on {$used}. Set it Inactive instead."]);
            return;
        }

        $serviceType->delete();

        if ((int) $this->service_type_id === (int) $id) {
            $this->resetForm();
        }

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Service type removed.']);
    }

    public function resetForm()
    {
        $this->reset(['service_type_id', 'name', 'description', 'is_active']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.freight.settings.service-types', [
            'serviceTypes' => FreightServiceType::withCount('freight_jobs')->orderBy('name', 'asc')->orderBy('id')->paginate(10),
        ]);
    }
}
