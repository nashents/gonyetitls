<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\Cargo;
use App\Models\ChargeType;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\FreightRateCard;
use App\Models\Location;
use App\Models\Vendor;
use Livewire\Component;

class RateCards extends Component
{
    public $vendors;
    public $customers;
    public $chargeTypes;
    public $locations;
    public $cargos;
    public $currencies;

    public $rate_card_id;
    public $direction = 'buy';
    public $vendor_id;
    public $customer_id;
    public $charge_type_id;
    public $mode;
    public $container_type;
    public $origin_location_id;
    public $destination_location_id;
    public $cargo_id;
    public $currency_id;
    public $rate_basis;
    public $rate;
    public $markup_type;
    public $markup_value;
    public $effective_from;
    public $effective_to;
    public $is_active = true;
    public $notes;

    public function mount()
    {
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->chargeTypes = ChargeType::orderBy('name', 'asc')->get();
        $this->locations = Location::orderBy('name', 'asc')->get();
        $this->cargos = Cargo::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
    }

    protected function rules()
    {
        return [
            'direction' => 'required|in:buy,sell',
            'rate' => 'nullable|numeric',
        ];
    }

    public function edit($id)
    {
        $card = FreightRateCard::findOrFail($id);
        $this->rate_card_id = $card->id;
        $this->direction = $card->direction;
        $this->vendor_id = $card->vendor_id;
        $this->customer_id = $card->customer_id;
        $this->charge_type_id = $card->charge_type_id;
        $this->mode = $card->mode;
        $this->container_type = $card->container_type;
        $this->origin_location_id = $card->origin_location_id;
        $this->destination_location_id = $card->destination_location_id;
        $this->cargo_id = $card->cargo_id;
        $this->currency_id = $card->currency_id;
        $this->rate_basis = $card->rate_basis;
        $this->rate = $card->rate;
        $this->markup_type = $card->markup_type;
        $this->markup_value = $card->markup_value;
        $this->effective_from = optional($card->effective_from)->format('Y-m-d');
        $this->effective_to = optional($card->effective_to)->format('Y-m-d');
        $this->is_active = $card->is_active;
        $this->notes = $card->notes;
    }

    public function save()
    {
        $this->validate();

        FreightRateCard::updateOrCreate(
            ['id' => $this->rate_card_id],
            [
                'direction' => $this->direction,
                'vendor_id' => $this->direction === 'buy' ? ($this->vendor_id ?: null) : null,
                'customer_id' => $this->direction === 'sell' ? ($this->customer_id ?: null) : null,
                'charge_type_id' => $this->charge_type_id ?: null,
                'mode' => $this->mode,
                'container_type' => $this->container_type,
                'origin_location_id' => $this->origin_location_id ?: null,
                'destination_location_id' => $this->destination_location_id ?: null,
                'cargo_id' => $this->cargo_id ?: null,
                'currency_id' => $this->currency_id ?: null,
                'rate_basis' => $this->rate_basis,
                'rate' => $this->rate ?: null,
                'markup_type' => $this->direction === 'sell' ? ($this->markup_type ?: null) : null,
                'markup_value' => $this->direction === 'sell' ? ($this->markup_value ?: null) : null,
                'effective_from' => $this->effective_from ?: null,
                'effective_to' => $this->effective_to ?: null,
                'is_active' => (bool) $this->is_active,
                'notes' => $this->notes,
            ]
        );

        $this->resetForm();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Rate card saved.']);
    }

    public function delete($id)
    {
        FreightRateCard::findOrFail($id)->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Rate card removed.']);
    }

    private function resetForm()
    {
        $this->reset([
            'rate_card_id', 'vendor_id', 'customer_id', 'charge_type_id', 'mode', 'container_type',
            'origin_location_id', 'destination_location_id', 'cargo_id', 'currency_id', 'rate_basis',
            'rate', 'markup_type', 'markup_value', 'effective_from', 'effective_to', 'notes',
        ]);
        $this->direction = 'buy';
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.freight.settings.rate-cards', [
            'rateCards' => FreightRateCard::with(['vendor', 'customer', 'charge_type', 'currency'])
                ->orderBy('direction')->orderBy('id', 'desc')->get(),
            'directions' => FreightRateCard::DIRECTIONS,
            'markupTypes' => FreightRateCard::MARKUP_TYPES,
            'rateBases' => FreightRateCard::RATE_BASES,
        ]);
    }
}
