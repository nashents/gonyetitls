<?php

namespace App\Http\Livewire\Bills;


use App\Models\Bill;
use App\Models\Brand;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Document;
use App\Models\BankAccount;
use App\Exports\BillsExport;
use App\Models\Denomination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\CategoryValue;
use Livewire\WithFileUploads;
use App\Models\TransactionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithFileUploads;

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $bank_accounts;
    public $bank_account_id;
    public $currencies;
    public $currency_id;
    public $selectedCurrency;
    public $payment_type;
    public $trips;
    public $trip;
    public $trip_id;
    private $bills;
    public $bill;
    public $bill_id;
    public $bill_balance;
    public $bill_currency;
    public $bill_filter;
    public $pop;
    public $reference_code;
    public $transaction_types;
    public $transaction_type_id;
    public $transaction_category;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $notes;
    public $user_id;
    public $customer_id;
    public $amount;
    public $current_balance;
    public $mode_of_payment;
    public $accounts;
    public $account_id;
    public $accrual_balance;
    public $date;
    public $inputs = [];
    public $i = 1;
    public $n = 1;


    public $exchange_amount;
    public $exchange_rate;

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
   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'account_id' => 'required',
    ];

    private function resetInputFields(){
        $this->payment_type = '';
        $this->method_of_payment = '';
    }

    private function recalculateBills(){
        $bills = Bill::where('trip_id',"!=", Null)
                        ->where('transporter_id','!=', Null)
                        ->where('status','!=','Paid')->get();

        if ((isset($bills) && $bills->count()>0)) {
                foreach ($bills as $bill) {
                    if (Auth::user()->employee->company->offloading_details == TRUE) {
                        $delivery_note = $bill->trip->delivery_note;
                        $bill->total = $delivery_note->transporter_offloaded_freight; 
                    }
                }
        }
    }


    public function exportBillsCSV(Excel $excel){
        return $excel->download(new BillsExport($this->from, $this->to, $this->bill_filter,  $this->search), 'bills_' .time().'.csv', Excel::CSV);
    }
    public function exportBillsPDF(Excel $excel){
        return $excel->download(new BillsExport($this->from, $this->to, $this->bill_filter,  $this->search), 'bills_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportBillsExcel(Excel $excel){
        return $excel->download(new BillsExport($this->from, $this->to, $this->bill_filter,  $this->search), 'bills_' .time().'.xlsx');
    }

    public function mount(){
        
        $this->resetPage();
        $this->recalculateBills();
        $this->bill_filter = "created_at";
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->transaction_type_id = TransactionType::where('name','Withdrawal')->first()->id;
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
        $this->bill_id = $id ;
        $this->bill = Bill::find($id);
        $this->name = Auth::user()->name;
        $this->surname = Auth::user()->surname;
        $this->selectedCurrency = $this->bill->currency_id;
        $this->bill_currency = $this->bill->currency;
        $this->bill_balance = $this->bill->balance;
        $this->current_balance = $this->bill_balance - $this->amount;
        $this->dispatchBrowserEvent('show-paymentModal');
    }

    public function recordPayment(){

        $account = Account::find($this->account_id);
        $current_balance = $account->balance;
        if (isset($current_balance)) {
           
        if ($current_balance >= $this->amount ) {
    
        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
        $payment->vendor_id = $this->bill->vendor_id;
        $payment->transporter_id = $this->bill->transporter_id;
        $payment->container_id = $this->bill->container_id;
        $payment->top_up_id = $this->bill->top_up_id;
        $payment->trip_id = $this->bill->trip_id;
        $payment->bill_id = $this->bill->id;
        $payment->transaction_type_id = $this->transaction_type_id;
        $payment->movement = "Dbt";
        $payment->description =  $this->bill->notes;
        $payment->user_id = Auth::user()->id;
        $payment->currency_id = $this->bill->currency_id;
        $payment->payment_number = $this->paymentNumber();   
        $payment->name = $this->name;
        $payment->surname = $this->surname;
        $payment->notes = $this->notes;
        $payment->category = "Bill";
        $payment->mode_of_payment = $this->mode_of_payment;
        $payment->reference_code = $this->reference_code;
        $payment->bank_account_id = $this->bank_account_id;
        $payment->account_id = $this->account_id;
        $payment->amount = $this->amount;
        $payment->exchange_rate = $this->exchange_rate;
        $payment->exchange_amount = $this->exchange_amount;
        if (is_numeric($this->bill->balance) && is_numeric($this->amount)) {
            $this->current_balance = $this->bill->balance - $this->amount;
        }
        $payment->balance = $this->current_balance;

        $last_bill = Bill::where('authorization','approved')->where('vendor_id',$this->bill->vendor_id)->where('currency_id', $this->bill->currency_id)->whereRaw('accrual_balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->where('accrual_balance','>',0)->orderBy('accrual_balance','desc')->first();
        if(isset($last_bill)){
            if((isset($last_bill->accrual_balance) && is_numeric($last_bill->accrual_balance)) && is_numeric($this->amount)){
                $payment->accrual_balance = $last_bill->accrual_balance - $this->amount;
            }
        }else{
            $accrual_balance = Bill::where('authorization','approved')->where('vendor_id',$this->bill->vendor_id)->where('accrual_balance', Null)->where('currency_id', $this->bill->currency_id)->whereRaw('balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('balance');
            if(isset($accrual_balance)){
                $payment->accrual_balance = $accrual_balance - $this->amount;
            }
        }

        $payment->date = $this->date;
        $payment->save();

        $account->balance = $current_balance - $this->amount;
        $account->update();

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

        $bill = Bill::find($this->bill->id);
        $bill->balance = $this->current_balance;
        if ($this->current_balance <= 0) {
            $bill->status = "Paid";
        }else {
            $bill->status = "Partial";
        }
        $bill->update();

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



        $this->dispatchBrowserEvent('hide-paymentModal');
        
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Recorded Successfully!!"
        ]);
        return redirect()->route('payments.index');
              # code...
            } else {

         $this->dispatchBrowserEvent('hide-paymentModal');
         $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Transaction failed, amount to pay exceeds account floating balance!!"
        ]);
                # code...
            }
        }
    }

    
    public function dateRange(){
 
        // $this->resetPage();
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

        if ($this->bill_balance != "" && $this->amount != "") {
            $this->current_balance = $this->bill_balance - $this->amount;
        }
        
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.bills.index',[
                        'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')
                        ->whereDate($this->bill_filter, '>=', $this->from)
                        ->whereDate($this->bill_filter, '<=', $this->to)
                        ->where('to_be_paid', True)
                        ->where('bill_number','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('bill_date','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('driver', function ($query) {
                            $query->whereHas('employee', function ($subQuery) {
                                $subQuery->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                            });
                        })
                        ->orWhereHas('ticket', function ($query) {
                            return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trip', function ($query) {
                            return $query->where('trip_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('invoice', function ($query) {
                            return $query->where('invoice_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('transporter', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('container', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('purchase', function ($query) {
                            return $query->where('purchase_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vendor', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->bill_filter,'desc')->paginate(10),
                       
    

                    ]);
                }else {
                    return view('livewire.bills.index',[
                        'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')
                        ->whereDate($this->bill_filter, '>=', $this->from)
                        ->whereDate($this->bill_filter, '<=', $this->to)
                        ->where('to_be_paid', True)
                        ->orderBy($this->bill_filter,'desc')->paginate(10),

                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.bills.index',[
                    'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')
                    ->whereMonth($this->bill_filter, date('m'))
                    ->whereYear($this->bill_filter, date('Y'))
                    ->where('to_be_paid', True)
                    ->where('bill_number','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('bill_date','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('driver', function ($query) {
                        $query->whereHas('employee', function ($subQuery) {
                            $subQuery->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                        });
                    })
                    ->orWhereHas('ticket', function ($query) {
                        return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trip', function ($query) {
                        return $query->where('trip_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('invoice', function ($query) {
                        return $query->where('invoice_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('container', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('purchase', function ($query) {
                        return $query->where('purchase_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->bill_filter,'desc')->paginate(10),
                ]);
            }
            else {
               
                return view('livewire.bills.index',[
                    'bills' => Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')
                    ->whereMonth($this->bill_filter, date('m'))
                    ->whereYear($this->bill_filter, date('Y'))
                    ->where('to_be_paid', True)
                    ->orderBy($this->bill_filter,'desc')->paginate(10),
                ]);
              
            }
        
        

   

    }
}
