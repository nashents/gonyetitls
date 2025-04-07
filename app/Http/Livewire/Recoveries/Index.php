<?php

namespace App\Http\Livewire\Recoveries;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Document;
use App\Models\Recovery;
use App\Models\BankAccount;
use App\Models\Denomination;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithFileUploads;

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $recoveries;
    public $recovery_products;
    public $recovery;
    public $recovery_id;
    public $customers;
    public $recovery_currency;
    public $currencies;
    public $currency_id;
    public $receipt_number;
    public $recovery_number;
    public $date;
    public $amount;
    public $receipt;
    public $balance;   
    public $bank_accounts;
    public $bank_account_id;
    public $accounts;
    public $account_id;
    public $trips;
    public $trip;
    public $trip_id;
    public $notes;
    public $recovery_balance;
    public $pop;
    public $reference_code;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $user_id;
    public $customer_id;
    public $current_balance;
    public $mode_of_payment;

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
   

    public function mount(){
     
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->recoveries = Recovery::orderBy('recovery_number','desc')->paginate(10);
        } else {
            $this->recoveries = Recovery::where('user_id',Auth::user()->id)->orderBy('recovery_number','desc')->paginate(10);
        }
    
        $this->currencies = Currency::latest()->get();
        $this->bank_accounts = BankAccount::latest()->get();
        $this->accounts = Account::where('account_type_id',1)->latest()->get();
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

    public function showPayment($id){
        $this->recovery_id = $id ;
        $this->recovery = Recovery::find($id);
        $this->recovery_balance = $this->recovery->balance;
        $this->recovery_currency = $this->recovery->currency;
        $this->name = $this->recovery->driver->employee->name;
        $this->surname = $this->recovery->driver->employee->surname;
        $this->current_balance = $this->recovery_balance - $this->amount;
        $this->dispatchBrowserEvent('show-paymentModal');
    }

    public function recordPayment(){

        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
        $payment->driver_id = $this->recovery->driver_id;
        $payment->recovery_id = $this->recovery->id;
        $payment->user_id = Auth::user()->id;
        $payment->currency_id = $this->recovery->currency_id;
        $payment->payment_number = $this->paymentNumber();   
        $payment->name = $this->name;
        $payment->category = "recovery";
        $payment->notes = $this->notes;
        $payment->surname = $this->surname;
        $payment->mode_of_payment = $this->mode_of_payment;
      
        $payment->reference_code = $this->reference_code;
        $payment->bank_account_id = $this->bank_account_id;
        $payment->account_id = $this->account_id;
        $payment->amount = $this->amount;
        if ($this->recovery) {
            $this->current_balance = $this->recovery->balance - $this->amount;
        }
        $payment->balance = $this->current_balance;
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


        $recovery = Recovery::find($this->recovery->id);
        $recovery->balance = $this->current_balance;
        if ($this->current_balance <= 0) {
            $recovery->status = "Paid";
        }else {
            $recovery->status = "Partial";
        }
        $recovery->update();

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

       

        $cashflow = new CashFlow;
        $cashflow->user_id = Auth::user()->id;
        $cashflow->driver_id = $this->recovery->driver_id;
        $cashflow->currency_id =$this->recovery->currency_id;
        $cashflow->recovery_id = $this->recovery->id;
        $cashflow->account_id = $this->account_id;
        $cashflow->type = 'InDirect';
        $cashflow->sub_type = 'Income';
        $cashflow->category = 'Recovery';
        $cashflow->date = $payment->date;
        $cashflow->amount = $payment->amount;
        $cashflow->save();
    
        $account = Account::find($this->account_id);
        $current_balance = $account->balance;
        $account->balance = $current_balance + $this->amount;
        $account->update();

      
        $receipt =  new Receipt;
        $receipt->payment_id = $payment->id;
        $receipt->company_id = $payment->company_id;
        $receipt->recovery_id = $payment->recovery_id;
        $receipt->driver_id = $payment->driver_id;
        $receipt->currency_id = $payment->currency_id;
        $receipt->receipt_number = $this->receiptNumber(); ;
        $receipt->user_id = Auth::user()->id;
        $receipt->amount = $this->amount;
        $receipt->balance = $this->current_balance;
        $receipt->date = $this->date;
        $receipt->save();

       
        $this->dispatchBrowserEvent('hide-paymentModal');
        return redirect()->route('payments.index');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Recorded Successfully!!"
        ]);
    }

    public function render()
    {

             
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->recoveries = Recovery::orderBy('recovery_number','desc')->paginate(10);
        } else {
            $this->recoveries = Recovery::where('user_id',Auth::user()->id)->orderBy('recovery_number','desc')->paginate(10);
        }

        if ($this->recovery_balance != "" && $this->amount != "") {
            $this->current_balance = $this->recovery_balance - $this->amount;
        }
      
        return view('livewire.recoveries.index',[
            'recoveries' => $this->recoveries,
            'current_balance' => $this->current_balance
        ]);
    }
}
