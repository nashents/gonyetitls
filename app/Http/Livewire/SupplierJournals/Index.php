<?php

namespace App\Http\Livewire\SupplierJournals;

use App\Models\Account;
use App\Models\AccountTypeGroup;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Currency;
use App\Models\SupplierJournal;
use App\Models\Vendor;
use App\Services\Accounting\SupplierJournalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Suppliers journal register: manual debit/credit adjustments to a vendor's
 * account (opening balances, discounts received, balances written back,
 * corrections), posted against Accounts Payable and a chosen contra
 * account. Debits can be applied to the vendor's open bills here, at
 * capture or later.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $vendor_filter;
    public $type_filter;
    public $status_filter = 'posted';
    public $vendors;
    public $currencies;

    // capture form
    public $vendor_id;
    public $type = SupplierJournal::DEBIT;
    public $date;
    public $currency_id;
    public $exchange_rate = 1;
    public $account_id;
    public $amount;
    public $reference;
    public $description;
    public $capture_bills = [];
    public $capture_allocations = [];

    // allocation modal
    public $journal_id;
    public $selected_journal;
    public $open_bills = [];
    public $allocations = [];
    public $selectedBill;
    public $allocate_amount;

    // void modal
    public $void_reason;

    protected $queryString = ['search', 'vendor_filter', 'type_filter', 'status_filter'];

    public function mount()
    {
        $this->vendors = Vendor::orderBy('name')->get(['id', 'name']);
        $this->currencies = Currency::orderBy('name')->get();
        $this->resetForm();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    private function baseCurrencyId()
    {
        return Auth::user()?->employee?->company?->currency_id;
    }

    public function getIsForeignCurrencyProperty(): bool
    {
        return $this->currency_id && (int) $this->currency_id !== (int) $this->baseCurrencyId();
    }

    private function resetForm(): void
    {
        $this->reset(['vendor_id', 'account_id', 'amount', 'reference', 'description', 'capture_bills', 'capture_allocations']);
        $this->type = SupplierJournal::DEBIT;
        $this->date = now()->toDateString();
        $this->currency_id = $this->baseCurrencyId();
        $this->exchange_rate = 1;
    }

    // ── Capture ─────────────────────────────────────────────────────────────

    public function showCreate()
    {
        $this->resetErrorBag();
        $this->resetForm();
        $this->dispatchBrowserEvent('show-sjCreateModal');
    }

    public function updatedVendorId()
    {
        $this->loadCaptureBills();
    }

    public function updatedCurrencyId()
    {
        $this->exchange_rate = $this->isForeignCurrency ? null : 1;
        $this->loadCaptureBills();
    }

    public function updatedType()
    {
        $this->loadCaptureBills();
    }

    private function loadCaptureBills(): void
    {
        $this->capture_allocations = [];
        $this->capture_bills = [];

        if ($this->type !== SupplierJournal::DEBIT || !$this->vendor_id || !$this->currency_id) {
            return;
        }

        $this->capture_bills = $this->openBillsQuery($this->vendor_id, $this->currency_id)
            ->orderBy('bill_date')
            ->orderBy('id')
            ->take(200)
            ->get(['id', 'bill_number', 'bill_date', 'total', 'balance'])
            ->toArray();
    }

    private function openBillsQuery($vendorId, $currencyId)
    {
        return Bill::where('vendor_id', $vendorId)
            ->where('currency_id', $currencyId)
            ->where('authorization', 'approved')
            ->whereRaw('CAST(balance AS DECIMAL(20,2)) > 0');
    }

    public function getCaptureAllocatedProperty(): float
    {
        return round(collect($this->capture_allocations)->sum(fn ($v) => is_numeric($v) ? (float) $v : 0), 2);
    }

    public function store()
    {
        $this->validate([
            'vendor_id'     => 'required|exists:vendors,id',
            'type'          => 'required|in:debit,credit',
            'date'          => 'required|date',
            'currency_id'   => 'required|exists:currencies,id',
            'exchange_rate' => $this->isForeignCurrency ? 'required|numeric|gt:0' : 'nullable|numeric|gt:0',
            'account_id'    => 'required|exists:accounts,id',
            'amount'        => 'required|numeric|min:0.01',
            'reference'     => 'nullable|string|max:255',
            'description'   => 'required|string|max:1000',
            'capture_allocations.*' => 'nullable|numeric|min:0',
        ], [], [
            'vendor_id'   => 'supplier',
            'account_id'  => 'contra account',
            'currency_id' => 'currency',
            'capture_allocations.*' => 'allocation',
        ]);

        $ap = Account::where('name', 'Accounts Payable')->value('id');
        if ((int) $this->account_id === (int) $ap) {
            $this->addError('account_id', 'The contra account cannot be Accounts Payable itself.');
            return;
        }

        $allocations = $this->type === SupplierJournal::DEBIT
            ? collect($this->capture_allocations)->filter(fn ($v) => is_numeric($v) && (float) $v > 0)->map(fn ($v) => round((float) $v, 2))->all()
            : [];

        if (array_sum($allocations) - (float) $this->amount > 0.004) {
            $this->addError('capture_allocations', 'Allocations can\'t exceed the journal amount.');
            return;
        }

        foreach ($allocations as $billId => $value) {
            $bill = collect($this->capture_bills)->firstWhere('id', (int) $billId);
            if (!$bill || $value - (float) $bill['balance'] > 0.004) {
                $this->addError('capture_allocations', 'An allocation is more than the bill\'s open balance.');
                return;
            }
        }

        try {
            $journal = app(SupplierJournalService::class)->create([
                'vendor_id'     => $this->vendor_id,
                'type'          => $this->type,
                'date'          => $this->date,
                'currency_id'   => $this->currency_id,
                'exchange_rate' => $this->isForeignCurrency ? $this->exchange_rate : 1,
                'account_id'    => $this->account_id,
                'amount'        => $this->amount,
                'reference'     => $this->reference ?: null,
                'description'   => $this->description,
            ], $allocations);
        } catch (\Throwable $e) {
            $this->addError('amount', $e->getMessage());
            return;
        }

        $this->resetForm();
        $this->dispatchBrowserEvent('hide-sjCreateModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Suppliers journal {$journal->journal_number} posted",
        ]);
    }

    // ── Allocation ──────────────────────────────────────────────────────────

    public function showAllocate($id)
    {
        $this->resetErrorBag();
        $this->journal_id = $id;
        $this->loadJournal();
        $this->selectedBill = null;
        $this->allocate_amount = $this->selected_journal ? $this->selected_journal->unallocatedAmount() : null;
        $this->dispatchBrowserEvent('show-sjAllocateModal');
    }

    public function updatedSelectedBill($id)
    {
        if ($id && $this->selected_journal) {
            $bill = Bill::find($id);
            $this->allocate_amount = round(min($this->selected_journal->unallocatedAmount(), (float) $bill?->balance), 2);
        }
    }

    public function allocate()
    {
        $this->validate([
            'selectedBill'    => 'required|exists:bills,id',
            'allocate_amount' => 'required|numeric|min:0.01',
        ]);

        $journal = SupplierJournal::findOrFail($this->journal_id);
        $bill = Bill::findOrFail($this->selectedBill);

        try {
            $allocation = app(SupplierJournalService::class)->allocate($journal, $bill, (float) $this->allocate_amount);
        } catch (\RuntimeException $e) {
            $this->addError('selectedBill', $e->getMessage());
            return;
        }

        if (!$allocation) {
            $this->addError('allocate_amount', 'Nothing to apply - the journal is fully applied or the bill has no open balance.');
            return;
        }

        $this->loadJournal();
        $this->selectedBill = null;
        $this->allocate_amount = $this->selected_journal->unallocatedAmount();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Applied {$allocation->amount} to Bill# {$bill->bill_number}",
        ]);
    }

    public function removeAllocation($allocationId)
    {
        $allocation = BillPayment::where('source', SupplierJournalService::SOURCE)
            ->where('supplier_journal_id', $this->journal_id)
            ->findOrFail($allocationId);

        app(SupplierJournalService::class)->deallocate($allocation);

        $this->loadJournal();
        $this->allocate_amount = $this->selected_journal->unallocatedAmount();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Allocation released back onto the bill balance',
        ]);
    }

    private function loadJournal(): void
    {
        $this->selected_journal = SupplierJournal::with(['vendor', 'currency', 'account', 'bill_payments.bill'])->find($this->journal_id);

        if (!$this->selected_journal) {
            $this->open_bills = [];
            $this->allocations = [];
            return;
        }

        $this->allocations = $this->selected_journal->bill_payments;

        $this->open_bills = $this->openBillsQuery($this->selected_journal->vendor_id, $this->selected_journal->currency_id)
            ->orderBy('bill_date', 'desc')
            ->take(200)
            ->get(['id', 'bill_number', 'bill_date', 'total', 'balance']);
    }

    // ── Void ────────────────────────────────────────────────────────────────

    public function showVoid($id)
    {
        $this->resetErrorBag();
        $this->journal_id = $id;
        $this->selected_journal = SupplierJournal::with(['vendor', 'currency', 'bill_payments'])->find($id);
        $this->void_reason = null;
        $this->dispatchBrowserEvent('show-sjVoidModal');
    }

    public function void()
    {
        $this->validate(['void_reason' => 'required|string|max:1000'], [], ['void_reason' => 'reason']);

        $journal = SupplierJournal::findOrFail($this->journal_id);

        try {
            app(SupplierJournalService::class)->void($journal, $this->void_reason);
        } catch (\RuntimeException $e) {
            $this->addError('void_reason', $e->getMessage());
            return;
        }

        $this->dispatchBrowserEvent('hide-sjVoidModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Suppliers journal {$journal->journal_number} voided and its journal entry reversed",
        ]);
    }

    public function render()
    {
        $allocated = '(select COALESCE(SUM(bp.amount+0),0) from bill_payments bp where bp.supplier_journal_id = supplier_journals.id and bp.deleted_at is null)';

        $journals = SupplierJournal::query()
            ->with(['vendor', 'currency', 'account', 'user'])
            ->select('supplier_journals.*')
            ->selectRaw("{$allocated} as allocated_total")
            ->when($this->vendor_filter, fn ($q) => $q->where('vendor_id', $this->vendor_filter))
            ->when($this->type_filter, fn ($q) => $q->where('type', $this->type_filter))
            ->when($this->status_filter, fn ($q) => $q->where('status', $this->status_filter))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('journal_number', 'like', $term)
                        ->orWhere('reference', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', $term));
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25);

        $apId = Account::where('name', 'Accounts Payable')->value('id');

        return view('livewire.supplier-journals.index', [
            'journals' => $journals,
            'account_groups' => AccountTypeGroup::with(['account_types.accounts' => fn ($q) => $q->where('status', 1)->where('id', '!=', $apId)->orderBy('name')])->get(),
            'isForeignCurrency' => $this->isForeignCurrency,
            'captureAllocated' => $this->captureAllocated,
        ]);
    }
}
