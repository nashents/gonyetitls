<?php

namespace App\Http\Livewire\Accounts;

use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Payment;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Document;
use App\Models\AccountType;
use App\Models\BankAccount;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\TransactionType;
use App\Models\AccountTypeGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    use WithFileUploads;

    public $grouped_account_types;
    public $account_types;
    public $account_type_id;
    public $name;
    public $amount;
    public $exchange_rate;
    public $exchange_amount;
    public $notes;
    public $receipt;
    public $date;
    public $description;
    public $transaction_account_types;
    public $transaction_types;
    public $transaction_type_id;
    public $transaction_category;
    public $customer = null;
    public $vendor = null;
    private $accounts;
    public $account_id;
    public $account_reference;
    public $currencies;
    public $selectedCurrency;
    public $bank_accounts;
    public $bank_account_id;
    public $customers;
    public $customer_id;
    public $vendors;
    public $vebdor_id;
    public $categories;
    public $category_id;
    public $active_group;
    public $last_payment;

    public function mount(){
        $this->resetPage();
        $this->active_group = AccountTypeGroup::orderBy('name','asc')->first()->id;
        $account_type_names = ['Cash & Bank', 'Business Owner Contribution & Drawing', 'Other Short-Term Asset', 'Due to You & Other Business Owners', 'Other Short-Term Liability']; 
        $this->transaction_account_types = AccountType::whereIn('name', $account_type_names)->get();
        $this->transaction_types = TransactionType::orderBy('name','asc')->get();
        $this->accounts = Account::orderBy('name','asc')->get();
        $unset_accounts = Account::where('account_type_group_id',Null)->orWhere('account_type_group_id',"")->get();
        if (isset($unset_accounts)) {
            foreach($unset_accounts as $account){
                $account_type_group = $account->account_type->account_type_group;
                if (isset($account_type_group)) {
                   $account->account_type_group_id = $account_type_group->id;
                   $account->update();
                }
            }
        }
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->bank_accounts = collect();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->account_types =  AccountType::orderBy('name','asc')->get();
        // $this->grouped_account_types = $this->account_types->groupBy('group');
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:accounts,name,NULL,id,deleted_at,NULL|string|min:2',
        'account_reference' => 'required|unique:accounts,account_reference,NULL,id,deleted_at,NULL|min:2',
    ];

    public function updatedSelectedCurrency($id){
        $this->bank_accounts = BankAccount::where('currency_id',$id)->orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->account_type_id = '';
        $this->name = '';
        $this->selectedCurrency = '';
        $this->description = '';
        $this->bank_account_id = '';
        $this->account_reference = '';
        $this->customer_id = '';
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
            $account_number =  $initials .'A'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $account->id + 1;
            $account_number =  $initials .'A'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $account_number;


    }

    public function showAccount($id,$group_id){
        $this->account_type_id = $id;
        $this->active_group = $group_id;
        $this->dispatchBrowserEvent('show-accountModal');
    }

    public function store(){
        // try{
        $account = new Account;
        $account->user_id = Auth::user()->id;
        $account->name = $this->name;
        $account->account_reference = $this->account_reference;
        $account->account_type_id = $this->account_type_id;
        $account_type = AccountType::find($this->account_type_id);
        $account->account_type_group_id = $account_type->account_type_group_id;
        $account->bank_account_id = $this->bank_account_id ? $this->bank_account_id : null;
        $account->customer_id = $this->customer_id ? $this->customer_id : null;
        $account->currency_id = $this->selectedCurrency;
        $account->description = $this->description;
        $account->save();

        $this->dispatchBrowserEvent('hide-accountModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Account Created Successfully!!"
        ]);

        // return redirect()->route('accounts.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating account!!"
    //     ]);
    // }
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

    public function edit($id){
    $account = Account::find($id);
    $this->user_id = $account->user_id;
    $this->name = $account->name;
    $this->account_type_id = $account->account_type_id;
    $this->selectedCurrency = $account->currency_id;
    $this->bank_account_id = $account->bank_account_id;
    $this->customer_id = $account->customer_id;
    $this->description = $account->description;
    $this->account_reference = $account->account_reference;
    $this->account_id = $account->id;
    $this->dispatchBrowserEvent('show-accountEditModal');

    }

   

    public function update()
    {
        if ($this->account_id) {
            try{
            $account = Account::find($this->account_id);
            $account->user_id = Auth::user()->id;
            $account->name = $this->name;
            $account->account_reference = $this->account_reference;
            $account->account_type_id = $this->account_type_id;
            $account_type = AccountType::find($this->account_type_id);
            $account->account_type_group_id = $account_type->account_type_group_id;
            $account->bank_account_id = $this->bank_account_id ? $this->bank_account_id : null;
            $account->customer_id = $this->customer_id ? $this->customer_id : null;
            $account->currency_id = $this->selectedCurrency;
            $account->description = $this->description;
            $account->update();
            
            $this->dispatchBrowserEvent('hide-accountEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Account Updated Successfully!!"
            ]);


            // return redirect()->route('accounts.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-accountEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating account!!"
            ]);
          }
        }
    }

    public function showTransaction($id){
        $this->account = Account::find($id);
        $this->selectedCurrency = $this->account->currency_id;
        $this->account_id = $id;
        $this->dispatchBrowserEvent('show-transactionModal');

    }

    public function storeTransaction(){
        

        $transaction_type = TransactionType::find($this->transaction_type_id);
        $account = Account::find($this->account_id); 
        $current_balance = $account->balance; 

        if (isset($transaction_type)) {
            if ($transaction_type->name == "Withdrawal") {
          
                if ($current_balance >= $this->amount) {
                    
                    $payment = new Payment;
                    $payment->user_id = Auth::user()->id;
                    $payment->account_id = $this->account_id;
                    $payment->payment_number = $this->paymentNumber();
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
            
                    return redirect()->route('accounts.show',$account->id);
                }else {
    
                    $this->dispatchBrowserEvent('hide-transactionModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Transaction failed, amount to withdraw exceeds floating balance!!"
                    ]);
                }
                
            }elseif ($transaction_type->name == "Deposit") {
               
            $payment = new Payment;
            $payment->user_id = Auth::user()->id;
            $payment->account_id = $this->account_id;
            $payment->payment_number = $this->paymentNumber();
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
           
            $this->dispatchBrowserEvent('hide-depositModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Deposit Transaction Added Successfully!!"
            ]);
                return redirect()->route('accounts.show',$account->id);
            }
            
    
        }

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setActive($id){
        $this->active_group = $id;
    }

    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }

        if (isset($this->customer_id) && isset($this->selectedCurrency)) { 
            $this->last_payment = Payment::where('customer_id',$this->customer_id)->where('currency_id',$this->selectedCurrency)->where('transaction_category', "Customer Deposits")->orderBy('created_at','desc')->first();
           
        }

        $this->account_types =  AccountType::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::where('currency_id',$this->selectedCurrency)->orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        
        if (isset($this->search)) {
               
            return view('livewire.invoices.index',[
                'accounts' => Account::query()->with(['account_type:id,name','currency'])
                    ->where('name','like', '%'.$this->search.'%')
                    ->orWhereHas('account_type', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('account_type_group', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('name','asc')->paginate(10),
                    'bank_accounts' => $this->bank_accounts,
                    'customers' => $this->customers,
                    'vendors' => $this->vendors,
                    'account_types' => $this->account_types,
                   
                 
            ]);

        }else{
            return view('livewire.accounts.index',[
                'accounts' => Account::orderBy('name','asc')->paginate(10),
                'bank_accounts' => $this->bank_accounts,
                'customers' => $this->customers,
                'vendors' => $this->vendors,
                'account_types' => $this->account_types,
            ]);
        }

       
    }
}
