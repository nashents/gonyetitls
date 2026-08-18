<?php

namespace App\Http\Livewire\Payments;

use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Document;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\Denomination;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalReversalService;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\TransactionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithFileUploads;

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    private $payments;
    public $payment_id;
    public $last_payment;
    public $payment_filter;
    public $movement;
    public $invoice_products;
    public $source_destination;
    public $invoice;
    public $invoice_id;
    public $customers;
    public $vendors;
    public $currencies;
    public $selectedCurrency;
    public $receipt_number;
    public $invoice_number;
    public $account_type;
    public $selected_account;
    public $date;
    public $amount;
    public $receipt;
    public $balance;   
    public $transaction_types;
    public $transaction_type_id;
    public $transaction_category;
    public $selectedCustomerAccount;
    public $selectedVendor;
    public $selectedVendorAccount;
    public $accounts;
    public $selectedAccount;
    public $bank_accounts;
    public $bank_account_id;
    public $trips;
    public $trip;
    public $trip_id;
    public $notes;
    public $invoice_balance;
    public $pop;
    public $reference_code;
    public $invoice_currency;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $user_id;
    public $selectedCustomer;
    public $current_balance;
    public $mode_of_payment;
    public $specify_other;
    public $selected_payment;

    public $exchange_amount;
    public $exchange_rate;

    public $expires_at;
    public $title;
    public $file;


    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }
   
    


    public function receiptNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
    
        $receipt = Receipt::orderBy('id','desc')->first();
    
        if (!$receipt) {
            $receipt_number =  $initials .'R'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $receipt->id + 1;
            $receipt_number =  $initials .'R'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $receipt_number;
    
    }
    public function paymentNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
    
        $payment = Payment::orderBy('id','desc')->first();
    
        if (!$payment) {
            $payment_number =  $initials .'P'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $payment->id + 1;
            $payment_number =  $initials .'P'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $payment_number;
    
    
    }
    

    private function resetInputFields(){
        $this->selectedCustomer = '';
        $this->selectedVendor = '';
        $this->selectedCurrency = '';
        $this->name = '';
        $this->surname = '';
        $this->notes = '';
        $this->mode_of_payment = "" ;
        $this->specify_other = "" ;
        $this->selectedAccount = "" ;
        $this->reference_code = "" ;
        $this->bank_account_id = "" ;
    }

    public function mount(){
      
        $this->resetPage();
        $this->payment_filter = "created_at";
        $this->source_destination = "Customer";
        $this->movement = "all";
        $this->accounts = collect();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();

    }
   
    public function updatedSelectedCurrency($id){
        if (!is_null($id)) {
            $this->accounts = Account::where('account_type_id',1)->where('currency_id',$id)->orderBy('name','asc')->get();
        } 
    }
    public function updatedSelectedAccount($id){
        if (!is_null($id)) {
            $this->selected_account = Account::find($id);
        } 
    }

    public function accountNumber(){
       
        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $account = Account::orderBy('id', 'desc')->first();

        if (!$account) {
            $account_number =  $initials .'CA'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $account->id + 1;
            $account_number =  $initials .'CA'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $account_number;


    }

    public function recordPayment()
    {
        $storedPopPath = null;

        try {
            DB::transaction(function () use (&$storedPopPath) {

                $user = Auth::user();

                $companyId = optional(optional($user->employee)->company)->id; // null if missing

                // -----------------------------
                // 1) Lock the account (if selected) and validate funds for vendor payments
                // -----------------------------
                $account = null;

                if (!empty($this->selectedAccount)) {
                    $account = Account::where('id', $this->selectedAccount)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($this->source_destination === "Vendor") {
                        if ((float)$account->balance < (float)$this->amount) {
                            throw new \RuntimeException("Insufficient funds in the selected account to process this transaction.");
                        }
                    }
                }

                // -----------------------------
                // 2) Compute running wallet (drawdown_balance) INSIDE TX with lock
                //    In your design drawdown_balance == "current wallet balance after this payment"
                // -----------------------------
                $newWalletBalance = null;

                // Customer wallet top-up
                if (
                    $this->source_destination === "Customer"
                    && !empty($this->selectedCustomer)
                    && !empty($this->selectedCurrency)
                    && $this->transaction_category === "Customer Deposits"
                ) {
                    $lastWallet = Payment::where('customer_id', $this->selectedCustomer)
                        ->where('currency_id', $this->selectedCurrency)
                        ->where('transaction_category', 'Customer Deposits')
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();

                    $lastBal = $lastWallet ? (float)$lastWallet->drawdown_balance : 0.0;
                    $newWalletBalance = $lastBal + (float)$this->amount;
                }

                // Vendor wallet top-up (if this is how you use it)
                if (
                    $this->source_destination === "Vendor"
                    && !empty($this->selectedVendor)
                    && !empty($this->selectedCurrency)
                    && $this->transaction_category === "Vendor Payments"
                ) {
                    $lastWallet = Payment::where('vendor_id', $this->selectedVendor)
                        ->where('currency_id', $this->selectedCurrency)
                        ->where('transaction_category', 'Vendor Payments')
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();

                    $lastBal = $lastWallet ? (float)$lastWallet->drawdown_balance : 0.0;
                    $newWalletBalance = $lastBal + (float)$this->amount;
                }

                // -----------------------------
                // 3) Save Payment
                // -----------------------------
                $payment = new Payment;
                $payment->company_id = $companyId;

                if ($this->source_destination === "Customer") {
                    $payment->customer_id = $this->selectedCustomer;
                    $payment->category    = "customer";
                    $payment->vendor_id   = null;
                } elseif ($this->source_destination === "Vendor") {
                    $payment->vendor_id   = $this->selectedVendor; // ✅ fixed casing
                    $payment->category    = "vendor";
                    $payment->customer_id = null;
                }

                $payment->user_id               = $user->id;
                $payment->currency_id           = $this->selectedCurrency;
                $payment->payment_number        = $this->paymentNumber();
                $payment->notes                 = $this->notes;
                $payment->mode_of_payment       = $this->mode_of_payment;
                $payment->transaction_type_id   = $this->transaction_type_id;
                $payment->transaction_category  = $this->transaction_category;
                $payment->specify_other         = $this->specify_other;
                $payment->reference_code        = $this->reference_code;
                $payment->account_id            = $this->selectedAccount;
                $payment->amount                = $this->amount;

                if ($newWalletBalance !== null) {
                    $payment->drawdown_balance = $newWalletBalance;
                }

                $payment->exchange_amount = $this->exchange_amount;
                $payment->exchange_rate   = $this->exchange_rate;
                $payment->date            = $this->date;

                $payment->save();

                // -----------------------------
                // 4) POP upload (store file + create Document)
                //    Note: file storage isn't rolled back; we cleanup on catch.
                // -----------------------------
                if (isset($this->pop) && $this->pop) {
                    $file = $this->pop;

                    $originalName = $file->getClientOriginalName();
                    $filename     = pathinfo($originalName, PATHINFO_FILENAME);
                    $ext          = $file->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $ext;

                    $storedPopPath = $file->storeAs('documents', $fileNameToStore, 'my_files');

                    $document = new Document;
                    $document->payment_id = $payment->id;
                    $document->category   = 'payment';
                    $document->title      = 'Proof Of Payment';
                    $document->filename   = $fileNameToStore;

                    if (!empty($this->expires_at)) {
                        $expireAt = Carbon::parse($this->expires_at);
                        $document->expires_at = $expireAt->toDateTimeString();
                        $document->status     = now()->lte($expireAt) ? 1 : 0;
                    } else {
                        $document->status = 1;
                    }

                    $document->save();
                }

                // -----------------------------
                // 5) Denominations
                // -----------------------------
                if (!empty($this->denomination) && is_array($this->denomination)) {
                    foreach ($this->denomination as $key => $value) {
                        if (empty($value)) continue;

                        $denomination = new Denomination;
                        $denomination->payment_id    = $payment->id;
                        $denomination->denomination  = $value;
                        $denomination->quantity      = $this->denomination_qty[$key] ?? null;
                        $denomination->save();
                    }
                }

                // -----------------------------
                // 6) Move cash/bank account + create receipt
                // -----------------------------
                if ($account) {
                    if ($this->source_destination === "Customer") {
                        $account->increment('balance', (float)$this->amount);

                        $receipt = new Receipt;
                        $receipt->payment_id      = $payment->id;
                        $receipt->company_id      = $payment->company_id;
                        $receipt->currency_id     = $payment->currency_id;
                        $receipt->receipt_number  = $this->receiptNumber();
                        $receipt->user_id         = $user->id;
                        $receipt->amount          = $this->amount;
                        $receipt->date            = $this->date;
                        $receipt->save();

                    } elseif ($this->source_destination === "Vendor") {
                        $account->decrement('balance', (float)$this->amount);
                    }
                }
            });

            // UI actions after success
            $this->dispatchBrowserEvent('hide-paymentModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => "Payment recorded successfully!",
            ]);

        } catch (\Throwable $e) {

            // Cleanup POP if file was saved but DB transaction rolled back
            if ($storedPopPath) {
                try { Storage::disk('my_files')->delete($storedPopPath); } catch (\Throwable $ignore) {}
            }

            $this->dispatchBrowserEvent('hide-paymentModal');
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id){
        $payment = Payment::find($id);
        $this->payment_id = $id;
        $this->selected_payment = $payment;
        $this->dispatchBrowserEvent('show-paymentDeleteModal');
    }

    public function getUnpostedPaymentsCountProperty()
    {
        $service = app(\App\Services\Accounting\PaymentJournalService::class);

        return app(\App\Services\Accounting\LedgerBackfillService::class)
            ->missingPaymentsQuery()
            ->get()
            ->filter(fn ($p) => $service->canPost($p))
            ->count();
    }

    /**
     * Posts every payment with no JournalEntry yet that PaymentJournalService
     * knows how to book - the same query/logic as `php artisan
     * ledger:backfill`, just triggered from the list page instead of the CLI.
     */
    public function bulkPostToLedger()
    {
        $result = app(\App\Services\Accounting\LedgerBackfillService::class)->runPayments();
        $posted = count($result['posted']);
        $errors = count($result['errors']);

        $this->dispatchBrowserEvent('alert', [
            'type'    => $errors > 0 ? 'warning' : 'success',
            'message' => "Posted {$posted} of {$result['total']} payment(s) to the ledger."
                . ($errors > 0 ? " {$errors} failed - see logs." : ''),
        ]);
    }

    /**
     * Manually push a payment to the general ledger. Payments have no
     * approval workflow - PaymentObserver already tries to post on
     * creation, but wraps it in a try/catch that only logs failures, so a
     * payment can silently end up with no JournalEntry. Gated the same way
     * PaymentDataIntegrityService gates its repair pass: only categories
     * PaymentJournalService actually knows how to book.
     */
    public function postToLedger($id)
    {
        $payment = Payment::find($id);
        if (! $payment) {
            return;
        }

        $service = app(\App\Services\Accounting\PaymentJournalService::class);

        if (! $service->canPost($payment)) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'warning',
                'message' => 'This payment type is not one the ledger poster recognizes - needs manual review.',
            ]);
            return;
        }

        try {
            $service->post($payment);
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Payment posted to the general ledger.',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Manual payment ledger post failed: ' . $e->getMessage());
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Could not post this payment to the ledger: ' . $e->getMessage(),
            ]);
        }
    }

    public function destroy()
    {
        DB::transaction(function () {

            $payment = Payment::with([
                'invoice',                    // single invoice mode (if you still use it)
                'bill',                       // single bill mode (if you still use it)
                'account',                    // bank/cash account
                'invoice_payments.invoice',   // allocations (drawdowns)
                'bill_payments.bill',         // allocations (drawdowns)
                'denominations',
                'documents',
                'receipt',
                'cash_flow',
            ])->findOrFail($this->payment_id);

            // -----------------------------
            // 1) Reverse allocations to invoices (preferred)
            // -----------------------------
            $appliedTotal = 0.0;

            if ($payment->invoice_payments && $payment->invoice_payments->count() > 0) {

                foreach ($payment->invoice_payments as $invoice_payment) {
                    $inv = $invoice_payment->invoice;
                    if (! $inv) continue;

                    // IMPORTANT: use the allocation amount, NOT payment amount
                    $applied = (float) ($invoice_payment->amount ?? 0);
                    $appliedTotal += $applied;

                    // Lock invoice row to avoid concurrent edits
                    $lockedInvoice = \App\Models\Invoice::where('id', $inv->id)->lockForUpdate()->first();
                    if ($lockedInvoice) {
                        $lockedInvoice->balance = (float) $lockedInvoice->balance + $applied;
                        $lockedInvoice->status  = $this->computeInvoiceStatus($lockedInvoice->balance, $lockedInvoice->total);
                        $lockedInvoice->save();
                    }

                    // Remove allocation row
                    $invoice_payment->delete();
                }

            } else {
                // Fallback: single-invoice payment reversal (only if your system uses it)
                $invoice = $payment->invoice;

                if ($invoice) {
                    $lockedInvoice = \App\Models\Invoice::where('id', $invoice->id)->lockForUpdate()->first();
                    if ($lockedInvoice) {
                        $lockedInvoice->balance = (float) $lockedInvoice->balance + (float) $payment->amount;
                        $lockedInvoice->status  = $this->computeInvoiceStatus($lockedInvoice->balance, $lockedInvoice->total);
                        $lockedInvoice->save();
                    }
                }
            }

            // -----------------------------
            // 1b) Reverse allocations to bills (mirrors invoice reversal above)
            // -----------------------------
            if ($payment->bill_payments && $payment->bill_payments->count() > 0) {

                foreach ($payment->bill_payments as $bill_payment) {
                    $bl = $bill_payment->bill;
                    if (! $bl) continue;

                    // IMPORTANT: use the allocation amount, NOT payment amount
                    $applied = (float) ($bill_payment->amount ?? 0);

                    // Lock bill row to avoid concurrent edits
                    $lockedBill = \App\Models\Bill::where('id', $bl->id)->lockForUpdate()->first();
                    if ($lockedBill) {
                        $lockedBill->balance = (float) $lockedBill->balance + $applied;
                        $lockedBill->status  = $this->computeInvoiceStatus($lockedBill->balance, $lockedBill->total);
                        $lockedBill->save();
                    }

                    // Remove allocation row
                    $bill_payment->delete();
                }

            } else {
                // Fallback: single-bill payment reversal (only if your system uses it)
                $bill = $payment->bill;

                if ($bill) {
                    $lockedBill = \App\Models\Bill::where('id', $bill->id)->lockForUpdate()->first();
                    if ($lockedBill) {
                        $lockedBill->balance = (float) $lockedBill->balance + (float) $payment->amount;
                        $lockedBill->status  = $this->computeInvoiceStatus($lockedBill->balance, $lockedBill->total);
                        $lockedBill->save();
                    }
                }
            }

            // -----------------------------
            // 2) Reverse bank/cash account movement (category-aware)
            // Customer payment: bank was +amount => reverse is -amount
            // Vendor payment:    bank was -amount => reverse is +amount
            // -----------------------------
            if ($payment->account) {
                $lockedAccount = \App\Models\Account::where('id', $payment->account->id)->lockForUpdate()->first();

                if ($lockedAccount) {
                    $category = strtolower((string) $payment->category);

                    if ($category === 'customer') {
                        $lockedAccount->balance = (float) $lockedAccount->balance - (float) $payment->amount;
                    } elseif (in_array($category, ['vendor', 'bill'], true)) {
                        // Vendor/Bill payments decremented cash on creation (money out) - reverse is +amount
                        $lockedAccount->balance = (float) $lockedAccount->balance + (float) $payment->amount;
                    } else {
                        // invoice/sale/recovery/unset all incremented cash on creation (money in) - reverse is -amount
                        $lockedAccount->balance = (float) $lockedAccount->balance - (float) $payment->amount;
                    }

                    $lockedAccount->save();
                }
            }

            // -----------------------------
            // 3) Reverse CUSTOMER wallet (drawdown_balance stored on last deposit payment row)
            //
            // Wallet current balance is stored in the latest "Customer Deposits" payment row
            // for that customer+currency.
            //
            // To fully undo this payment:
            // wallet_new = wallet_current + appliedTotal - payment.amount
            // (undo wallet deductions, remove deposit)
            // -----------------------------
            if ($payment->customer_id && $payment->transaction_category === 'Customer Deposits') {

                $walletLatest = Payment::where('customer_id', $payment->customer_id)
                    ->where('currency_id', $payment->currency_id)
                    ->where('transaction_category', 'Customer Deposits')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($walletLatest) {
                    $walletCurrent = (float) ($walletLatest->drawdown_balance ?? 0);

                    $walletNew = $walletCurrent + (float)$appliedTotal - (float)$payment->amount;

                    // Guard: wallet should never go negative (unless you allow overdraft)
                    if ($walletNew < 0) {
                        $walletNew = 0;
                    }

                    if ($walletLatest->id === $payment->id) {
                        // If we are deleting the latest wallet snapshot, we must move the new balance
                        // to the previous wallet row (since this one will be deleted)
                        $prevWallet = Payment::where('customer_id', $payment->customer_id)
                            ->where('currency_id', $payment->currency_id)
                            ->where('transaction_category', 'Customer Deposits')
                            ->where('id', '<', $payment->id)
                            ->orderByDesc('id')
                            ->lockForUpdate()
                            ->first();

                        if ($prevWallet) {
                            $prevWallet->drawdown_balance = $walletNew;
                            $prevWallet->save();
                        } else {
                            // No previous wallet row -> wallet becomes 0 after deleting this record
                            // (Nothing to update)
                        }

                    } else {
                        // Update the latest wallet snapshot to reflect removal of this payment
                        $walletLatest->drawdown_balance = $walletNew;
                        $walletLatest->save();
                    }
                }
            }

            // -----------------------------
            // 4) Delete children (DB rows)
            // -----------------------------
            $payment->denominations?->each->delete();
            $payment->documents?->each->delete();

            if ($payment->receipt) {
                $payment->receipt->delete();
            }

            if ($payment->cash_flow) {
                $payment->cash_flow->delete();
            }

            // -----------------------------
            // 5) Reverse the associated journal entry, if any
            // -----------------------------
            $journalEntry = JournalEntry::where('payment_id', $payment->id)->first();
            if ($journalEntry) {
                app(JournalReversalService::class)->reverse(
                    $journalEntry,
                    "Payment {$payment->payment_number} deleted"
                );
            }

            // -----------------------------
            // 6) Delete payment
            // -----------------------------
            $payment->delete();
        });

        $this->dispatchBrowserEvent('hide-paymentDeleteModal');
        $this->resetInputFields();

        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => "Payment reversed and deleted successfully!",
        ]);
    }

    /**
     * Safer invoice status computation (tolerant to decimals).
     * Consider storing money as integer cents long-term.
     */
    protected function computeInvoiceStatus($balance, $total): string
    {
        $balance = (float) $balance;
        $total   = (float) $total;
        $eps     = 0.00001;

        if (abs($balance - $total) < $eps) return 'Unpaid';
        if ($balance > $eps && $balance < ($total - $eps)) return 'Partial';
        if ($balance <= $eps) return 'Paid';

        // balance > total (rare): treat as Unpaid or introduce "Credit"
        return 'Unpaid';
    }

    public function render()
    {

        if ($this->source_destination == "Customer") {
            $this->transaction_type_id = optional(TransactionType::where('name','Deposit')->first())->id;
            $this->transaction_category = "Customer Deposits";
            if (isset($this->selectedCustomer) && isset($this->selectedCurrency)) { 
            $this->last_payment = Payment::where('customer_id',$this->selectedCustomer)->where('currency_id',$this->selectedCurrency)->where('transaction_category',  $this->transaction_category)->orderBy('created_at','desc')->first();
        }

        }elseif ($this->source_destination == "Vendor") {
            $this->transaction_type_id = optional(TransactionType::where('name','Withdrawal')->first())->id;
            $this->transaction_category = "Vendor Payments";
            if (isset($this->selectedVendor) && isset($this->selectedCurrency)) { 
            $this->last_payment = Payment::where('vendor_id',$this->selectedVendor)->where('currency_id',$this->selectedCurrency)->where('transaction_category',  $this->transaction_category)->orderBy('created_at','desc')->first();
        }

        }

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }

        $query = Payment::query()
        ->with(['customer:id,name','vendor:id,name', 'currency:id,name,symbol', 'transaction_type:id,name', 'journalEntry']);

        switch ($this->movement) {
        case 'Debit':
            $query->whereNotNull('bill_id');
            break;
        case 'Credit':
            $query->whereNull('bill_id');
            break;
        // 'all' or anything else → no bill_id constraint
        }

       if (!empty($this->from) && !empty($this->to)) {

            $from = Carbon::parse($this->from)->startOfDay();
            $to   = Carbon::parse($this->to)->endOfDay();

            $query->whereBetween($this->payment_filter, [$from, $to]);

        } else {

            $from = now()->startOfMonth()->startOfDay();
            $to   = now()->endOfMonth()->endOfDay(); // includes all times on last day

            $query->whereBetween($this->payment_filter, [$from, $to]);
        }


        $search = trim((string) ($this->search ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('payments.payment_number', 'like', "%{$search}%")
                ->orWhere('payments.transaction_category', 'like', "%{$search}%")
                ->orWhere('payments.mode_of_payment', 'like', "%{$search}%")
                ->orWhere('payments.date', 'like', "%{$search}%")
                ->orWhereHas('transaction_type', fn ($qq) => $qq->where('name', 'like', "%{$search}%"))
                ->orWhereHas('customer', fn ($qq) => $qq->where('name', 'like', "%{$search}%"))
                ->orWhereHas('currency', fn ($qq) => $qq->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->orderBy($this->payment_filter, 'desc')->paginate(10);

        return view('livewire.payments.index', [
        'payments' => $payments,
        ]);
    }
}
