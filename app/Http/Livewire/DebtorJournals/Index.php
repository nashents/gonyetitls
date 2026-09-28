<?php

namespace App\Http\Livewire\DebtorJournals;

use App\Models\Account;
use App\Models\AccountTypeGroup;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DebtorJournal;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\Accounting\DebtorJournalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Debtors journal register: manual debit/credit adjustments to a customer's
 * account (write-offs, discounts, opening balances, corrections), posted
 * against Accounts Receivable and a chosen contra account. Credits can be
 * applied to the customer's open invoices here, at capture or later.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $customer_filter;
    public $type_filter;
    public $status_filter = 'posted';
    public $customers;
    public $currencies;

    // capture form
    public $customer_id;
    public $type = DebtorJournal::CREDIT;
    public $date;
    public $currency_id;
    public $exchange_rate = 1;
    public $account_id;
    public $amount;
    public $reference;
    public $description;
    public $capture_invoices = [];
    public $capture_allocations = [];

    // allocation modal
    public $journal_id;
    public $selected_journal;
    public $open_invoices = [];
    public $allocations = [];
    public $selectedInvoice;
    public $allocate_amount;

    // void modal
    public $void_reason;

    protected $queryString = ['search', 'customer_filter', 'type_filter', 'status_filter'];

    public function mount()
    {
        $this->customers = Customer::orderBy('name')->get(['id', 'name']);
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
        $this->reset(['customer_id', 'account_id', 'amount', 'reference', 'description', 'capture_invoices', 'capture_allocations']);
        $this->type = DebtorJournal::CREDIT;
        $this->date = now()->toDateString();
        $this->currency_id = $this->baseCurrencyId();
        $this->exchange_rate = 1;
    }

    // ── Capture ─────────────────────────────────────────────────────────────

    public function showCreate()
    {
        $this->resetErrorBag();
        $this->resetForm();
        $this->dispatchBrowserEvent('show-djCreateModal');
    }

    public function updatedCustomerId()
    {
        $this->loadCaptureInvoices();
    }

    public function updatedCurrencyId()
    {
        $this->exchange_rate = $this->isForeignCurrency ? null : 1;
        $this->loadCaptureInvoices();
    }

    public function updatedType()
    {
        $this->loadCaptureInvoices();
    }

    private function loadCaptureInvoices(): void
    {
        $this->capture_allocations = [];
        $this->capture_invoices = [];

        if ($this->type !== DebtorJournal::CREDIT || !$this->customer_id || !$this->currency_id) {
            return;
        }

        $this->capture_invoices = Invoice::where('customer_id', $this->customer_id)
            ->where('currency_id', $this->currency_id)
            ->where('authorization', 'approved')
            ->whereRaw('CAST(balance AS DECIMAL(20,2)) > 0')
            ->orderBy('date')
            ->orderBy('id')
            ->take(200)
            ->get(['id', 'invoice_number', 'date', 'total', 'balance'])
            ->toArray();
    }

    public function getCaptureAllocatedProperty(): float
    {
        return round(collect($this->capture_allocations)->sum(fn ($v) => is_numeric($v) ? (float) $v : 0), 2);
    }

    public function store()
    {
        $this->validate([
            'customer_id'   => 'required|exists:customers,id',
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
            'customer_id' => 'customer',
            'account_id'  => 'contra account',
            'currency_id' => 'currency',
            'capture_allocations.*' => 'allocation',
        ]);

        $ar = Account::where('name', 'Accounts Receivable')->value('id');
        if ((int) $this->account_id === (int) $ar) {
            $this->addError('account_id', 'The contra account cannot be Accounts Receivable itself.');
            return;
        }

        $allocations = $this->type === DebtorJournal::CREDIT
            ? collect($this->capture_allocations)->filter(fn ($v) => is_numeric($v) && (float) $v > 0)->map(fn ($v) => round((float) $v, 2))->all()
            : [];

        if (array_sum($allocations) - (float) $this->amount > 0.004) {
            $this->addError('capture_allocations', 'Allocations can\'t exceed the journal amount.');
            return;
        }

        foreach ($allocations as $invoiceId => $value) {
            $invoice = collect($this->capture_invoices)->firstWhere('id', (int) $invoiceId);
            if (!$invoice || $value - (float) $invoice['balance'] > 0.004) {
                $this->addError('capture_allocations', 'An allocation is more than the invoice\'s open balance.');
                return;
            }
        }

        try {
            $journal = app(DebtorJournalService::class)->create([
                'customer_id'   => $this->customer_id,
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
        $this->dispatchBrowserEvent('hide-djCreateModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Debtors journal {$journal->journal_number} posted",
        ]);
    }

    // ── Allocation ──────────────────────────────────────────────────────────

    public function showAllocate($id)
    {
        $this->resetErrorBag();
        $this->journal_id = $id;
        $this->loadJournal();
        $this->selectedInvoice = null;
        $this->allocate_amount = $this->selected_journal ? $this->selected_journal->unallocatedAmount() : null;
        $this->dispatchBrowserEvent('show-djAllocateModal');
    }

    public function updatedSelectedInvoice($id)
    {
        if ($id && $this->selected_journal) {
            $invoice = Invoice::find($id);
            $this->allocate_amount = round(min($this->selected_journal->unallocatedAmount(), (float) $invoice?->balance), 2);
        }
    }

    public function allocate()
    {
        $this->validate([
            'selectedInvoice' => 'required|exists:invoices,id',
            'allocate_amount' => 'required|numeric|min:0.01',
        ]);

        $journal = DebtorJournal::findOrFail($this->journal_id);
        $invoice = Invoice::findOrFail($this->selectedInvoice);

        try {
            $allocation = app(DebtorJournalService::class)->allocate($journal, $invoice, (float) $this->allocate_amount);
        } catch (\RuntimeException $e) {
            $this->addError('selectedInvoice', $e->getMessage());
            return;
        }

        if (!$allocation) {
            $this->addError('allocate_amount', 'Nothing to apply - the journal is fully applied or the invoice has no open balance.');
            return;
        }

        $this->loadJournal();
        $this->selectedInvoice = null;
        $this->allocate_amount = $this->selected_journal->unallocatedAmount();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Applied {$allocation->amount} to Invoice# {$invoice->invoice_number}",
        ]);
    }

    public function removeAllocation($allocationId)
    {
        $allocation = InvoicePayment::where('source', DebtorJournalService::SOURCE)
            ->where('debtor_journal_id', $this->journal_id)
            ->findOrFail($allocationId);

        app(DebtorJournalService::class)->deallocate($allocation);

        $this->loadJournal();
        $this->allocate_amount = $this->selected_journal->unallocatedAmount();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Allocation released back onto the invoice balance',
        ]);
    }

    private function loadJournal(): void
    {
        $this->selected_journal = DebtorJournal::with(['customer', 'currency', 'account', 'invoice_payments.invoice'])->find($this->journal_id);

        if (!$this->selected_journal) {
            $this->open_invoices = [];
            $this->allocations = [];
            return;
        }

        $this->allocations = $this->selected_journal->invoice_payments;

        $this->open_invoices = Invoice::where('customer_id', $this->selected_journal->customer_id)
            ->where('currency_id', $this->selected_journal->currency_id)
            ->where('authorization', 'approved')
            ->whereRaw('CAST(balance AS DECIMAL(20,2)) > 0')
            ->orderBy('date', 'desc')
            ->take(200)
            ->get(['id', 'invoice_number', 'date', 'total', 'balance']);
    }

    // ── Void ────────────────────────────────────────────────────────────────

    public function showVoid($id)
    {
        $this->resetErrorBag();
        $this->journal_id = $id;
        $this->selected_journal = DebtorJournal::with(['customer', 'currency', 'invoice_payments'])->find($id);
        $this->void_reason = null;
        $this->dispatchBrowserEvent('show-djVoidModal');
    }

    public function void()
    {
        $this->validate(['void_reason' => 'required|string|max:1000'], [], ['void_reason' => 'reason']);

        $journal = DebtorJournal::findOrFail($this->journal_id);

        try {
            app(DebtorJournalService::class)->void($journal, $this->void_reason);
        } catch (\RuntimeException $e) {
            $this->addError('void_reason', $e->getMessage());
            return;
        }

        $this->dispatchBrowserEvent('hide-djVoidModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Debtors journal {$journal->journal_number} voided and its journal entry reversed",
        ]);
    }

    public function render()
    {
        $allocated = '(select COALESCE(SUM(ip.amount+0),0) from invoice_payments ip where ip.debtor_journal_id = debtor_journals.id and ip.deleted_at is null)';

        $journals = DebtorJournal::query()
            ->with(['customer', 'currency', 'account', 'user'])
            ->select('debtor_journals.*')
            ->selectRaw("{$allocated} as allocated_total")
            ->when($this->customer_filter, fn ($q) => $q->where('customer_id', $this->customer_filter))
            ->when($this->type_filter, fn ($q) => $q->where('type', $this->type_filter))
            ->when($this->status_filter, fn ($q) => $q->where('status', $this->status_filter))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('journal_number', 'like', $term)
                        ->orWhere('reference', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25);

        $arId = Account::where('name', 'Accounts Receivable')->value('id');

        return view('livewire.debtor-journals.index', [
            'journals' => $journals,
            'account_groups' => AccountTypeGroup::with(['account_types.accounts' => fn ($q) => $q->where('status', 1)->where('id', '!=', $arId)->orderBy('name')])->get(),
            'isForeignCurrency' => $this->isForeignCurrency,
            'captureAllocated' => $this->captureAllocated,
        ]);
    }
}
