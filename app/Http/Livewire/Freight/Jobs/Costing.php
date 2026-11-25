<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Models\ChargeType;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\FreightCharge;
use App\Models\FreightCost;
use App\Models\FreightJob;
use App\Models\FreightRateCard;
use App\Models\Tax;
use App\Models\Vendor;
use App\Services\Freight\FreightAccountingService;
use App\Services\Freight\FreightChargeService;
use App\Services\Freight\FreightCostingService;
use App\Services\Freight\FreightCostService;
use App\Services\Freight\FreightRateCardMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
    public $cost_status = 'received';

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
    public $charge_status = 'draft';

    // Rate card pricing (FreightRateCardMatcher)
    public $cost_rate_card_id;
    public $cost_rate_basis;
    public $cost_rate_card_note;
    public $charge_rate_card_id;
    public $charge_rate_basis;
    public $charge_rate_card_note;

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

    private function blockedUntilApproved(string $what): bool
    {
        if ($this->job->isApproved()) {
            return false;
        }

        $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => "This freight job must be approved before {$what} can be generated."]);
        return true;
    }

    public function generateBills(FreightAccountingService $accounting)
    {
        if ($this->blockedUntilApproved('bills')) {
            return;
        }

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
        if ($this->blockedUntilApproved('invoices')) {
            return;
        }

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
        $this->cost_rate_card_id = $cost->freight_rate_card_id;
        $this->cost_rate_basis = $cost->freight_rate_card?->rate_basis;
        $this->cost_rate_card_note = $cost->freight_rate_card ? $this->rateCardNote($cost->freight_rate_card) : null;
        $this->cost_status = $cost->verification_status ?: 'received';
        $this->dispute_reason = $cost->dispute_reason;
        $this->resetValidation();

        $this->dispatchBrowserEvent('show-costModal-' . $this->job->id);
    }

    /*
     * Rate cards: on a new line, picking the charge type / supplier / customer
     * or changing the date looks up the best rate card and fills currency,
     * qty, rate and amount. On an existing line nothing changes unless
     * "Apply rate card" is clicked. While a line is priced from a card, editing
     * qty, days or rate recalculates the amount.
     */
    public function updatedCostChargeTypeId() { $this->autoApplyRateCard('cost'); }
    public function updatedCostVendorId() { $this->autoApplyRateCard('cost'); }
    public function updatedCostDateReceived() { $this->autoApplyRateCard('cost'); }
    public function updatedCostQuantity() { $this->recalculateFromRateCard('cost'); }
    public function updatedCostChargeableDays() { $this->recalculateFromRateCard('cost'); }
    public function updatedCostRate() { $this->recalculateFromRateCard('cost'); }

    public function updatedChargeChargeTypeId() { $this->autoApplyRateCard('charge'); }
    public function updatedChargeCustomerId() { $this->autoApplyRateCard('charge'); }
    public function updatedChargeDateBilled() { $this->autoApplyRateCard('charge'); }
    public function updatedChargeQuantity() { $this->recalculateFromRateCard('charge'); }
    public function updatedChargeChargeableDays() { $this->recalculateFromRateCard('charge'); }
    public function updatedChargeRate() { $this->recalculateFromRateCard('charge'); }

    public function applyCostRateCard() { $this->applyRateCard('cost', true); }
    public function applyChargeRateCard() { $this->applyRateCard('charge', true); }

    private function autoApplyRateCard(string $line): void
    {
        if (!$this->{$line . '_id'}) {
            $this->applyRateCard($line, false);
        }
    }

    private function applyRateCard(string $line, bool $explicit): void
    {
        $matcher = app(FreightRateCardMatcher::class);
        $isCost = $line === 'cost';

        $card = $matcher->match(
            $this->job,
            $isCost ? 'buy' : 'sell',
            $this->{$line . '_charge_type_id'},
            $isCost ? $this->cost_vendor_id : ($this->charge_customer_id ?: $this->job->customer_id),
            $this->{$line . '_shipping_container_id'},
            $isCost ? $this->cost_date_received : $this->charge_date_billed
        );

        if (!$card) {
            $this->{$line . '_rate_card_id'} = null;
            $this->{$line . '_rate_basis'} = null;
            $this->{$line . '_rate_card_note'} = $this->{$line . '_charge_type_id'}
                ? 'No matching ' . ($isCost ? 'buying' : 'selling') . ' rate card - enter the amount manually.'
                : null;
            if ($explicit) {
                $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'No matching rate card for this line.']);
            }
            return;
        }

        $priced = $matcher->price($card, $this->job, $this->{$line . '_shipping_container_id'}, $this->{$line . '_chargeable_days'});

        $this->{$line . '_rate_card_id'} = $card->id;
        $this->{$line . '_rate_basis'} = $card->rate_basis;
        $this->{$line . '_currency_id'} = $priced['currency_id'] ?: $this->{$line . '_currency_id'};
        $this->{$line . '_quantity'} = $priced['quantity'];
        if ($priced['chargeable_days'] !== null) {
            $this->{$line . '_chargeable_days'} = $priced['chargeable_days'];
        }
        $this->{$line . '_rate'} = $priced['rate'];
        $this->{$line . '_amount'} = $priced['amount'];
        $this->{$line . '_rate_card_note'} = $this->rateCardNote($card);
    }

    private function recalculateFromRateCard(string $line): void
    {
        $amount = $this->calculatedAmount($line);
        if ($amount !== null) {
            $this->{$line . '_amount'} = $amount;
        }
    }

    /**
     * Amount = Rate x Qty x Chargeable Days whenever a rate is entered (blank
     * qty/days count as 1). A rate-card line that isn't priced per day ignores
     * days, matching FreightRateCardMatcher. No rate -> null, amount is typed.
     */
    private function calculatedAmount(string $line): ?float
    {
        $rate = $this->{$line . '_rate'};
        if ($rate === null || $rate === '' || !is_numeric($rate)) {
            return null;
        }

        $perDay = !$this->{$line . '_rate_card_id'} || $this->{$line . '_rate_basis'} === 'per_day';
        $days = $perDay ? ((float) $this->{$line . '_chargeable_days'} ?: 1) : 1;

        return round((float) $rate * ((float) $this->{$line . '_quantity'} ?: 1) * $days, 2);
    }

    private function rateCardNote(FreightRateCard $card): string
    {
        $card->loadMissing(['currency', 'origin', 'destination']);
        $basis = FreightRateCard::RATE_BASES[$card->rate_basis] ?? 'Flat';
        $route = ($card->origin || $card->destination)
            ? ' · ' . ($card->origin?->city ?? 'Any') . ' → ' . ($card->destination?->city ?? 'Any')
            : '';
        $markup = $card->direction === 'sell' && $card->markup_value
            ? ' + ' . ($card->markup_type === 'percentage' ? rtrim(rtrim(number_format($card->markup_value, 2), '0'), '.') . '%' : number_format($card->markup_value, 2)) . ' markup'
            : '';

        return "Rate card #{$card->id}: {$card->currency?->symbol}" . number_format($card->rate, 2) . " {$basis}{$markup}{$route}";
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
            'freight_rate_card_id' => $this->cost_rate_card_id ?: null,
            'supplier_invoice_reference' => $this->cost_supplier_invoice_reference,
            'date_received' => $this->cost_date_received ?: null,
            'currency_id' => $this->cost_currency_id ?: null,
            'quantity' => $this->cost_quantity ?: null,
            'chargeable_days' => $this->cost_chargeable_days ?: null,
            'rate' => $this->cost_rate ?: null,
            'amount' => $this->calculatedAmount('cost') ?? ($this->cost_amount ?: 0),
            'exchange_rate' => $this->cost_exchange_rate ?: 1,
            'tax_id' => $this->cost_tax_id ?: null,
            'recoverable' => (bool) $this->cost_recoverable,
            'customer_billable' => (bool) $this->cost_customer_billable,
            'notes' => $this->cost_notes,
        ];
    }

    public function saveCost(FreightCostService $service)
    {
        $existing = $this->cost_id ? FreightCost::findOrFail($this->cost_id) : null;
        $statusLocked = $existing && $existing->statusLocked();

        $this->validate([
            'cost_amount' => 'required|numeric',
            'cost_status' => [$statusLocked ? 'nullable' : 'required', Rule::in(array_keys(FreightCost::VERIFICATION_STATUSES))],
            'dispute_reason' => [Rule::requiredIf(!$statusLocked && in_array($this->cost_status, FreightCost::REASON_STATUSES, true)), 'nullable', 'string', 'max:1000'],
        ], [], ['cost_status' => 'verification status', 'dispute_reason' => 'reason']);

        DB::transaction(function () use ($service, $existing, $statusLocked) {
            $cost = $existing ? $service->update($existing, $this->costData()) : $service->create($this->costData());

            // Through transitionVerification so verified_by/at + the dispute reason are recorded.
            if (!$statusLocked && ($cost->verification_status !== $this->cost_status
                || (in_array($this->cost_status, FreightCost::REASON_STATUSES, true) && $cost->dispute_reason !== $this->dispute_reason))) {
                $service->transitionVerification($cost, $this->cost_status, $this->dispute_reason ?: null);
            }
        });

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

    /** Quick status change from the actions column. Disputed/Rejected open the edit modal for a reason. */
    public function transitionCost($id, $status, FreightCostService $service)
    {
        $cost = FreightCost::where('freight_job_id', $this->job->id)->findOrFail($id);

        if (!array_key_exists($status, FreightCost::VERIFICATION_STATUSES)) {
            return;
        }
        if ($cost->statusLocked()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This cost is already on bill ' . ($cost->bill?->bill_number ?? '') . '; its status cannot be changed.']);
            return;
        }
        if (in_array($status, FreightCost::REASON_STATUSES, true)) {
            $this->editCost($cost->id);
            $this->cost_status = $status;
            return;
        }

        $service->transitionVerification($cost, $status);
        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Cost marked as ' . FreightCost::VERIFICATION_STATUSES[$status] . '.']);
    }

    private function resetCostForm()
    {
        $this->reset([
            'cost_id', 'cost_shipment_id', 'cost_shipping_container_id', 'cost_customs_declaration_id',
            'cost_vendor_id', 'cost_charge_type_id', 'cost_supplier_invoice_reference', 'cost_date_received',
            'cost_currency_id', 'cost_quantity', 'cost_chargeable_days', 'cost_rate', 'cost_amount',
            'cost_tax_id', 'cost_recoverable', 'cost_customer_billable', 'cost_notes',
            'cost_rate_card_id', 'cost_rate_basis', 'cost_rate_card_note',
            'cost_status', 'dispute_reason',
        ]);
        $this->cost_exchange_rate = 1;
        $this->resetValidation();
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
        $this->charge_rate_card_id = $charge->freight_rate_card_id;
        $this->charge_rate_basis = $charge->freight_rate_card?->rate_basis;
        $this->charge_rate_card_note = $charge->freight_rate_card ? $this->rateCardNote($charge->freight_rate_card) : null;
        $this->charge_status = $charge->status ?: 'draft';
        $this->resetValidation();

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
            'freight_rate_card_id' => $this->charge_rate_card_id ?: null,
            'customer_invoice_reference' => $this->charge_customer_invoice_reference,
            'date_billed' => $this->charge_date_billed ?: null,
            'currency_id' => $this->charge_currency_id ?: null,
            'quantity' => $this->charge_quantity ?: null,
            'chargeable_days' => $this->charge_chargeable_days ?: null,
            'rate' => $this->charge_rate ?: null,
            'amount' => $this->calculatedAmount('charge') ?? ($this->charge_amount ?: 0),
            'exchange_rate' => $this->charge_exchange_rate ?: 1,
            'tax_id' => $this->charge_tax_id ?: null,
            'notes' => $this->charge_notes,
        ];
    }

    public function saveCharge(FreightChargeService $service)
    {
        $existing = $this->charge_id ? FreightCharge::findOrFail($this->charge_id) : null;
        $statusLocked = $existing && $existing->statusLocked();

        $this->validate([
            'charge_amount' => 'required|numeric',
            'charge_status' => [$statusLocked ? 'nullable' : 'required', Rule::in(array_keys(FreightCharge::STATUSES))],
        ], [], ['charge_status' => 'status']);

        DB::transaction(function () use ($service, $existing, $statusLocked) {
            $charge = $existing ? $service->update($existing, $this->chargeData()) : $service->create($this->chargeData());

            // Through transitionStatus so approved_by/at are recorded.
            if (!$statusLocked && $charge->status !== $this->charge_status) {
                $service->transitionStatus($charge, $this->charge_status);
            }
        });

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

    /** Quick status change from the actions column. */
    public function transitionCharge($id, $status, FreightChargeService $service)
    {
        $charge = FreightCharge::where('freight_job_id', $this->job->id)->findOrFail($id);

        if (!array_key_exists($status, FreightCharge::STATUSES)) {
            return;
        }
        if ($charge->statusLocked()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge is already on invoice ' . ($charge->invoice?->invoice_number ?? '') . '; its status cannot be changed.']);
            return;
        }

        $service->transitionStatus($charge, $status);
        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge marked as ' . FreightCharge::STATUSES[$status] . '.']);
    }

    private function resetChargeForm()
    {
        $this->reset([
            'charge_id', 'charge_shipment_id', 'charge_shipping_container_id', 'charge_customs_declaration_id',
            'charge_customer_id', 'charge_charge_type_id', 'charge_customer_invoice_reference', 'charge_date_billed',
            'charge_currency_id', 'charge_quantity', 'charge_chargeable_days', 'charge_rate', 'charge_amount',
            'charge_tax_id', 'charge_notes',
            'charge_rate_card_id', 'charge_rate_basis', 'charge_rate_card_note',
            'charge_status',
        ]);
        $this->charge_exchange_rate = 1;
        $this->resetValidation();
        // Charges are normally billed to the job's customer (and its selling rate cards).
        $this->charge_customer_id = $this->job->customer_id;
    }

    public function toggleExpandCharge($id)
    {
        $this->expanded_charge_id = $this->expanded_charge_id == $id ? null : $id;
    }

    public function refresh($category)
    {
        if ($category === 'vendors') {
            $this->vendors = Vendor::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Vendors Refreshed Successfully!!.']);
        } elseif ($category === 'charge_types') {
            $this->chargeTypes = ChargeType::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge Types Refreshed Successfully!!.']);
        } elseif ($category === 'customers') {
            $this->customers = Customer::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Customers Refreshed Successfully!!.']);
        } elseif ($category === 'currencies') {
            $this->currencies = Currency::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Currencies Refreshed Successfully!!.']);
        } elseif ($category === 'taxes') {
            $this->taxes = Tax::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Taxes Refreshed Successfully!!.']);
        }
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
