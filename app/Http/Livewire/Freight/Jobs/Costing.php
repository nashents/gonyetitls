<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Models\ChargeType;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\FreightCharge;
use App\Models\FreightCost;
use App\Models\FreightJob;
use App\Models\Tax;
use App\Models\Vendor;
use App\Services\Freight\FreightAccountingService;
use App\Services\Freight\FreightChargeService;
use App\Services\Freight\FreightCostingService;
use App\Services\Freight\FreightCostService;
use Livewire\Component;

class Costing extends Component
{
    public $job;

    public $chargeTypes;
    public $vendors;
    public $customers;
    public $currencies;
    public $taxes;

    // Estimates form
    public $estimated_revenue;
    public $estimated_cost;

    // Cost form
    public $cost_id;
    public $cost_shipment_id;
    public $cost_shipping_container_id;
    public $cost_customs_declaration_id;
    public $cost_vendor_id;
    public $cost_charge_type_id;
    public $cost_supplier_invoice_reference;
    public $cost_date_received;
    public $cost_currency_id;
    public $cost_quantity;
    public $cost_chargeable_days;
    public $cost_rate;
    public $cost_amount;
    public $cost_exchange_rate = 1;
    public $cost_tax_id;
    public $cost_recoverable = false;
    public $cost_customer_billable = false;
    public $cost_notes;

    public $dispute_reason;

    // Charge form
    public $charge_id;
    public $charge_shipment_id;
    public $charge_shipping_container_id;
    public $charge_customs_declaration_id;
    public $charge_customer_id;
    public $charge_charge_type_id;
    public $charge_customer_invoice_reference;
    public $charge_date_billed;
    public $charge_currency_id;
    public $charge_quantity;
    public $charge_chargeable_days;
    public $charge_rate;
    public $charge_amount;
    public $charge_exchange_rate = 1;
    public $charge_tax_id;
    public $charge_notes;

    public $expanded_cost_id;
    public $expanded_charge_id;

    public function mount($jobId)
    {
        $this->job = FreightJob::findOrFail($jobId);
        $this->chargeTypes = ChargeType::orderBy('name', 'asc')->get();
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->taxes = Tax::orderBy('name', 'asc')->get();
        $this->estimated_revenue = $this->job->estimated_revenue;
        $this->estimated_cost = $this->job->estimated_cost;
        $this->refreshJob();
    }

    private function refreshJob()
    {
        $this->job = FreightJob::with([
            'costs.charge_type', 'costs.vendor', 'costs.currency', 'costs.bill',
            'charges.charge_type', 'charges.customer', 'charges.currency', 'charges.invoice',
            'shipments.containers', 'shipments.customs_declarations',
        ])->findOrFail($this->job->id);
    }

    public function generateBills(FreightAccountingService $accounting)
    {
        $result = $accounting->generateBillsFromCosts($this->job);
        $this->refreshJob();

        $message = $result['bills']->count()
            ? $result['bills']->count() . ' bill(s) generated.'
            : 'No eligible cost lines to bill.';
        if (!empty($result['warnings'])) {
            $message .= ' ' . implode(' ', $result['warnings']);
        }

        $this->dispatchBrowserEvent('alert', ['type' => $result['bills']->count() ? 'success' : 'error', 'message' => $message]);
    }

    public function generateInvoices(FreightAccountingService $accounting)
    {
        $result = $accounting->generateInvoicesFromCharges($this->job);
        $this->refreshJob();

        $message = $result['invoices']->count()
            ? $result['invoices']->count() . ' invoice(s) generated.'
            : 'No eligible charge lines to invoice.';
        if (!empty($result['warnings'])) {
            $message .= ' ' . implode(' ', $result['warnings']);
        }

        $this->dispatchBrowserEvent('alert', ['type' => $result['invoices']->count() ? 'success' : 'error', 'message' => $message]);
    }

    public function saveEstimates(FreightCostingService $costing)
    {
        $costing->saveEstimates($this->job, $this->estimated_revenue ?: null, $this->estimated_cost ?: null);
        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Estimates saved.']);
    }

    protected function rules()
    {
        return [
            'cost_amount' => 'required_without:cost_id|numeric',
            'charge_amount' => 'required_without:charge_id|numeric',
        ];
    }

    public function showAddCost()
    {
        $this->resetCostForm();
        $this->dispatchBrowserEvent('show-costModal-' . $this->job->id);
    }

    public function editCost($id)
    {
        $cost = FreightCost::findOrFail($id);
        $this->cost_id = $cost->id;
        $this->cost_shipment_id = $cost->shipment_id;
        $this->cost_shipping_container_id = $cost->shipping_container_id;
        $this->cost_customs_declaration_id = $cost->customs_declaration_id;
        $this->cost_vendor_id = $cost->vendor_id;
        $this->cost_charge_type_id = $cost->charge_type_id;
        $this->cost_supplier_invoice_reference = $cost->supplier_invoice_reference;
        $this->cost_date_received = optional($cost->date_received)->format('Y-m-d');
        $this->cost_currency_id = $cost->currency_id;
        $this->cost_quantity = $cost->quantity;
        $this->cost_chargeable_days = $cost->chargeable_days;
        $this->cost_rate = $cost->rate;
        $this->cost_amount = $cost->amount;
        $this->cost_exchange_rate = $cost->exchange_rate;
        $this->cost_tax_id = $cost->tax_id;
        $this->cost_recoverable = $cost->recoverable;
        $this->cost_customer_billable = $cost->customer_billable;
        $this->cost_notes = $cost->notes;

        $this->dispatchBrowserEvent('show-costModal-' . $this->job->id);
    }

    private function costData(): array
    {
        return [
            'freight_job_id' => $this->job->id,
            'shipment_id' => $this->cost_shipment_id ?: null,
            'shipping_container_id' => $this->cost_shipping_container_id ?: null,
            'customs_declaration_id' => $this->cost_customs_declaration_id ?: null,
            'vendor_id' => $this->cost_vendor_id ?: null,
            'charge_type_id' => $this->cost_charge_type_id ?: null,
            'supplier_invoice_reference' => $this->cost_supplier_invoice_reference,
            'date_received' => $this->cost_date_received ?: null,
            'currency_id' => $this->cost_currency_id ?: null,
            'quantity' => $this->cost_quantity ?: null,
            'chargeable_days' => $this->cost_chargeable_days ?: null,
            'rate' => $this->cost_rate ?: null,
            'amount' => $this->cost_amount ?: 0,
            'exchange_rate' => $this->cost_exchange_rate ?: 1,
            'tax_id' => $this->cost_tax_id ?: null,
            'recoverable' => (bool) $this->cost_recoverable,
            'customer_billable' => (bool) $this->cost_customer_billable,
            'notes' => $this->cost_notes,
        ];
    }

    public function saveCost(FreightCostService $service)
    {
        $this->validate(['cost_amount' => 'required|numeric']);

        if ($this->cost_id) {
            $service->update(FreightCost::findOrFail($this->cost_id), $this->costData());
        } else {
            $service->create($this->costData());
        }

        $this->resetCostForm();
        $this->refreshJob();
        $this->dispatchBrowserEvent('hide-costModal-' . $this->job->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight cost saved.']);
    }

    public function deleteCost($id, FreightCostService $service)
    {
        $service->delete(FreightCost::findOrFail($id));
        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight cost removed.']);
    }

    public function transitionCost($id, $status, FreightCostService $service)
    {
        $service->transitionVerification(FreightCost::findOrFail($id), $status, $this->dispute_reason ?: null);
        $this->reset(['dispute_reason']);
        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Verification status updated.']);
    }

    private function resetCostForm()
    {
        $this->reset([
            'cost_id', 'cost_shipment_id', 'cost_shipping_container_id', 'cost_customs_declaration_id',
            'cost_vendor_id', 'cost_charge_type_id', 'cost_supplier_invoice_reference', 'cost_date_received',
            'cost_currency_id', 'cost_quantity', 'cost_chargeable_days', 'cost_rate', 'cost_amount',
            'cost_tax_id', 'cost_recoverable', 'cost_customer_billable', 'cost_notes',
        ]);
        $this->cost_exchange_rate = 1;
    }

    public function toggleExpandCost($id)
    {
        $this->expanded_cost_id = $this->expanded_cost_id == $id ? null : $id;
    }

    public function showAddCharge()
    {
        $this->resetChargeForm();
        $this->dispatchBrowserEvent('show-chargeModal-' . $this->job->id);
    }

    public function editCharge($id)
    {
        $charge = FreightCharge::findOrFail($id);
        $this->charge_id = $charge->id;
        $this->charge_shipment_id = $charge->shipment_id;
        $this->charge_shipping_container_id = $charge->shipping_container_id;
        $this->charge_customs_declaration_id = $charge->customs_declaration_id;
        $this->charge_customer_id = $charge->customer_id;
        $this->charge_charge_type_id = $charge->charge_type_id;
        $this->charge_customer_invoice_reference = $charge->customer_invoice_reference;
        $this->charge_date_billed = optional($charge->date_billed)->format('Y-m-d');
        $this->charge_currency_id = $charge->currency_id;
        $this->charge_quantity = $charge->quantity;
        $this->charge_chargeable_days = $charge->chargeable_days;
        $this->charge_rate = $charge->rate;
        $this->charge_amount = $charge->amount;
        $this->charge_exchange_rate = $charge->exchange_rate;
        $this->charge_tax_id = $charge->tax_id;
        $this->charge_notes = $charge->notes;

        $this->dispatchBrowserEvent('show-chargeModal-' . $this->job->id);
    }

    private function chargeData(): array
    {
        return [
            'freight_job_id' => $this->job->id,
            'shipment_id' => $this->charge_shipment_id ?: null,
            'shipping_container_id' => $this->charge_shipping_container_id ?: null,
            'customs_declaration_id' => $this->charge_customs_declaration_id ?: null,
            'customer_id' => $this->charge_customer_id ?: null,
            'charge_type_id' => $this->charge_charge_type_id ?: null,
            'customer_invoice_reference' => $this->charge_customer_invoice_reference,
            'date_billed' => $this->charge_date_billed ?: null,
            'currency_id' => $this->charge_currency_id ?: null,
            'quantity' => $this->charge_quantity ?: null,
            'chargeable_days' => $this->charge_chargeable_days ?: null,
            'rate' => $this->charge_rate ?: null,
            'amount' => $this->charge_amount ?: 0,
            'exchange_rate' => $this->charge_exchange_rate ?: 1,
            'tax_id' => $this->charge_tax_id ?: null,
            'notes' => $this->charge_notes,
        ];
    }

    public function saveCharge(FreightChargeService $service)
    {
        $this->validate(['charge_amount' => 'required|numeric']);

        if ($this->charge_id) {
            $service->update(FreightCharge::findOrFail($this->charge_id), $this->chargeData());
        } else {
            $service->create($this->chargeData());
        }

        $this->resetChargeForm();
        $this->refreshJob();
        $this->dispatchBrowserEvent('hide-chargeModal-' . $this->job->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight charge saved.']);
    }

    public function deleteCharge($id, FreightChargeService $service)
    {
        $service->delete(FreightCharge::findOrFail($id));
        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight charge removed.']);
    }

    public function transitionCharge($id, $status, FreightChargeService $service)
    {
        $service->transitionStatus(FreightCharge::findOrFail($id), $status);
        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge status updated.']);
    }

    private function resetChargeForm()
    {
        $this->reset([
            'charge_id', 'charge_shipment_id', 'charge_shipping_container_id', 'charge_customs_declaration_id',
            'charge_customer_id', 'charge_charge_type_id', 'charge_customer_invoice_reference', 'charge_date_billed',
            'charge_currency_id', 'charge_quantity', 'charge_chargeable_days', 'charge_rate', 'charge_amount',
            'charge_tax_id', 'charge_notes',
        ]);
        $this->charge_exchange_rate = 1;
    }

    public function toggleExpandCharge($id)
    {
        $this->expanded_charge_id = $this->expanded_charge_id == $id ? null : $id;
    }

    public function render(FreightCostingService $costing)
    {
        return view('livewire.freight.jobs.costing', [
            'verificationStatuses' => FreightCost::VERIFICATION_STATUSES,
            'chargeStatuses' => FreightCharge::STATUSES,
            'accruedCost' => $costing->accruedCost($this->job),
            'actualMarginPercent' => $costing->marginPercent($this->job->actual_revenue, $this->job->actual_margin),
            'estimatedMarginPercent' => $costing->marginPercent($this->job->estimated_revenue, $this->job->estimated_margin),
        ]);
    }
}
