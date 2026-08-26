<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\Agent;
use App\Models\Broker;
use App\Models\Cargo;
use App\Models\ClearingAgent;
use App\Models\Consignee;
use App\Models\Customer;
use App\Models\FreightJob;
use App\Models\Location;
use App\Models\ShipmentParty;
use App\Models\Transporter;
use App\Models\Vendor;
use App\Services\Freight\FreightJobService;
use Livewire\Component;

class AddShipment extends Component
{
    public $job;
    public $locations;
    public $cargos;
    public $partyOptions = [];
    public $partyModels = [];

    public $mode;
    public $shipment_type;
    public $port_of_loading_id;
    public $port_of_discharge_id;
    public $booking_reference;
    public $freight_terms;
    public $etd;
    public $eta;

    public $cargo_rows = [];
    public $party_rows = [];

    public function mount($jobId)
    {
        $this->job = FreightJob::findOrFail($jobId);
        $this->locations = Location::orderBy('name', 'asc')->get();
        $this->cargos = Cargo::orderBy('name', 'asc')->get();

        $this->partyOptions = ShipmentParty::partyTypeOptions();
        $this->partyModels = [
            'customer' => Customer::orderBy('name')->get(['id', 'name']),
            'vendor' => Vendor::orderBy('name')->get(['id', 'name']),
            'consignee' => Consignee::orderBy('name')->get(['id', 'name']),
            'broker' => Broker::orderBy('name')->get(['id', 'name']),
            'agent' => Agent::orderBy('name')->get(['id', 'name']),
            'transporter' => Transporter::orderBy('name')->get(['id', 'name']),
            'clearing_agent' => ClearingAgent::orderBy('name')->get(['id', 'name']),
        ];

        $this->addCargoRow();
        $this->addPartyRow();
    }

    public function addCargoRow()
    {
        $this->cargo_rows[] = ['cargo_id' => null, 'commodity' => null, 'hs_code' => null, 'quantity' => null, 'packages' => null, 'gross_weight' => null];
    }

    public function removeCargoRow($index)
    {
        unset($this->cargo_rows[$index]);
        $this->cargo_rows = array_values($this->cargo_rows);
    }

    public function addPartyRow()
    {
        $this->party_rows[] = ['party_type' => null, 'party_id' => null, 'role' => null];
    }

    public function removePartyRow($index)
    {
        unset($this->party_rows[$index]);
        $this->party_rows = array_values($this->party_rows);
    }

    protected function rules()
    {
        return [
            'mode' => 'required|string',
        ];
    }

    public function store(FreightJobService $service)
    {
        $this->validate();

        $cargoRows = collect($this->cargo_rows)
            ->filter(fn ($row) => filled($row['commodity']) || filled($row['cargo_id']))
            ->values()
            ->all();

        $partyRows = collect($this->party_rows)
            ->filter(fn ($row) => filled($row['party_type']) && filled($row['party_id']))
            ->values()
            ->all();

        $service->addShipment($this->job, [
            'mode' => $this->mode,
            'shipment_type' => $this->shipment_type,
            'port_of_loading_id' => $this->port_of_loading_id ?: null,
            'port_of_discharge_id' => $this->port_of_discharge_id ?: null,
            'booking_reference' => $this->booking_reference,
            'freight_terms' => $this->freight_terms,
            'etd' => $this->etd ?: null,
            'eta' => $this->eta ?: null,
        ], $cargoRows, $partyRows);

        $this->reset(['mode', 'shipment_type', 'port_of_loading_id', 'port_of_discharge_id', 'booking_reference', 'freight_terms', 'etd', 'eta', 'cargo_rows', 'party_rows']);
        $this->addCargoRow();
        $this->addPartyRow();

        $this->dispatchBrowserEvent('hide-addShipmentModal-' . $this->job->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Shipment added to job successfully!']);
        $this->emitUp('shipmentAdded');
    }

    public function render()
    {
        return view('livewire.freight.shipments.add-shipment');
    }
}
