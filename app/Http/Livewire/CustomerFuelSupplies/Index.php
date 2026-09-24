<?php

namespace App\Http\Livewire\CustomerFuelSupplies;

use App\Models\Customer;
use App\Models\CustomerFuelSupply;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\Accounting\CustomerFuelSupplyService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Customer-supplied fuel register: every fuel order / top-up a customer
 * supplied in kind, how much of it has been applied to their invoices, and
 * manual apply/release for the ones that couldn't be matched to a trip
 * invoice automatically (e.g. a top-up with no trip, or a trip invoiced to
 * a different account).
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $customer_filter;
    public $status_filter;
    public $customers;

    public $supply_id;
    public $selected_supply;
    public $open_invoices = [];
    public $allocations = [];
    public $selectedInvoice;
    public $allocate_amount;

    protected $queryString = ['search', 'customer_filter', 'status_filter'];

    public function mount()
    {
        $this->customers = Customer::whereIn('id', CustomerFuelSupply::select('customer_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showAllocate($id)
    {
        $this->resetErrorBag();
        $this->supply_id = $id;
        $this->loadSupply();
        $this->selectedInvoice = null;
        $this->allocate_amount = $this->selected_supply ? $this->selected_supply->unallocatedAmount() : null;
        $this->dispatchBrowserEvent('show-cfsAllocateModal');
    }

    public function updatedSelectedInvoice($id)
    {
        if ($id && $this->selected_supply) {
            $invoice = Invoice::find($id);
            $this->allocate_amount = round(min($this->selected_supply->unallocatedAmount(), (float) $invoice?->balance), 2);
        }
    }

    public function allocate()
    {
        $this->validate([
            'selectedInvoice' => 'required|exists:invoices,id',
            'allocate_amount' => 'required|numeric|min:0.01',
        ]);

        $supply = CustomerFuelSupply::findOrFail($this->supply_id);
        $invoice = Invoice::findOrFail($this->selectedInvoice);

        try {
            $allocation = app(CustomerFuelSupplyService::class)->allocate($supply, $invoice, (float) $this->allocate_amount);
        } catch (\RuntimeException $e) {
            $this->addError('selectedInvoice', $e->getMessage());
            return;
        }

        if (!$allocation) {
            $this->addError('allocate_amount', 'Nothing to apply - the supply is fully applied or the invoice has no open balance.');
            return;
        }

        $this->loadSupply();
        $this->selectedInvoice = null;
        $this->allocate_amount = $this->selected_supply->unallocatedAmount();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Applied {$allocation->amount} to Invoice# {$invoice->invoice_number}",
        ]);
    }

    public function removeAllocation($allocationId)
    {
        $allocation = InvoicePayment::where('source', CustomerFuelSupplyService::SOURCE)
            ->where('customer_fuel_supply_id', $this->supply_id)
            ->findOrFail($allocationId);

        app(CustomerFuelSupplyService::class)->deallocate($allocation);

        $this->loadSupply();
        $this->allocate_amount = $this->selected_supply->unallocatedAmount();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Allocation released back onto the invoice balance',
        ]);
    }

    private function loadSupply(): void
    {
        $this->selected_supply = CustomerFuelSupply::with(['customer', 'currency', 'trip', 'invoice_payments.invoice'])->find($this->supply_id);

        if (!$this->selected_supply) {
            $this->open_invoices = [];
            $this->allocations = [];
            return;
        }

        $this->allocations = $this->selected_supply->invoice_payments;

        $this->open_invoices = Invoice::where('customer_id', $this->selected_supply->customer_id)
            ->where('currency_id', $this->selected_supply->currency_id)
            ->where('authorization', 'approved')
            ->whereRaw('CAST(balance AS DECIMAL(20,2)) > 0')
            ->orderBy('date', 'desc')
            ->take(200)
            ->get(['id', 'invoice_number', 'date', 'total', 'balance']);
    }

    public function render()
    {
        $allocated = '(select COALESCE(SUM(ip.amount+0),0) from invoice_payments ip where ip.customer_fuel_supply_id = customer_fuel_supplies.id and ip.deleted_at is null)';

        $supplies = CustomerFuelSupply::query()
            ->with(['customer', 'currency', 'trip', 'fuel', 'top_up', 'container'])
            ->select('customer_fuel_supplies.*')
            ->selectRaw("{$allocated} as allocated_total")
            ->when($this->customer_filter, fn ($q) => $q->where('customer_id', $this->customer_filter))
            ->when($this->status_filter === 'open', fn ($q) => $q->whereRaw("customer_fuel_supplies.amount - {$allocated} > 0.004"))
            ->when($this->status_filter === 'applied', fn ($q) => $q->whereRaw("customer_fuel_supplies.amount - {$allocated} <= 0.004"))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('supply_number', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term))
                        ->orWhereHas('trip', fn ($t) => $t->where('trip_number', 'like', $term));
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25);

        return view('livewire.customer-fuel-supplies.index', [
            'supplies' => $supplies,
        ]);
    }
}
