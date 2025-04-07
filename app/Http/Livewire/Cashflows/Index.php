<?php

namespace App\Http\Livewire\Cashflows;

use Carbon\Carbon;
use App\Models\Horse;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Payment;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Document;
use App\Models\AccountType;
use App\Models\BankAccount;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithFileUploads;

    public $payments;
    public $payment;
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
    public $account_types;
    public $account_type_id;
    public $account_type;
    public $accounts;
    public $account;
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
    public $currency_id;
    public $date;

    public function mount(){
       
        $this->selectedAccount = "All";

        if ($this->selectedAccount == "All") {
            $this->payments = Payment::orderBy('date','desc')->get();
        }else{
            $this->payments = collect();
        }
       
        $this->accounts = Account::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->expenses = Expense::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
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

    public function updatedSelectedAccount($account){
        if (!is_null($account)) {
            if ($account == "All") {
                $this->payments = Payment::orderBy('date','desc')->get();
            }else {
                $this->payments = Payment::where('account_id', $account)->orderBy('date','desc')->get();
            }
          
        }
      
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
        $this->payment->delete();
        $this->dispatchBrowserEvent('hide-paymentDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Transaction Deleted Successfully!!"
        ]);
       
    }

    public function storeIncomeTransaction(){

        $account = Account::find($this->account_id);
        $current_balance = $account->balance;

        $payment = new Payment;
        $payment->user_id = Auth::user()->id;
        $payment->account_id = $this->account_id;
        if ($this->customer == TRUE) {
            $payment->customer_id = $this->customer_id;
        }
     
        $payment->date = $this->date;
        $payment->description = $this->description;
        $payment->notes = $this->notes;
        $payment->currency_id = $account->currency->id;
        $payment->transaction_type = $this->transaction_type;
        $payment->transaction_category = $this->transaction_category;
        $payment->amount = $this->amount;
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
        if(isset($this->expires_at)){
            $document->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
            $today = now()->toDateTimeString();
            $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
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
       
        $this->dispatchBrowserEvent('hide-incomeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Income Transaction Added Successfully!!"
        ]);

        return redirect(request()->header('Referer'));
       
    }
    public function storeExpenseTransaction(){

        $account = Account::find($this->account_id);
        $current_balance = $account->balance;

        if ($current_balance > $this->amount) {
        $account->balance = $current_balance - $this->amount;
        $account->update();

        $payment = new Payment;
        $payment->user_id = Auth::user()->id;
        $payment->account_id = $this->account_id;
        if ($this->customer == TRUE) {
            $payment->customer_id = $this->customer_id;
        }
        if ($this->vendor == TRUE) {
            $payment->vendor_id = $this->vendor_id;
        }
        $payment->date = $this->date;
        $payment->expense_id = $this->expense_id;
        $payment->notes = $this->notes;
        $payment->currency_id = $account->currency->id;
        $payment->transaction_type = $this->transaction_type;
        $payment->transaction_category = $this->transaction_category;
        $payment->amount = $this->amount;
        $payment->save();     
        
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
        if(isset($this->expires_at)){
            $document->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
            $today = now()->toDateTimeString();
            $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
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
       
        $this->dispatchBrowserEvent('hide-expenseModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>" Expense Transaction Added Successfully!!"
        ]);

        return redirect(request()->header('Referer'));

    }else {
        $this->dispatchBrowserEvent('hide-transactionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
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
        // $this->payments = Payment::all();
        return view('livewire.cashflows.index',[
            'payments' => $this->payments
        ]);
    }
}
