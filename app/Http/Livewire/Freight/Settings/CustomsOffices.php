<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\Country;
use App\Models\CustomsDeclaration;
use App\Models\CustomsOffice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Freight > Master > Customs Offices: the Customs Declaration dropdown list.
 * Declarations store the office NAME, so a rename is carried onto them.
 */
class CustomsOffices extends Component
{
    public $office_id;
    public $name;
    public $country_id;
    public $is_active = true;

    public $countries = [];

    public function mount()
    {
        $this->countries = Country::orderBy('name', 'asc')->get(['id', 'name']);
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('customs_offices', 'name')->ignore($this->office_id)],
            'country_id' => 'nullable|exists:countries,id',
        ];
    }

    public function edit($id)
    {
        $office = CustomsOffice::findOrFail($id);
        $this->office_id = $office->id;
        $this->name = $office->name;
        $this->country_id = $office->country_id;
        $this->is_active = $office->is_active;
        $this->resetValidation();
    }

    public function save()
    {
        $this->name = preg_replace('/\s+/', ' ', trim((string) $this->name));
        $this->validate();

        $renamed = 0;

        DB::transaction(function () use (&$renamed) {
            if ($this->office_id) {
                $office = CustomsOffice::findOrFail($this->office_id);
                $oldName = $office->name;

                $office->update([
                    'name' => $this->name,
                    'country_id' => $this->country_id ?: null,
                    'is_active' => (bool) $this->is_active,
                ]);

                if ($oldName !== $this->name) {
                    // One by one so the audit trail records each declaration change.
                    CustomsDeclaration::where('customs_office', $oldName)->get()
                        ->each(function ($declaration) use (&$renamed) {
                            $declaration->update(['customs_office' => $this->name]);
                            $renamed++;
                        });
                }
            } else {
                CustomsOffice::create([
                    'name' => $this->name,
                    'country_id' => $this->country_id ?: null,
                    'is_active' => (bool) $this->is_active,
                    'created_by' => Auth::id(),
                ]);
            }
        });

        $this->resetForm();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Customs office saved.' . ($renamed ? " {$renamed} declaration(s) updated to the new name." : '')]);
    }

    public function toggleActive($id)
    {
        $office = CustomsOffice::findOrFail($id);
        $office->update(['is_active' => !$office->is_active]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $office->name . ($office->is_active ? ' activated.' : ' deactivated.')]);
    }

    public function delete($id)
    {
        $office = CustomsOffice::findOrFail($id);
        $used = CustomsDeclaration::where('customs_office', $office->name)->count();

        if ($used) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => "{$office->name} is used on {$used} customs declaration(s). Deactivate it instead."]);
            return;
        }

        $office->delete();

        if ((int) $this->office_id === (int) $id) {
            $this->resetForm();
        }

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Customs office removed.']);
    }

    public function resetForm()
    {
        $this->reset(['office_id', 'name', 'country_id', 'is_active']);
        $this->resetValidation();
    }

    public function render()
    {
        $offices = CustomsOffice::with('country')->orderBy('name', 'asc')->get();
        $usage = CustomsDeclaration::whereIn('customs_office', $offices->pluck('name'))
            ->select('customs_office', DB::raw('COUNT(*) as total'))
            ->groupBy('customs_office')
            ->pluck('total', 'customs_office');

        return view('livewire.freight.settings.customs-offices', [
            'offices' => $offices,
            'usage' => $usage,
        ]);
    }
}
