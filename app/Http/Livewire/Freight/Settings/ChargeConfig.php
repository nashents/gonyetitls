<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\ChargeFreeDayPolicy;
use App\Models\ChargeRateTier;
use App\Models\ContainerChargeExposure;
use App\Models\Currency;
use App\Models\Vendor;
use Livewire\Component;

class ChargeConfig extends Component
{
    public $vendors;
    public $currencies;

    // Free Day Policy form
    public $policy_id;
    public $policy_charge_type;
    public $policy_vendor_id;
    public $policy_free_days;

    // Rate Tier form
    public $tier_id;
    public $tier_charge_type;
    public $tier_vendor_id;
    public $tier_day_from;
    public $tier_day_to;
    public $tier_rate;
    public $tier_currency_id;

    public function mount()
    {
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
    }

    protected function rules()
    {
        return [
            'policy_charge_type' => 'required_with:policy_free_days|string',
        ];
    }

    public function editPolicy($id)
    {
        $policy = ChargeFreeDayPolicy::findOrFail($id);
        $this->policy_id = $policy->id;
        $this->policy_charge_type = $policy->charge_type;
        $this->policy_vendor_id = $policy->shipping_line_vendor_id;
        $this->policy_free_days = $policy->free_days;
    }

    public function savePolicy()
    {
        $this->validate([
            'policy_charge_type' => 'required|string',
            'policy_free_days' => 'required|integer|min:0',
        ]);

        $duplicate = ChargeFreeDayPolicy::where('charge_type', $this->policy_charge_type)
            ->where('shipping_line_vendor_id', $this->policy_vendor_id ?: null)
            ->when($this->policy_id, fn ($q) => $q->where('id', '!=', $this->policy_id))
            ->exists();

        if ($duplicate) {
            $this->addError('policy_charge_type', 'A policy already exists for this charge type and shipping line.');
            return;
        }

        ChargeFreeDayPolicy::updateOrCreate(
            ['id' => $this->policy_id],
            [
                'charge_type' => $this->policy_charge_type,
                'shipping_line_vendor_id' => $this->policy_vendor_id ?: null,
                'free_days' => $this->policy_free_days,
            ]
        );

        $this->resetPolicyForm();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Free day policy saved.']);
    }

    public function deletePolicy($id)
    {
        ChargeFreeDayPolicy::findOrFail($id)->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Free day policy removed.']);
    }

    private function resetPolicyForm()
    {
        $this->reset(['policy_id', 'policy_charge_type', 'policy_vendor_id', 'policy_free_days']);
    }

    public function editTier($id)
    {
        $tier = ChargeRateTier::findOrFail($id);
        $this->tier_id = $tier->id;
        $this->tier_charge_type = $tier->charge_type;
        $this->tier_vendor_id = $tier->shipping_line_vendor_id;
        $this->tier_day_from = $tier->day_from;
        $this->tier_day_to = $tier->day_to;
        $this->tier_rate = $tier->rate;
        $this->tier_currency_id = $tier->currency_id;
    }

    public function saveTier()
    {
        $this->validate([
            'tier_charge_type' => 'required|string',
            'tier_day_from' => 'required|integer|min:1',
            'tier_day_to' => 'nullable|integer|gte:tier_day_from',
            'tier_rate' => 'required|numeric|min:0',
        ]);

        ChargeRateTier::updateOrCreate(
            ['id' => $this->tier_id],
            [
                'charge_type' => $this->tier_charge_type,
                'shipping_line_vendor_id' => $this->tier_vendor_id ?: null,
                'day_from' => $this->tier_day_from,
                'day_to' => $this->tier_day_to ?: null,
                'rate' => $this->tier_rate,
                'currency_id' => $this->tier_currency_id ?: null,
            ]
        );

        $this->resetTierForm();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Rate tier saved.']);
    }

    public function deleteTier($id)
    {
        ChargeRateTier::findOrFail($id)->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Rate tier removed.']);
    }

    private function resetTierForm()
    {
        $this->reset(['tier_id', 'tier_charge_type', 'tier_vendor_id', 'tier_day_from', 'tier_day_to', 'tier_rate', 'tier_currency_id']);
    }

    public function render()
    {
        return view('livewire.freight.settings.charge-config', [
            'chargeTypes' => ContainerChargeExposure::CHARGE_TYPES,
            'policies' => ChargeFreeDayPolicy::with('shipping_line_vendor')->orderBy('charge_type')->get(),
            'tiers' => ChargeRateTier::with(['shipping_line_vendor', 'currency'])->orderBy('charge_type')->orderBy('day_from')->get(),
        ]);
    }
}
