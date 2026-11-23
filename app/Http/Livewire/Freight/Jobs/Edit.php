<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Http\Livewire\Freight\Jobs\Concerns\FreightJobFormOptions;
use App\Models\CustomsDeclarationLine;
use App\Models\FreightJob;
use App\Models\Shipment;
use App\Models\ShipmentCargo;
use App\Models\ShipmentParty;
use App\Services\Freight\FreightJobAuthorizationService;
use App\Services\Freight\FreightJobService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Edits everything the Create form captures: the job's Customer & Service
 * fields plus, for each of its shipments, Shipment Details, Cargo and
 * Parties (add / edit / remove rows). Containers, legs, customs and documents
 * stay on the Show page's tabs.
 */
class Edit extends Component
{
    use FreightJobFormOptions;

    public $job;
    public $employee;

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
    public $status;
    public $notes;

    /**
     * One entry per shipment: its detail fields plus 'cargo_rows' and
     * 'party_rows'. 'id' is null for a shipment / row not saved yet.
     */
    public $shipments = [];

    /** Saved rows the user removed, deleted on save. */
    public $removed_cargo_ids = [];
    public $removed_party_ids = [];

    public const STATUSES = [
        'draft', 'confirmed', 'in_progress', 'customs_clearance', 'port_storage',
        'transport_arranged', 'delivered', 'invoiced', 'closed', 'cancelled',
    ];

    public function mount($jobId)
    {
        $this->job = FreightJob::with(['shipments.cargo_items', 'shipments.parties'])->findOrFail($jobId);
        $this->employee = Auth::user()->employee;

        $this->customer_id = $this->job->customer_id;
        $this->customer_reference = $this->job->customer_reference;
        $this->freight_service_type_id = $this->job->freight_service_type_id;
        $this->salesperson_id = $this->job->salesperson_id;
        $this->operations_officer_id = $this->job->operations_officer_id;
        $this->clearing_officer_id = $this->job->clearing_officer_id;
        $this->quotation_id = $this->job->quotation_id;
        $this->incoterm = $this->job->incoterm;
        $this->currency_id = $this->job->currency_id;
        $this->import_export_type = $this->job->import_export_type;
        $this->primary_transport_mode = $this->job->primary_transport_mode;
        $this->from = $this->job->from;
        $this->to = $this->job->to;
        $this->status = $this->job->status;
        $this->notes = $this->job->notes;

        foreach ($this->job->shipments->sortBy('id') as $shipment) {
            $this->shipments[] = $this->shipmentState($shipment);
        }
        if (empty($this->shipments)) {
            $this->shipments[] = $this->shipmentState(null);
        }

        $this->loadFormOptions($this->employee->company_id, $this->keptCargoIds(), [
            $this->job->salesperson_id, $this->job->operations_officer_id, $this->job->clearing_officer_id,
        ]);
    }

    private function shipmentState(?Shipment $shipment): array
    {
        $cargoRows = $shipment ? $shipment->cargo_items->sortBy('id')->map(fn (ShipmentCargo $c) => [
            'id' => $c->id,
            'cargo_id' => $c->cargo_id,
            'commodity' => $c->commodity,
            'hs_code' => $c->hs_code,
            'quantity' => $c->quantity,
            'uom' => $c->uom,
            'packages' => $c->packages,
            'gross_weight' => $c->gross_weight,
            'is_dangerous_goods' => (bool) $c->is_dangerous_goods,
        ])->values()->all() : [];

        $partyRows = $shipment ? $shipment->parties->sortBy('id')->map(fn (ShipmentParty $p) => [
            'id' => $p->id,
            'party_type' => $p->party_type,
            'party_id' => $p->party_id,
            'role' => $p->role,
        ])->values()->all() : [];

        return [
            'id' => $shipment?->id,
            'shipment_number' => $shipment?->shipment_number,
            'shipment_type' => $shipment?->shipment_type,
            'loading_point_id' => $shipment?->loading_point_id,
            'offloading_point_id' => $shipment?->offloading_point_id,
            'etd' => $shipment?->etd?->format('Y-m-d\TH:i'),
            'eta' => $shipment?->eta?->format('Y-m-d\TH:i'),
            'booking_reference' => $shipment?->booking_reference,
            'freight_terms' => $shipment?->freight_terms,
            'cargo_rows' => $cargoRows ?: [$this->blankCargoRow()],
            'party_rows' => $partyRows ?: [$this->blankPartyRow()],
        ];
    }

    protected function keptCargoIds(): array
    {
        return collect($this->shipments)->pluck('cargo_rows')->flatten(1)->pluck('cargo_id')->filter()->unique()->values()->all();
    }

    protected function rules()
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'freight_service_type_id' => 'nullable|integer|exists:freight_service_types,id',
            'primary_transport_mode' => 'required|string',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'status' => ['required', 'string', Rule::in($this->allowedStatuses())],
            'shipments.*.etd' => 'nullable|date',
            'shipments.*.eta' => 'nullable|date',
            'shipments.*.cargo_rows.*.commodity' => 'nullable|string',
            'shipments.*.cargo_rows.*.quantity' => 'nullable|numeric',
            'shipments.*.cargo_rows.*.packages' => 'nullable|integer',
            'shipments.*.cargo_rows.*.gross_weight' => 'nullable|numeric',
            'shipments.*.party_rows.*.party_id' => 'required_with:shipments.*.party_rows.*.party_type',
            'shipments.*.party_rows.*.role' => 'required_with:shipments.*.party_rows.*.party_id',
        ];
    }

    protected $validationAttributes = [
        'shipments.*.party_rows.*.party_id' => 'party',
        'shipments.*.party_rows.*.role' => 'role',
    ];

    /** Until the job is approved it can only be draft or cancelled. */
    private function allowedStatuses(): array
    {
        return $this->job->isApproved() ? self::STATUSES : FreightJob::PRE_APPROVAL_STATUSES;
    }

    public function addCargoRow($s)
    {
        $this->shipments[$s]['cargo_rows'][] = $this->blankCargoRow();
    }

    public function removeCargoRow($s, $i)
    {
        $id = $this->shipments[$s]['cargo_rows'][$i]['id'] ?? null;

        if ($id) {
            if ($reason = $this->cargoRemovalBlockedReason($id)) {
                $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $reason]);
                return;
            }
            $this->removed_cargo_ids[] = $id;
        }

        unset($this->shipments[$s]['cargo_rows'][$i]);
        $this->shipments[$s]['cargo_rows'] = array_values($this->shipments[$s]['cargo_rows']);
    }

    /** A cargo line packed in a container or declared to customs can't be removed. */
    private function cargoRemovalBlockedReason(int $cargoItemId): ?string
    {
        $cargo = ShipmentCargo::find($cargoItemId);
        if (! $cargo) {
            return null;
        }

        $label = $cargo->commodity ?: ($cargo->cargo?->name ?? 'This cargo line');
        if ($cargo->containers()->exists()) {
            return "{$label} is packed in a container. Remove it from the container first (Containers tab).";
        }
        if (CustomsDeclarationLine::where('shipment_cargo_id', $cargoItemId)->exists()) {
            return "{$label} is on a customs declaration line and can't be removed.";
        }

        return null;
    }

    public function addPartyRow($s)
    {
        $this->shipments[$s]['party_rows'][] = $this->blankPartyRow();
    }

    public function removePartyRow($s, $i)
    {
        if ($id = $this->shipments[$s]['party_rows'][$i]['id'] ?? null) {
            $this->removed_party_ids[] = $id;
        }

        unset($this->shipments[$s]['party_rows'][$i]);
        $this->shipments[$s]['party_rows'] = array_values($this->shipments[$s]['party_rows']);
    }

    public function refreshPartyOptions($s, $i)
    {
        $this->reloadPartyType($this->shipments[$s]['party_rows'][$i]['party_type'] ?? null);
    }

    public function update(FreightJobService $jobService)
    {
        $this->validate();

        DB::transaction(function () use ($jobService) {
            $this->job->update([
                'customer_id' => $this->customer_id,
                'customer_reference' => $this->customer_reference,
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
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            foreach ($this->shipments as $state) {
                $this->saveShipment($state, $jobService);
            }

            if ($this->removed_cargo_ids) {
                ShipmentCargo::whereIn('id', $this->removed_cargo_ids)->get()->each->delete();
            }
            if ($this->removed_party_ids) {
                ShipmentParty::whereIn('id', $this->removed_party_ids)->get()->each->delete();
            }
        });

        $resubmitted = $this->job->authorization === 'rejected';
        app(FreightJobAuthorizationService::class)->resubmit($this->job);

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight Job ' . $this->job->job_number . ' updated successfully!' . ($resubmitted ? ' It has been sent for authorization again.' : '')]);

        return redirect()->route('freight.jobs.show', $this->job->id);
    }

    private function saveShipment(array $state, FreightJobService $jobService): void
    {
        $details = [
            'shipment_type' => $state['shipment_type'] ?: null,
            'loading_point_id' => $state['loading_point_id'] ?: null,
            'offloading_point_id' => $state['offloading_point_id'] ?: null,
            'etd' => $state['etd'] ?: null,
            'eta' => $state['eta'] ?: null,
            'booking_reference' => $state['booking_reference'] ?: null,
            'freight_terms' => $state['freight_terms'] ?: null,
        ];

        $cargoRows = collect($state['cargo_rows'])->filter(fn ($row) => filled($row['commodity']) || filled($row['cargo_id']) || filled($row['id']));
        $partyRows = collect($state['party_rows'])->filter(fn ($row) => filled($row['party_type']) && filled($row['party_id']));

        // A job with no shipment yet: only create one if something was entered.
        if (! $state['id']) {
            if (collect($details)->filter()->isEmpty() && $cargoRows->isEmpty() && $partyRows->isEmpty()) {
                return;
            }
            $jobService->addShipment(
                $this->job,
                $details + ['user_id' => Auth::id(), 'mode' => $this->primary_transport_mode, 'incoterm' => $this->incoterm],
                $cargoRows->map(fn ($row) => $this->cargoAttributes($row))->values()->all(),
                $partyRows->map(fn ($row) => $this->partyAttributes($row))->values()->all()
            );
            return;
        }

        $shipment = Shipment::where('freight_job_id', $this->job->id)->findOrFail($state['id']);
        $shipment->update($details);

        foreach ($cargoRows as $row) {
            $attributes = $this->cargoAttributes($row);
            if ($row['id']) {
                ShipmentCargo::where('shipment_id', $shipment->id)->findOrFail($row['id'])->update($attributes);
            } else {
                ShipmentCargo::create($attributes + ['shipment_id' => $shipment->id]);
            }
        }

        foreach ($partyRows as $row) {
            $attributes = $this->partyAttributes($row);
            if ($row['id']) {
                ShipmentParty::where('shipment_id', $shipment->id)->findOrFail($row['id'])->update($attributes);
            } else {
                ShipmentParty::create($attributes + ['shipment_id' => $shipment->id]);
            }
        }
    }

    private function cargoAttributes(array $row): array
    {
        return [
            'cargo_id' => $row['cargo_id'] ?: null,
            'commodity' => $row['commodity'] ?: null,
            'hs_code' => $row['hs_code'] ?: null,
            'quantity' => $row['quantity'] === '' ? null : $row['quantity'],
            'uom' => $row['uom'] ?: null,
            'packages' => $row['packages'] === '' ? null : $row['packages'],
            'gross_weight' => $row['gross_weight'] === '' ? null : $row['gross_weight'],
            'is_dangerous_goods' => (bool) $row['is_dangerous_goods'],
        ];
    }

    private function partyAttributes(array $row): array
    {
        return [
            'party_type' => $row['party_type'],
            'party_id' => $row['party_id'],
            'role' => $row['role'] ?: null,
        ];
    }

    public function render()
    {
        return view('livewire.freight.jobs.edit', [
            'incoterms' => FreightJob::INCOTERMS,
            'statuses' => $this->allowedStatuses(),
        ]);
    }
}
