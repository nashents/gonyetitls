<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Models\Agent;
use App\Models\Broker;
use App\Models\Cargo;
use App\Models\ClearingAgent;
use App\Models\Consignee;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\FreightServiceType;
use App\Models\Location;
use App\Models\Quotation;
use App\Models\ShipmentParty;
use App\Models\Transporter;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Freight\FreightJobService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $employee;
    public $department_names = [];
    public $role_names = [];

    // dropdown collections
    public $customers;
    public $freight_service_types;
    public $currencies;
    public $countries;
    public $officers;
    public $quotations;
    public $locations;
    public $cargos;
    public $partyOptions = [];
    public $partyModels = [];

    // Customer & Service
    public $customer_id;
    public $customer_reference;
    public $freight_service_type_id;
    public $salesperson_id;
    public $operations_officer_id;
    public $clearing_officer_id;
    public $quotation_id;
    public $incoterm;
    public $currency_id;
    public $import_export_type;
    public $primary_transport_mode;
    public $origin;
    public $destination;
    public $origin_country_id;
    public $destination_country_id;
    public $notes;

    // Shipment Details
    public $mode;
    public $shipment_type;
    public $port_of_loading_id;
    public $port_of_discharge_id;
    public $place_of_receipt_id;
    public $place_of_delivery_id;
    public $etd;
    public $eta;
    public $booking_reference;
    public $freight_terms;

    // Cargo (repeatable)
    public $cargo_rows = [];

    // Parties (repeatable)
    public $party_rows = [];

    public function mount()
    {
        $user = Auth::user();
        $this->employee = $user->employee;

        foreach ($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
        foreach ($user->roles as $role) {
            $this->role_names[] = $role->name;
        }

        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->freight_service_types = FreightServiceType::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->countries = Country::orderBy('name', 'asc')->get();
        $this->officers = User::orderBy('name', 'asc')->get();
        $this->quotations = Quotation::with('customer')
            ->whereYear('date', date('Y'))
            ->latest()
            ->get();
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
        $this->cargo_rows[] = [
            'cargo_id' => null,
            'commodity' => null,
            'hs_code' => null,
            'quantity' => null,
            'uom' => null,
            'packages' => null,
            'gross_weight' => null,
            'is_dangerous_goods' => false,
        ];
    }

    public function removeCargoRow($index)
    {
        unset($this->cargo_rows[$index]);
        $this->cargo_rows = array_values($this->cargo_rows);
    }

    public function addPartyRow()
    {
        $this->party_rows[] = [
            'party_type' => null,
            'party_id' => null,
            'role' => null,
        ];
    }

    public function removePartyRow($index)
    {
        unset($this->party_rows[$index]);
        $this->party_rows = array_values($this->party_rows);
    }

    protected function rules()
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'freight_service_type_id' => 'nullable|integer|exists:freight_service_types,id',
            'primary_transport_mode' => 'required|string',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'mode' => 'required|string',
            'cargo_rows.*.commodity' => 'nullable|string',
            'party_rows.*.role' => 'nullable|string',
        ];
    }

    public function store(FreightJobService $service)
    {
        $this->validate();

        $jobData = [
            'user_id' => Auth::id(),
            'customer_id' => $this->customer_id,
            'customer_reference' => $this->customer_reference,
            'company_id' => $this->employee->company_id,
            'freight_service_type_id' => $this->freight_service_type_id,
            'salesperson_id' => $this->salesperson_id,
            'operations_officer_id' => $this->operations_officer_id,
            'clearing_officer_id' => $this->clearing_officer_id,
            'quotation_id' => $this->quotation_id,
            'incoterm' => $this->incoterm,
            'currency_id' => $this->currency_id,
            'import_export_type' => $this->import_export_type,
            'primary_transport_mode' => $this->primary_transport_mode,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'origin_country_id' => $this->origin_country_id,
            'destination_country_id' => $this->destination_country_id,
            'notes' => $this->notes,
        ];

        $shipmentData = [
            'user_id' => Auth::id(),
            'mode' => $this->mode,
            'shipment_type' => $this->shipment_type,
            'port_of_loading_id' => $this->port_of_loading_id,
            'port_of_discharge_id' => $this->port_of_discharge_id,
            'place_of_receipt_id' => $this->place_of_receipt_id,
            'place_of_delivery_id' => $this->place_of_delivery_id,
            'etd' => $this->etd ?: null,
            'eta' => $this->eta ?: null,
            'booking_reference' => $this->booking_reference,
            'freight_terms' => $this->freight_terms,
            'incoterm' => $this->incoterm,
        ];

        $cargoRows = collect($this->cargo_rows)
            ->filter(fn ($row) => filled($row['commodity']) || filled($row['cargo_id']))
            ->values()
            ->all();

        $partyRows = collect($this->party_rows)
            ->filter(fn ($row) => filled($row['party_type']) && filled($row['party_id']))
            ->values()
            ->all();

        $job = $service->create($jobData, $shipmentData, $cargoRows, $partyRows);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Freight Job ' . $job->job_number . ' created successfully!',
        ]);

        return redirect()->route('freight.jobs.show', $job->id);
    }

    public function render()
    {
        return view('livewire.freight.jobs.create');
    }
}
