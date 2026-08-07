<?php

namespace App\Http\Livewire\Transactions;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\AccountTypeGroup;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Horse;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\Trailer;
use App\Models\TransactionType;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $payments;
    public $payment;
    public $last_payment;
    public $horses;
    public $horse_id;
    public $selectedType;
    public $selectedCategory;
    public $selectedFrom;
    public $selectedTo;
    public $comments;
    public $description;
    public $notes;
    public $transaction_category;
    public $transaction_type;
    public $bank_accounts;
    public $bank_account_id;
    public $transaction_account_types;
    public $transaction_types;
    public $transaction_type_id;
    public $account_types;
    public $account_type_id;
    public $account_type;
    public $accounts;
    public $account;
    public $selectedCurrency;
    public $exchange_amount;
    public $account_id;
    public $customer = null;
    public $vendor = null;
    public $selectedAccount;
    public $vendors;
    public $vendor_id;
    public $expenses;
    public $expense_id;
    public $customers;
    public $customer_id;
   
    public $receipt;
    public $invoice;
   
    public $amount;
   
    public $currencies;
    public $date;
    public $from;
    public $to;
    public $category;
    public $type;
    public $payment_id;
    public $receiptUpload;

  
    public string $journal_number = '';
    public string $reference = '';
  
    public string $status = 'posted';

    public ?int $currency_id = null;
    public float $exchange_rate = 1;

    public function getIsForeignCurrencyProperty(): bool
    {
        if (!$this->currency_id) return false;
        return $this->currency_id !== Auth::user()->employee->company->currency_id;
    }

    // Reset exchange rate when currency changes
    public function updatedCurrencyId(): void
    {
        $this->exchange_rate = $this->isForeignCurrency ? 0 : 1;
    }

    // Lines
    public array $lines = [];

    protected function blankLine(): array
    {
        return [
            'account_id'      => '',
            'debit'           => 0,
            'credit'          => 0,
            'description'     => '',
        ];
    }

    public function updatedLines($value, $key): void
    {
        // $key is e.g. "0.debit" or "1.credit"
        [$index, $field] = explode('.', $key);

        if ($field === 'debit' && (float) $value > 0) {
            $this->lines[$index]['credit'] = 0;
        }

        if ($field === 'credit' && (float) $value > 0) {
            $this->lines[$index]['debit'] = 0;
        }
    }

    // Computed totals
    public function getTotalDebitProperty(): float
    {
        return collect($this->lines)->sum(fn($l) => (float) $l['debit']);
    }

    public function getTotalCreditProperty(): float
    {
        return collect($this->lines)->sum(fn($l) => (float) $l['credit']);
    }

    public function addLine(): void
    {
        $this->lines[] = $this->blankLine();
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 2) return;
        array_splice($this->lines, $index, 1);
    }

    public function storeJournalEntry(): void
    {
        $this->validate([
            'date'                   => 'required|date',
            'currency_id'            => 'required|integer|exists:currencies,id',
            'exchange_rate'          => ['required', 'numeric', 'min:0', $this->isForeignCurrency ? 'gt:0' : ''],
            'status'                 => 'required|in:draft,posted',
            'lines'                  => 'required|array|min:2',
            'lines.*.account_id'     => 'required|integer|exists:accounts,id',
            'lines.*.debit'          => 'required|numeric|min:0',
            'lines.*.credit'         => 'required|numeric|min:0',
        ]);

        if (abs($this->totalDebit - $this->totalCredit) >= 0.01) {
            $this->addError('lines', 'Journal must be balanced before posting.');
            return;
        }

        DB::transaction(function () {

            $companyId = Auth::user()->employee->company_id;

            $entry = JournalEntry::create([
                'company_id'     => $companyId,
                'journal_number' => $this->journal_number,
                'date'           => $this->date,
                'reference'      => $this->reference ?: null,
                'description'    => $this->description ?: null,
                'is_manual'      => true,
                'status'         => $this->status,
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => $this->status === 'posted' ? Auth::id() : null,
                'posted_at'      => $this->status === 'posted' ? now() : null,
            ]);

            foreach ($this->lines as $line) {

                $debit  = (float) $line['debit'];
                $credit = (float) $line['credit'];
                $rate = (float) $this->exchange_rate;

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'currency_id'      => $this->currency_id,
                    'exchange_rate'    => $this->exchange_rate,
                    'debit'            => $debit,
                    'credit'           => $credit,
                    'exchange_debit'   => $debit * $this->exchange_rate,
                    'exchange_credit'  => $credit * $this->exchange_rate,
                    'description'      => $line['description'] ?: null,
                    'branch_id'        => null,
                    'customer_id'      => null,
                    'vendor_id'        => null,
                    'employee_id'      => null,
                    'driver_id'        => null,
                    'horse_id'         => null,
                    'vehicle_id'       => null,
                    'trailer_id'       => null,
                    'transporter_id'   => null,
                ]);
                        
            }
        });

        $this->reset(['reference', 'description', 'lines']);

        $this->journal_number = $this->generateJournalNumber();

        $this->lines = [$this->blankLine(), $this->blankLine()];

        $this->dispatchBrowserEvent('hide-journalModal');

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Journal entry saved successfully."
        ]);
       
    }

    protected function generateJournalNumber(): string
    {
        $last = JournalEntry::whereNotNull('journal_number')
            ->orderByDesc('id')
            ->value('journal_number');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function getDimensionOptionsProperty(): array
    {
        return [
            'branch_id'      => Branch::select('id', 'name')->get(),
            'customer_id'    => Customer::select('id', 'name')->get(),
            'vendor_id'      => Vendor::select('id', 'name')->get(),
            'employee_id'    => Employee::select('id', 'name')->get(),
            'driver_id'      => Driver::select('id')->get(),
            'horse_id'       => Horse::select('id', 'registration_number')->get(),
            'vehicle_id'     => Vehicle::select('id', 'registration_number')->get(),
            'trailer_id'     => Trailer::select('id', 'registration_number')->get(),
            'transporter_id' => Transporter::select('id', 'name')->get(),
        ];
    }

    public function mount(){

      $this->date          = now()->toDateString();
        $this->journal_number = $this->generateJournalNumber();
        $this->lines         = [
            $this->blankLine(),
            $this->blankLine(),
        ];
       
        $this->selectedAccount = "All";

        if ($this->selectedAccount == "All") {
            $this->payments = Payment::orderBy('created_at','desc')->get();
        }else{
            $this->payments = collect();
        }
        

        $account_type_names = ['Cash & Bank', 'Business Owner Contribution & Drawing', 'Other Short-Term Asset', 'Due to You & Other Business Owners', 'Other Short-Term Liability']; 
        $this->transaction_account_types = AccountType::whereIn('name', $account_type_names)->get();
        $this->accounts = Account::where('account_type_id',1)->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->expenses = Expense::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->transaction_types = TransactionType::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::orderBy('name','asc')->get();
        $this->account_types =  AccountType::orderBy('created_at','asc')->get();
        $this->horses = Horse::latest()->get();
       
        $this->from = null;
        $this->to = null;
        $this->category = null;
        $this->type = null;
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'invoice' => 'required|file',
    ];

    private function resetInputFields(){
        $this->invoice = '';
    }
    public function calculateTotalPayments($id){
        $payments = Payment::where('account_id',$id)->whereYear('created_at',date('Y'))->whereRaw('amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('amount');
       
        return  number_format($payments,2);
    }

    public function calculateTotalPaymentsInDefault(){
        $all_payments = Payment::where('currency_id',Auth::user()->employee->company->currency_id)->whereYear('created_at',date('Y'))->whereRaw('amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('amount');
        $all_payments_exchange = Payment::where('currency_id','!=',Auth::user()->employee->company->currency_id)->whereYear('created_at',date('Y'))->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');
        $total_payment = $all_payments + $all_payments_exchange;
        return  number_format($total_payment ? $total_payment : 0,2);
    }

    public function updatedSelectedAccount($id){
        if (!is_null($id)) {
            if ($id == "All") {
                $this->payments = Payment::orderBy('created_at','desc')->get();
            }else {
                $this->payments = Payment::where('account_id', $id)->orderBy('date','desc')->get();
            }

          
          
        }
      
    }
    public function updatedAccountId($id){
        if (!is_null($id)) {
            $account = Account::find($id);
            $this->selectedCurrency = $account->currency_id;
          
        }
      
    }

    public function paymentNumber(){

        $initials = "";
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

   


    public function updatedSelectedType($type){
        if (!is_null($type)) {
            if (isset($this->selectedCategory)) {
                $this->payments = Payment::where('type', $type)
                                           ->where('category', $this->selectedCategory)->get();
            }else {
                $this->payments = Payment::where('type', $type)->get();
            }
          
        }
      
    }

    public function delete($id){
        $this->payment = Payment::find($id);
        $this->dispatchBrowserEvent('show-paymentDeleteModal');
    }
    public function deleteTransaction(){
        DB::transaction(function () {
            $payment = Payment::lockForUpdate()->find($this->payment->id);

            if ($payment && $payment->account_id) {
                $account = Account::lockForUpdate()->find($payment->account_id);

                if ($account && is_numeric($account->balance) && is_numeric($payment->amount)) {
                    if ($payment->movement === 'Crt') {
                        $account->balance = (float) $account->balance - (float) $payment->amount;
                        $account->save();
                    } elseif ($payment->movement === 'Dbt') {
                        $account->balance = (float) $account->balance + (float) $payment->amount;
                        $account->save();
                    }
                }
            }

            $payment?->delete();
        });

        $this->dispatchBrowserEvent('hide-paymentDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Transaction Deleted Successfully!!"
        ]);

    }

    public function showDepositModal(){
        $transaction_type = TransactionType::where('name','Deposit')->first();
        $this->transaction_type_id = $transaction_type->id;
        $this->transaction_category = Account::where('name','Uncategorized Income')->first()->name;
        $this->dispatchBrowserEvent('show-depositModal');
    }
   

    public function storeDepositTransaction(){

        $account = Account::find($this->account_id);
        $current_balance = $account->balance;

        $payment = new Payment;
        $payment->user_id = Auth::user()->id;
        $payment->payment_number = $this->paymentNumber();
        $payment->account_id = $this->account_id;
        if ($this->customer == TRUE) {
            $payment->customer_id = $this->customer_id;
        }
        $payment->date = $this->date;
        $payment->transaction_type_id = $this->transaction_type_id;
        $payment->movement = "Crt";
        $payment->description = $this->description;
       
        $payment->notes = $this->notes;
        $payment->currency_id = $account->currency->id;

        $new_transaction_category = str_replace("\n", "", $this->transaction_category);
        $payment->transaction_category = $new_transaction_category;
        
        if(isset($this->customer_id) && isset($this->selectedCurrency) &&   $new_transaction_category == "Customer Deposits"){
            if (isset($this->last_payment) && $this->last_payment->drawdown_balance > 0) {
                $payment->drawdown_balance = $this->last_payment->drawdown_balance + $this->amount;
            }else{
                $payment->drawdown_balance = $this->amount;
            }
        }
       
        $payment->amount = $this->amount;
        $payment->exchange_rate = $this->exchange_rate;
        $payment->exchange_amount = $this->exchange_amount;
        $payment->save();     

       
        $account->balance = $current_balance + $this->amount;
        $account->update();
        
        if(isset($this->receipt)){
            $file = $this->receipt;
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
        $document->cash_flow_id = $payment->id;
        $document->category = 'cash_flow';
        $document->title = "Receipt";
        if (isset( $fileNameToStore)) {
             $document->filename = $fileNameToStore;
        }
        $document->status = 1;
        $document->save();
  

        }
       
        $this->dispatchBrowserEvent('hide-depositModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Deposit Transaction Added Successfully!!"
        ]);

        return redirect(request()->header('Referer'));
       
    }

    public function showWithdrawalModal(){
        $transaction_type = TransactionType::where('name','Withdrawal')->first();
        $this->transaction_type_id = $transaction_type->id;
        $this->transaction_category = Account::where('name','Uncategorized Expense')->first()->name;
        $this->dispatchBrowserEvent('show-withdrawalModal');
    }

    public function storeWithdrawalTransaction(){

        $account = Account::find($this->account_id);
        $current_balance = $account->balance;

        if ($current_balance > $this->amount) {
        

        $payment = new Payment;
        $payment->user_id = Auth::user()->id;
        $payment->payment_number = $this->paymentNumber();
        $payment->account_id = $this->account_id;
        if ($this->customer == TRUE) {
            $payment->customer_id = $this->customer_id;
        }
        if ($this->vendor == TRUE) {
            $payment->vendor_id = $this->vendor_id;
        }
        $payment->date = $this->date;
        $payment->transaction_type_id = $this->transaction_type_id;
        $payment->movement = "Dbt";
        $payment->description = $this->description;
        $payment->transaction_category = $this->transaction_category;
        $payment->notes = $this->notes;
        $payment->currency_id = $account->currency->id;
        $payment->amount = $this->amount;
        $payment->exchange_rate = $this->exchange_rate;
        $payment->exchange_amount = $this->exchange_amount;
        $payment->save();     
        

        $account->balance = $current_balance - $this->amount;
        $account->update();

        if(isset($this->receipt)){
            $file = $this->receipt;
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
        $document->title = "Receipt";
        if (isset( $fileNameToStore)) {
             $document->filename = $fileNameToStore;
        }
        $document->status = 1;
        $document->save();
  

        }
       
        $this->dispatchBrowserEvent('hide-withdrawalModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>" Withdrawal Transaction Added Successfully!!"
        ]);

        return redirect(request()->header('Referer'));

    }else {
        $this->dispatchBrowserEvent('hide-transactionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Transaction failed, amount to withdraw exceeds account floating balance!!"
        ]);
        return redirect(request()->header('Referer'));
    }
    }

    public function search(){
        if (isset($this->selectedCategory) && $this->selectedType == NULL) {
            $this->payments = Payment::whereBetween('date',[$this->from, $this->to] )
            ->where('category', $this->selectedCategory)->latest()->get();
        }elseif (isset($this->selectedType) && $this->selectedCategory == NULL) {
            $this->payments = Payment::whereBetween('date',[$this->from, $this->to] )
            ->where('type', $this->selectedType)->latest()->get();
        }elseif (isset($this->selectedCategory) && isset($this->selectedType)) {
            $this->payments = Payment::whereBetween('date',[$this->from, $this->to] )
            ->where('type', $this->selectedType)
            ->where('category', $this->selectedCategory)->latest()->get();
        }else{
            $this->payments = Payment::whereBetween('date',[$this->from, $this->to] )->latest()->get();
        }
       
    }
    public function updatedSelectedCategory($category){
        if (!is_null($category)) {
            if (isset($this->selectedType)) {
                $this->payments = Payment::where('category', $category)
                                           ->where('type', $this->selectedType)->get();
            }else {
                $this->payments = Payment::where('category', $category)->get();
            }
        }
      
    }

    public function update($id){
        $payment = Payment::find($id);
        $this->payment_id = $payment->id;
        $this->dispatchBrowserEvent('show-receiptUploadModal');

        }
        public function uploadInvoice(){
            $this->receiptUpload = 1;
            if($this->invoice){
                $file = $this->invoice;
                // get file with ext
                $fileNameWithExt = $file->getClientOriginalName();
                //get filename
                $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                //get extention
                $extention = $file->getClientOriginalExtension();
                //file name to store
                $fileNameToStore = $filename.'_'.time().'.'.$extention;
                $file->storeAs('/documents', $fileNameToStore, 'my_files');

            }
        $payment = Payment::find( $this->payment_id);
        $payment->user_id = Auth::user()->id;
        if (isset($fileNameToStore)) {
            $payment->invoice = $fileNameToStore;
        }
        $payment->update();
        $this->dispatchBrowserEvent('hide-receiptUploadModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Invoice Uploaded Successfully!!"
        ]);
        }

    public function render()
    {
        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }

        if (isset($this->customer_id) && isset($this->selectedCurrency)) { 
            $this->last_payment = Payment::where('customer_id',$this->customer_id)->where('currency_id',$this->selectedCurrency)->where('transaction_category', "Customer Deposits")->orderBy('created_at','desc')->first();
           
        }

      
   
        return view('livewire.transactions.index',[
            'journal_transaction_account_types' => AccountTypeGroup::with('account_types.accounts')->get(),
            'currencies'                => Currency::all(),
            'dimensionOptions'          => $this->dimensionOptions,
            'totalDebit'                => $this->totalDebit,
            'totalCredit'               => $this->totalCredit,
            'isForeignCurrency'         => $this->isForeignCurrency,
        ]);
    }


}
