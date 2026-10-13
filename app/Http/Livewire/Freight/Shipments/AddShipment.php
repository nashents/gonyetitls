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
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Models\ShipmentParty;
use App\Models\Transporter;
use App\Models\Vendor;
use App\Services\Freight\FreightJobService;
use Livewire\Component;

class AddShipment extends Component
{
    public $job;
    public $locations;
    public $loading_points;
    public $offloading_points;
    public $cargos;
    public $partyOptions = [];
    public $partyModels = [];

    public $mode;
    public $shipment_type;
    public $loading_point_id;
    public $offloading_point_id;
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
        $this->loading_points = LoadingPoint::orderBy('name', 'asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name', 'asc')->get();
        $this->cargos = Cargo::orderBy('name', 'asc')->get();

        $this->partyOptions = ShipmentParty::partyTypeOptions();
        $this->partyModels = [
            'customer' => Customer::orderBy('name')->get(['id', 'name'])->toArray(),
            'vendor' => Vendor::orderBy('name')->get(['id', 'name'])->toArray(),
            'consignee' => Consignee::orderBy('name')->get(['id', 'name'])->toArray(),
            'broker' => Broker::orderBy('name')->get(['id', 'name'])->toArray(),
            'agent' => Agent::orderBy('name')->get(['id', 'name'])->toArray(),
            'transporter' => Transporter::orderBy('name')->get(['id', 'name'])->toArray(),
            'clearing_agent' => ClearingAgent::orderBy('name')->get(['id', 'name'])->toArray(),
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

    public function refresh($category)
    {
        if ($category === 'locations') {
            $this->locations = Location::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Locations Refreshed Successfully!!.']);
        } elseif ($category === 'cargos') {
            $this->cargos = Cargo::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Cargo Types Refreshed Successfully!!.']);
        } elseif ($category === 'loading_points') {
            $this->loading_points = LoadingPoint::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Loading Points Refreshed Successfully!!.']);
        } elseif ($category === 'offloading_points') {
            $this->offloading_points = OffloadingPoint::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Offloading Points Refreshed Successfully!!.']);
        }
    }

    public function refreshPartyOptions($index)
    {
        $type = $this->party_rows[$index]['party_type'] ?? null;

        if (!$type || !isset($this->partyModels[$type])) {
            return;
        }

        $modelClass = [
            'customer' => Customer::class,
            'vendor' => Vendor::class,
            'consignee' => Consignee::class,
            'broker' => Broker::class,
            'agent' => Agent::class,
            'transporter' => Transporter::class,
            'clearing_agent' => ClearingAgent::class,
        ][$type];

        $this->partyModels[$type] = $modelClass::orderBy('name')->get(['id', 'name'])->toArray();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => ucfirst(str_replace('_', ' ', $type)) . 's Refreshed Successfully!!.',
        ]);
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
            'loading_point_id' => $this->loading_point_id ?: null,
            'offloading_point_id' => $this->offloading_point_id ?: null,
            'booking_reference' => $this->booking_reference,
            'freight_terms' => $this->freight_terms,
            'etd' => $this->etd ?: null,
            'eta' => $this->eta ?: null,
        ], $cargoRows, $partyRows);

        $this->reset(['mode', 'shipment_type', 'loading_point_id', 'offloading_point_id', 'booking_reference', 'freight_terms', 'etd', 'eta', 'cargo_rows', 'party_rows']);
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
