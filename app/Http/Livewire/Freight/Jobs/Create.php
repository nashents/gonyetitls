<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Http\Livewire\Freight\Jobs\Concerns\FreightJobFormOptions;
use App\Models\FreightJob;
use App\Services\Freight\FreightJobAuthorizationService;
use App\Services\Freight\FreightJobService;
use App\Services\Freight\ShipmentLegService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Create extends Component
{
    use FreightJobFormOptions;

    public $employee;
    public $department_names = [];
    public $role_names = [];

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
    public $from;
    public $to;
    public $notes;

    // Shipment Details
    public $shipment_type;
    public $loading_point_id;
    public $offloading_point_id;
    public $etd;
    public $eta;
    public $estimated_revenue;
    public $estimated_cost;
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

        $this->loadFormOptions($this->employee->company_id);

        $this->addCargoRow();
        $this->addPartyRow();
    }

    public function addCargoRow()
    {
        $this->cargo_rows[] = $this->blankCargoRow();
    }

    public function removeCargoRow($index)
    {
        unset($this->cargo_rows[$index]);
        $this->cargo_rows = array_values($this->cargo_rows);
    }

    public function addPartyRow()
    {
        $this->party_rows[] = $this->blankPartyRow();
    }

    public function removePartyRow($index)
    {
        unset($this->party_rows[$index]);
        $this->party_rows = array_values($this->party_rows);
    }

    public function refreshPartyOptions($index)
    {
        $this->reloadPartyType($this->party_rows[$index]['party_type'] ?? null);
    }

    public function isConsolidation(): bool
    {
        return $this->shipment_type === 'Consolidation';
    }

    protected function rules()
    {
        return [
            // A Consolidation (master container) job is never invoiced - customer optional.
            'customer_id' => [$this->isConsolidation() ? 'nullable' : 'required', 'integer', 'exists:customers,id'],
            'freight_service_type_id' => 'nullable|integer|exists:freight_service_types,id',
            'primary_transport_mode' => 'required|string',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'estimated_revenue' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'cargo_rows.*.commodity' => 'nullable|string',
            'party_rows.*.party_id' => 'required_with:party_rows.*.party_type',
            'party_rows.*.role' => 'nullable|string|required_with:party_rows.*.party_id',
        ];
    }

    protected $validationAttributes = [
        'party_rows.*.party_id' => 'party',
        'party_rows.*.role' => 'role',
    ];

    public function store(FreightJobService $service)
    {
        $this->validate();

        $jobData = [
            'user_id' => Auth::id(),
            'customer_id' => $this->customer_id ?: null,
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
            'from' => $this->from,
            'to' => $this->to,
            'notes' => $this->notes,
        ] + $this->estimateFields();

        $shipmentData = [
            'user_id' => Auth::id(),
            'mode' => $this->primary_transport_mode,
            'shipment_type' => $this->shipment_type,
            'loading_point_id' => $this->loading_point_id,
            'offloading_point_id' => $this->offloading_point_id,
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

        $job = DB::transaction(function () use ($service, $jobData, $shipmentData, $cargoRows, $partyRows) {
            $job = $service->create($jobData, $shipmentData, $cargoRows, $partyRows);

            // Leg 1 (job From → To) is created now; edit it / add legs on the Transport tab.
            if ($shipment = $job->shipments()->first()) {
                app(ShipmentLegService::class)->createFirstLeg($shipment);
            }

            return $job;
        });

        app(FreightJobAuthorizationService::class)->notifyAuthorizers($job);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Freight Job ' . $job->job_number . ' created and sent for authorization.',
        ]);

        return redirect()->route('freight.jobs.show', $job->id);
    }

    /** Same rule as the Costing tab's saveEstimates: margin only when both are set. */
    private function estimateFields(): array
    {
        $revenue = $this->estimated_revenue === '' || $this->estimated_revenue === null ? null : (float) $this->estimated_revenue;
        $cost = $this->estimated_cost === '' || $this->estimated_cost === null ? null : (float) $this->estimated_cost;
        if (!\App\Models\Company::freightCostingVisibleTo()) {
            $revenue = $cost = null; // Freight Costing Managed By Finance: only Finance sets estimates
        }

        return [
            'estimated_revenue' => $revenue,
            'estimated_cost' => $cost,
            'estimated_margin' => ($revenue !== null && $cost !== null) ? $revenue - $cost : null,
        ];
    }

    public function render()
    {
        return view('livewire.freight.jobs.create', [
            'incoterms' => FreightJob::INCOTERMS,
        ]);
    }
}
