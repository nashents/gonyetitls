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
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\TransactionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
    public $accrual_balance;
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

     public function showAccrual($id){
        $payment = Payment::find($id);
        $this->payment_id = $id ; 
        $this->accrual_balance = $payment->accrual_balance;
        $this->dispatchBrowserEvent('show-accrualModal');
    }

    public function updateAccrualBalance(){
        $payment = Payment::find($this->payment_id);
        $payment->accrual_balance = $this->accrual_balance;
        $payment->update();

        $this->dispatchBrowserEvent('hide-accrualModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Accrual Balance Updated Successfully!!"
        ]);
    }

    public function recordPayment(){

        DB::transaction(function () {

        if ($this->source_destination === "Vendor" && $this->selected_account && isset($this->selected_account->balance)) {
            if ($this->selected_account->balance < $this->amount) {
                $this->dispatchBrowserEvent('hide-paymentModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => "Insufficient funds in the selected account to process this transaction."
                ]);

                return;
            }
        }

        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";

        if ($this->source_destination == "Customer") {
            $payment->customer_id = $this->selectedCustomer;
            $payment->category = "customer";
            $payment->vendor_id = Null;
        }elseif ($this->source_destination == "Vendor") {
            $payment->vendor_id = $this->selectedVendor;
            $payment->category = "vendor";
            $payment->customer_id = Null;
        }
       

        $payment->user_id = Auth::user()->id;
        $payment->currency_id = $this->selectedCurrency;
        $payment->payment_number = $this->paymentNumber();   
        $payment->notes = $this->notes;
        $payment->mode_of_payment = $this->mode_of_payment;
        $payment->transaction_type_id = $this->transaction_type_id;
        $payment->transaction_category = $this->transaction_category;
        $payment->specify_other = $this->specify_other;
        $payment->reference_code = $this->reference_code;
        $payment->account_id = $this->selectedAccount;
        $payment->amount = $this->amount;

        if(isset($this->selectedCustomer) && isset($this->selectedCurrency) &&  $this->transaction_category == "Customer Deposits"){
            if (isset($this->last_payment) && $this->last_payment->drawdown_balance > 0) {
                $payment->drawdown_balance = $this->last_payment->drawdown_balance + $this->amount;
            }else{
                $payment->drawdown_balance = $this->amount;
            }
        }
        
        if(isset($this->selectedvendor) && isset($this->selectedCurrency) &&  $this->transaction_category == "Vendor Payments"){
           
            if (isset($this->last_payment) && $this->last_payment->drawdown_balance > 0) {
                $payment->drawdown_balance = $this->last_payment->drawdown_balance + $this->amount;
            }else{
                $payment->drawdown_balance = $this->amount;
            }
        }
       
      
        $payment->exchange_amount = $this->exchange_amount;
        $payment->exchange_rate = $this->exchange_rate;
        $payment->date = $this->date;
        $payment->save();

       

        if(isset($this->pop)){
            $file = $this->pop;
            // get file with ext
            $fileNameWithExt = $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore = $filename.'_'.time().'.'.$extention;
            $file->storeAs('/documents', $fileNameToStore, 'my_files');

            $document = new Document;
            $document->payment_id = $payment->id;
            $document->category = 'payment';
            $document->title = "Proof Of Payment";
            if (isset( $fileNameToStore)) {
                $document->filename = $fileNameToStore;
            }
            if(isset($this->expires_at)){
                $document->expires_at = Carbon::create($this->expires_at)->toDateTimeString();
                $today = now()->toDateTimeString();
                $expire = Carbon::create($this->expires_at)->toDateTimeString();
                if ($today <=  $expire) {
                    $document->status = 1;
                }else{
                    $document->status = 0;
                }
            }else {
            $document->status = 1;
            }
            $document->save();
  
        }

        if ($this->denomination) {
            foreach ($this->denomination as $key => $value) {
                $denomination = new Denomination;
                $denomination->payment_id = $payment->id;
                if (isset($this->denomination[$key])) {
                    $denomination->denomination = $this->denomination[$key];
                }
              if (isset($this->denomination_qty[$key])) {
                $denomination->quantity =  $this->denomination_qty[$key];
              }
               
                $denomination->save();
            }
        }

        if (isset($this->selectedAccount)) {
            if ($this->source_destination == "Customer") {
                $account = Account::find($this->selectedAccount);
                $current_balance = $account->balance;
                $account->balance = $current_balance + $this->amount;
                $account->update();

                $receipt =  new Receipt;
                $receipt->payment_id = $payment->id;
                $receipt->company_id = $payment->company_id;
                $receipt->currency_id = $payment->currency_id;
                $receipt->receipt_number = $this->receiptNumber(); ;
                $receipt->user_id = Auth::user()->id;
                $receipt->amount = $this->amount;
                $receipt->date = $this->date;
                $receipt->save();
                
            }elseif ($this->source_destination == "Vendor") {
                $account = Account::find($this->selectedAccount);
                $current_balance = $account->balance;
                $account->balance = $current_balance - $this->amount;
                $account->update();
            }
            
        }

        
        

       
        $this->dispatchBrowserEvent('hide-paymentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Recorded Successfully!!"
        ]);
    
    });
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

    public function destroy()
    {
        DB::transaction(function () {

            $payment = Payment::with([
                'invoice',                    // if you still use single-invoice payments sometimes
                'account',                    // cash/bank account
                'invoice_payments.invoice',   // allocations
                'denominations',
                'documents',
                'receipt',
                'cash_flow',
            ])->findOrFail($this->payment_id);

            // -----------------------------
            // 1) Reverse allocations to invoices (preferred)
            // -----------------------------
            $appliedTotal = 0;

            if ($payment->invoice_payments && $payment->invoice_payments->count() > 0) {

                foreach ($payment->invoice_payments as $invoice_payment) {
                    $drawdownInvoice = $invoice_payment->invoice;
                    if (! $drawdownInvoice) continue;

                    $applied = (float) $invoice_payment->amount;   // 👈 change column name if needed
                    $appliedTotal += $applied;

                    // Reverse: invoice balance increases (they owe again)
                    $drawdownInvoice->balance = (float) $drawdownInvoice->balance + $applied;
                    $drawdownInvoice->status  = $this->computeInvoiceStatus($drawdownInvoice->balance, $drawdownInvoice->total);
                    $drawdownInvoice->save();

                    // Delete the allocation row (so history matches deletion)
                    $invoice_payment->delete();
                }

            } else {
                // -----------------------------
                // 1b) Fallback: single invoice payment reversal
                // Only do this if your system still allows Payment->invoice directly.
                // -----------------------------
                $invoice = $payment->invoice;
                if ($invoice) {
                    $invoice->balance = (float) $invoice->balance + (float) $payment->amount;
                    $invoice->status  = $this->computeInvoiceStatus($invoice->balance, $invoice->total);
                    $invoice->save();
                }
            }

            // -----------------------------
            // 2) Reverse cash/bank account movement
            // -----------------------------
            if ($payment->account) {
                $payment->account->balance = (float) $payment->account->balance - (float) $payment->amount;
                $payment->account->save();
            }

            // -----------------------------
            // 3) Reverse customer wallet (temporary holding account)
            // Wallet originally increased by payment.amount
            // Wallet may have decreased by applied allocations
            // To fully undo, we:
            //   + add back appliedTotal (undo drawdowns)
            //   - subtract payment.amount (remove deposit)
            // Net effect restores the wallet to pre-payment state.
            // -----------------------------
            if ($payment->customer_account_id) {
                $customerAccount = Account::find($payment->customer_account_id);

                if ($customerAccount) {
                    $customerAccount->balance = (float) $customerAccount->balance + (float) $appliedTotal;
                    $customerAccount->balance = (float) $customerAccount->balance - (float) $payment->amount;
                    $customerAccount->save();
                }
            }

            // -----------------------------
            // 4) Delete children
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
            // 5) Delete payment
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
            $this->transaction_type_id = TransactionType::where('name','Deposit')->first();
            $this->transaction_category = "Customer Deposits";
            if (isset($this->selectedCustomer) && isset($this->selectedCurrency)) { 
            $this->last_payment = Payment::where('customer_id',$this->selectedCustomer)->where('currency_id',$this->selectedCurrency)->where('transaction_category',  $this->transaction_category)->orderBy('created_at','desc')->first();
        }

        }elseif ($this->source_destination == "Vendor") {
            $this->transaction_type_id = TransactionType::where('name','Withdrawal')->first();
            $this->transaction_category = "Vendor Payments";
            if (isset($this->selectedVendor) && isset($this->selectedCurrency)) { 
            $this->last_payment = Payment::where('vendor_id',$this->selectedVendor)->where('currency_id',$this->selectedCurrency)->where('transaction_category',  $this->transaction_category)->orderBy('created_at','desc')->first();
        }

        }

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }

        $query = Payment::query()
        ->with(['customer:id,name', 'currency:id,name,symbol', 'transaction_type:id,name']);

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
            $query->whereBetween($this->payment_filter, [$this->from, $this->to]);
        } else {
            $start = Carbon::now()->startOfMonth()->toDateString();
            $end   = Carbon::now()->endOfMonth()->toDateString();
            $query->whereBetween($this->payment_filter, [$start, $end]);
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
