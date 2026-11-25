<?php

namespace App\Http\Livewire\Freight\Consolidations;

use App\Models\Cargo;
use App\Models\Consolidation;
use App\Models\Customer;
use App\Models\FreightServiceType;
use App\Services\Freight\ConsolidationClientService;
use Livewire\Component;

/**
 * Freight > Consolidations > {CN} > Add Clients: one grid row per client cargo
 * line; saving creates a freight job per client (LCL shipment + cargo,
 * route/dates copied from the master) and attaches them as house shipments.
 */
class AddClients extends Component
{
    public $consolidation;
    public $customers;
    public $serviceTypes;
    public $cargos;

    public $default_service_type_id;
    public $rows = [];

    public function mount($id)
    {
        $this->consolidation = Consolidation::with('master_shipment.freight_job', 'master_shipment.containers')->findOrFail($id);
        $this->loadOptions();

        $this->default_service_type_id = FreightServiceType::where('name', 'Freight Forwarding & Customs Clearing')->value('id');

        for ($i = 0; $i < 5; $i++) {
            $this->addRow();
        }
    }

    private function loadOptions(): void
    {
        $this->customers = Customer::orderBy('name', 'asc')->get(['id', 'name']);
        $this->serviceTypes = FreightServiceType::orderBy('name', 'asc')->get(['id', 'name']);
        $this->cargos = Cargo::selectable()->orderBy('name', 'asc')->get(['id', 'name']);
    }

    private function blankRow(): array
    {
        return [
            'customer_id' => null,
            'customer_reference' => null,
            'freight_service_type_id' => $this->default_service_type_id,
            'cargo_id' => null,
            'commodity' => null,
            'hs_code' => null,
            'packages' => null,
            'gross_weight' => null,
            'cbm' => null,
            'marks_and_numbers' => null,
        ];
    }

    public function addRow()
    {
        $this->rows[] = $this->blankRow();
    }

    /** New row for the same client - a second commodity in the same job. */
    public function duplicateRow($index)
    {
        $source = $this->rows[$index] ?? null;
        if (!$source) {
            return;
        }

        $copy = $this->blankRow();
        $copy['customer_id'] = $source['customer_id'];
        $copy['customer_reference'] = $source['customer_reference'];
        $copy['freight_service_type_id'] = $source['freight_service_type_id'];

        array_splice($this->rows, $index + 1, 0, [$copy]);
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        if (!$this->rows) {
            $this->addRow();
        }
    }

    public function updatedDefaultServiceTypeId($value)
    {
        // Apply to rows that haven't been started yet.
        foreach ($this->rows as $i => $row) {
            if (!$row['customer_id']) {
                $this->rows[$i]['freight_service_type_id'] = $value ?: null;
            }
        }
    }

    public function refresh($category)
    {
        $this->loadOptions();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => ucfirst($category) . ' Refreshed Successfully!!.']);
    }

    /** Rows with anything typed in them. */
    private function filledRows(): array
    {
        return collect($this->rows)->filter(fn ($r) => collect($r)->except('freight_service_type_id')->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())->all();
    }

    public function save(ConsolidationClientService $service)
    {
        $filled = $this->filledRows();

        if (!$filled) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Enter at least one client row.']);
            return;
        }

        $rules = [];
        $messages = [];
        foreach (array_keys($filled) as $i) {
            $rules["rows.$i.customer_id"] = 'required|integer|exists:customers,id';
            $rules["rows.$i.freight_service_type_id"] = 'nullable|integer|exists:freight_service_types,id';
            $rules["rows.$i.cargo_id"] = 'nullable|integer|exists:cargos,id';
            $rules["rows.$i.commodity"] = "required_without:rows.$i.cargo_id|nullable|string|max:255";
            $rules["rows.$i.packages"] = 'nullable|integer|min:0';
            $rules["rows.$i.gross_weight"] = 'nullable|numeric|min:0';
            $rules["rows.$i.cbm"] = 'required|numeric|gt:0';
            $messages["rows.$i.customer_id.required"] = 'Select the client.';
            $messages["rows.$i.commodity.required_without"] = 'Select a commodity or type a description.';
            $messages["rows.$i.cbm.required"] = 'CBM is required (used for pricing and cost split).';
            $messages["rows.$i.cbm.gt"] = 'CBM must be more than 0.';
        }

        $this->validate($rules, $messages);

        $jobs = $service->addClients($this->consolidation, array_values($filled));

        session()->flash('success', count($jobs) . ' client job(s) created and added to ' . $this->consolidation->consolidation_number
            . ': ' . collect($jobs)->pluck('job_number')->implode(', ') . '. Each was sent for authorization.');

        return redirect()->route('freight.consolidations.show', $this->consolidation->id);
    }

    public function render()
    {
        $filled = collect($this->filledRows());

        return view('livewire.freight.consolidations.add-clients', [
            'clientCount' => $filled->filter(fn ($r) => $r['customer_id'])
                ->map(fn ($r) => $r['customer_id'] . '|' . trim((string) $r['customer_reference']) . '|' . $r['freight_service_type_id'])
                ->unique()->count(),
            'totalCbm' => $filled->sum(fn ($r) => is_numeric($r['cbm']) ? (float) $r['cbm'] : 0),
            'totalWeight' => $filled->sum(fn ($r) => is_numeric($r['gross_weight']) ? (float) $r['gross_weight'] : 0),
            'totalPackages' => $filled->sum(fn ($r) => is_numeric($r['packages']) ? (int) $r['packages'] : 0),
        ]);
    }
}
