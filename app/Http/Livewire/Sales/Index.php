<?php

namespace App\Http\Livewire\Sales;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Document;
use App\Models\BankAccount;
use App\Models\SalePayment;
use App\Models\Denomination;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
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
    public $sale_filter;
    public $trip_filter;
    private $sales;
    public $sale_products;
    public $sale;
    public $sale_id;
    public $customers;
    public $currencies;
    public $currency_id;
    public $receipt_number;
    public $sale_number;
    public $date;
    public $amount;
    public $receipt;
    public $balance;   
    public $accounts;
    public $account_id;
    public $bank_accounts;
    public $bank_account_id;
    public $trips;
    public $trip;
    public $trip_id;
    public $notes;
    public $sale_balance;
    public $pop;
    public $reference_code;
    public $sale_currency;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $user_id;
    public $customer_id;
    public $current_balance;
    public $mode_of_payment;
    public $selectedCustomer ;
    public $customer_accounts;
    public $selectedCustomerAccount;
    public $unpaid_sales;
    public $selectedSale;
    public $account_payments;
    public $payment_id;
    public $selectedPayment;
    public $drawdown_sale_balance;
    public $drawdown_amount;
    public $sale_drawdown_current_balance;
    public $sale_drawdown_balance;
    public $payment_drawdown_balance;
    public $amount_paid;
    public $unsaled_trips;
    public $item_subtotal = 0;
    public $subtotal = 0;
    public $subtotal_incl = 0;
    public $total = 0;

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
        $this->resetPage();
        $this->sale_filter = "created_at";
        $this->currencies = Currency::latest()->get();
        $this->bank_accounts = BankAccount::latest()->get();
        $this->customers = Customer::latest()->get();
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


    public function updatedSelectedCustomer($id){
        if (!is_null($id)) {
            $this->selectedCustomer = $id;
            $this->customer_accounts = Account::where('customer_id',$id)->latest()->get();
        } 
    }

    public function updatedSelectedCustomerAccount($id){
        if (!is_null($id)) {
            $this->selectedCustomerAccount = $id;
            $account = Account::find($id);
            $currency_id = $account->currency_id;
            $this->unpaid_sales = Sale::where('customer_id',$this->selectedCustomer)
            ->where('currency_id',$currency_id)
            ->where('authorization','approved')
            ->where('status','Unpaid')
            ->orWhere('customer_id',$this->selectedCustomer)
            ->where('currency_id',$currency_id)
            ->where('authorization','approved')
            ->where('status','Partial')
            ->orderBy('created_at','desc')->get();

            $this->account_payments = Payment::where('customer_account_id',$id)
            ->where('drawdown_balance','>',0)
            ->orderBy('created_at','desc')->get();
        } 
    }

    public function updatedSelectedPayment($id){
        if (!is_null($id)) {
            $this->selectedPayment = $id;
            $payment = Payment::find($id);
            $this->drawdown_amount = $payment->drawdown_balance;
        }
    }   

    public function updatedSelectedSale($id){
        if (!is_null($id)) {
        $this->selectedSale = $id;
        $this->sale = Sale::find($id);
        $this->sale_drawdown_balance = $this->sale->balance;
      

        if (isset($this->drawdown_amount) && isset($this->sale_drawdown_balance) && ($this->drawdown_amount >= $this->sale_drawdown_balance)) {
            $this->payment_drawdown_balance = $this->drawdown_amount - $this->sale_drawdown_balance;
            $this->sale_drawdown_balance = 0;
            $this->amount_paid = $this->sale_drawdown_balance;
        }else{
            $this->sale_drawdown_balance = $this->sale_drawdown_balance - $this->drawdown_amount;
            $this->payment_drawdown_balance = 0;
            $this->amount_paid = $this->drawdown_amount;
        }
        
        }
      
    }




    public function showPayment($id){
        $this->sale_id = $id ;
        $this->sale = Sale::find($id);
        $this->sale_currency = $this->sale->currency;
        $this->customer_id = $this->sale->customer_id;
        $this->sale_balance = $this->sale->balance;
        $this->current_balance = $this->sale_balance - $this->amount;
        $this->dispatchBrowserEvent('show-paymentModal');
    }

    public function drawdownPayments(){

        $payment = Payment::find($this->selectedPayment);
        $payment->drawdown_balance = $this->payment_drawdown_balance;
        $payment->update();
        
        $sale = Sale::find($this->selectedSale); 

        $sale_payment = new SalePayment;
        $sale_payment->sale_id = $this->selectedSale;
        $sale_payment->customer_id = $sale->customer_id;
        $sale_payment->payment_id = $this->selectedPayment;
        $sale_payment->currency_id = $sale->currency_id;
        $sale_payment->amount = $this->amount_paid;
        $sale_payment->save();
      
    

        $sale = Sale::find($this->selectedSale);
        $sale->balance = $this->sale_drawdown_balance;
        if ($this->sale_drawdown_balance <= 0) {
            $sale->status = "Paid";
        }else {
            $sale->status = "Partial";
        }
        $sale->update();
 
        $this->dispatchBrowserEvent('hide-paymentDrawdownModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Drawdown Effected Successfully!!"
        ]);
       
    }

    public function saleNumber(){
  
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $this->initials = $words[0][0].$words[1][0];
            }else {
                $this->initials = $words[0][0];
            }
            $sale = Sale::orderBy('id','desc')->first();
            if (!$sale) {
                $this->number = 1;
                $sale_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                $this->number = $sale->id + 1;
                $sale_number =  $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $sale_number;
      
    
    }

    public function recordPayment(){

      
        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
        $payment->customer_id = $this->sale->customer_id;
        $payment->user_id = Auth::user()->id;
        $payment->currency_id = $this->sale->currency_id;
        $payment->payment_number = $this->paymentNumber();   
        $payment->name = $this->name;
        $payment->notes = $this->notes;
        $payment->surname = $this->surname;
        $payment->mode_of_payment = $this->mode_of_payment;
        $payment->category = "sale";
        $payment->reference_code = $this->reference_code;
        $payment->bank_account_id = $this->bank_account_id;
        $payment->sale_id = $this->sale->id;
        $payment->account_id = $this->account_id;
        $payment->amount = $this->amount;
        if ($this->sale) {
            $this->current_balance = $this->sale->balance - $this->amount;
        }
        $payment->balance = $this->current_balance;
        $payment->date = $this->date;
        $payment->save();

        $sale_payment = new SalePayment;
        $sale_payment->customer_id = $this->sale->customer_id;
        $sale_payment->sale_id = $this->sale->id;
        $sale_payment->payment_id = $payment->id;
        $sale_payment->currency_id = $this->sale->currency_id;
        $sale_payment->amount = $this->amount;
        $sale_payment->save();  
        

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


        $sale = Sale::find($this->sale->id);
        $sale->balance = $this->current_balance;
        if ($this->current_balance <= 0) {
            $sale->status = "Paid";
        }else {
            $sale->status = "Partial";
        }
        $sale->update();

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
     

        
        $cashflow = new CashFlow;
        $cashflow->user_id = Auth::user()->id;
        $cashflow->customer_id = $this->sale->customer_id;
        $cashflow->currency_id =$this->sale->currency_id;
        $cashflow->sale_id = $this->sale->id;
        $cashflow->payment_id = $payment->id;
        $cashflow->account_id = $this->account_id;
        $cashflow->type = 'Direct';
        $cashflow->sub_type = 'Income';
        $cashflow->category = 'Sale';
        $cashflow->transaction_type = 'Deposit';
        $cashflow->date = $payment->date;
        $cashflow->amount = $payment->amount;
        $cashflow->save();        

      
        $receipt =  new Receipt;
        $receipt->payment_id = $payment->id;
        $receipt->company_id = $payment->company_id;
        $receipt->sale_id = $payment->sale_id;
        $receipt->vendor_id = $payment->vendor_id;
        $receipt->transporter_id = $payment->transporter_id;
        $receipt->currency_id = $payment->currency_id;
        $receipt->receipt_number = $this->receiptNumber(); ;
        $receipt->user_id = Auth::user()->id;
        $receipt->amount = $this->amount;
        $receipt->balance = $this->current_balance;
        $receipt->date = $this->date;
        $receipt->save();

       
        $this->dispatchBrowserEvent('hide-paymentModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Recorded Successfully!!"
        ]);
        return redirect()->route('payments.index');
       
    }

   
    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render()
    {
        $this->amount;
        $this->sale_balance;

        if ($this->sale_balance != "" && $this->amount != "") {
            $this->current_balance = $this->sale_balance - $this->amount;
        }

  
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.sales.index',[
                        'sales' => Sale::query()->with(['customer:id,name','currency'])->whereBetween($this->sale_filter,[$this->from, $this->to] )
                        ->where('sale_number','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('expiry','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->sale_filter,'desc')->paginate(10),
                        'sale_filter' => $this->sale_filter,
                        'current_balance' => $this->current_balance
                    ]);
                }else {
                    return view('livewire.sales.index',[
                        'sales' => Sale::query()->with(['customer:id,name','currency'])->whereBetween($this->sale_filter,[$this->from, $this->to] )->orderBy($this->sale_filter,'desc')->paginate(10),
                        'sale_filter' => $this->sale_filter,
                        'current_balance' => $this->current_balance
                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.sales.index',[
                    'sales' => Sale::query()->with(['customer:id,name','currency'])->whereMonth($this->sale_filter, date('m'))
                    ->whereYear($this->sale_filter, date('Y'))
                    ->where('sale_number','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('expiry','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->sale_filter,'desc')->paginate(10),
                        'sale_filter' => $this->sale_filter,
                        'current_balance' => $this->current_balance
                ]);
            }
            else {
               
                return view('livewire.sales.index',[
                    'sales' => Sale::query()->with(['customer:id,name','currency'])->whereMonth($this->sale_filter, date('m'))
                    ->whereYear($this->sale_filter, date('Y'))->orderBy($this->sale_filter,'desc')->paginate(10),
                    'sale_filter' => $this->sale_filter,
                    'current_balance' => $this->current_balance
                ]);
              
            }
        

    }
}
