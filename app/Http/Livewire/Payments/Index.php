<?php

namespace App\Http\Livewire\Payments;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\Denomination;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\TransactionType;
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
    public $invoice_products;
    public $invoice;
    public $invoice_id;
    public $customers;
    public $currencies;
    public $selectedCurrency;
    public $receipt_number;
    public $invoice_number;
    public $account_type;
    public $date;
    public $amount;
    public $receipt;
    public $balance;   
    public $transaction_types;
    public $transaction_type_id;
    public $transaction_category;
    public $selectedCustomerAccount;
    public $accounts;
    public $account_id;
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
        $this->selectedCurrency = '';
        $this->name = '';
        $this->surname = '';
        $this->notes = '';
        $this->mode_of_payment = "" ;
        $this->specify_other = "" ;
        $this->account_id = "" ;
        $this->reference_code = "" ;
        $this->bank_account_id = "" ;
    }

    public function mount(){
        $this->transaction_type_id = TransactionType::where('name','Deposit')->first();
        $this->transaction_category = "Customer Deposits";
        $this->resetPage();
        $this->payment_filter = "created_at";
        $this->accounts = collect();
    }
   
    public function updatedSelectedCurrency($id){
        if (!is_null($id)) {
            $this->accounts = Account::where('account_type_id',1)->where('currency_id',$id)->orderBy('name','asc')->get();
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

    public function recordPayment(){

        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
        $payment->customer_id = $this->selectedCustomer;
        $payment->user_id = Auth::user()->id;
        $payment->currency_id = $this->selectedCurrency;
        $payment->payment_number = $this->paymentNumber();   
        $payment->notes = $this->notes;
        $payment->mode_of_payment = $this->mode_of_payment;
        $payment->transaction_type_id = $this->transaction_type_id;
        $payment->transaction_category = $this->transaction_category;
        $payment->specify_other = $this->specify_other;
        $payment->category = "customer";
        $payment->reference_code = $this->reference_code;
        $payment->account_id = $this->account_id;
        $payment->amount = $this->amount;
        if(isset($this->selectedCustomer) && isset($this->selectedCurrency) &&  $this->transaction_category == "Customer Deposits"){
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

        if (isset($this->account_id)) {
            $account = Account::find($this->account_id);
            $current_balance = $account->balance;
            $account->balance = $current_balance + $this->amount;
            $account->update();
        }

        
        $receipt =  new Receipt;
        $receipt->payment_id = $payment->id;
        $receipt->company_id = $payment->company_id;
        $receipt->currency_id = $payment->currency_id;
        $receipt->receipt_number = $this->receiptNumber(); ;
        $receipt->user_id = Auth::user()->id;
        $receipt->amount = $this->amount;
        $receipt->date = $this->date;
        $receipt->save();

       
        $this->dispatchBrowserEvent('hide-paymentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Recorded Successfully!!"
        ]);
    
       
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }

        $this->customers = Customer::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();

        if (isset($this->selectedCustomer) && isset($this->selectedCurrency)) { 
            $this->last_payment = Payment::where('customer_id',$this->selectedCustomer)->where('currency_id',$this->selectedCurrency)->where('transaction_category',  $this->transaction_category)->orderBy('created_at','desc')->first();
        }


        if (isset($this->from) && isset($this->to)) {

            if (isset($this->search)) {

                return view('livewire.payments.index',[
                    'payments' => Payment::query()->with(['customer','currency'])->whereBetween($this->payment_filter,[$this->from, $this->to] )
                    ->where('payment_number','like', '%'.$this->search.'%')
                    ->orWhere('transaction_category','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('mode_of_payment','like', '%'.$this->search.'%')
                    ->orWhereHas('transaction_type', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->payment_filter,'desc')->paginate(10),
                  
                ]);

            }else {
               
                return view('livewire.payments.index',[
                    'payments' => Payment::query()->with(['customer','currency'])
                    ->whereBetween($this->payment_filter,[$this->from, $this->to] )
                    ->orderBy($this->payment_filter,'desc')->paginate(10),
                  
                ]);
            }
           
        }
        elseif (isset($this->search)) {
           
            return view('livewire.payments.index',[
                'payments' => Payment::query()->with(['customer','currency'])
                ->whereMonth($this->payment_filter,date('m'))
                ->whereYear($this->payment_filter,date('Y'))
                ->where('payment_number','like', '%'.$this->search.'%')
                ->orWhere('transaction_category','like', '%'.$this->search.'%')
                ->orWhere('mode_of_payment','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhereHas('transaction_type', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('customer', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->payment_filter,'desc')->paginate(10),
            ]);

        }
        else {
            return view('livewire.payments.index',[
                'payments' => Payment::query()->with(['customer:id,name','currency'])->whereMonth($this->payment_filter, date('m'))
                ->whereYear($this->payment_filter, date('Y'))->orderBy($this->payment_filter,'desc')->paginate(10),
              
            ]);
        }

        return view('livewire.payments.index',[
            'payments' => $this->payments,
            'customers' => $this->customers,
        ]);
    }
}
