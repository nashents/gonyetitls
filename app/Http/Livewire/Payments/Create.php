<?php

namespace App\Http\Livewire\Payments;


use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\Denomination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{

    use WithFileUploads;
    
    public $bank_accounts;
    public $bank_account_id;
    public $currencies;
    public $currency_id;
    public $bills;
    public $bill;
    public $bill_id;
    public $invoices;
    public $invoice;
    public $invoice_id;
    public $total;
    public $pop;
    public $reference_code;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $user_id;
    public $customer_id;
    public $amount;
    public $balance;
    public $mode_of_payment;
    public $date;
    public $search = NULL;   
    public $invoice_products;
    public $customers;
    public $receipt_number;
    public $invoice_number;
    public $receipt;
    public $invoice_balance;
    public $current_balance;
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

    public $bill_inputs = [];
    public $o = 1;
    public $m = 1;

    public function addBill($o)
    {
        $o = $o + 1;
        $this->o = $o;
        array_push($this->bill_inputs ,$o);
    }

    public function removeBill($o)
    {
        unset($this->bill_inputs[$o]);
    }

    public $denomination_inputs = [];
    public $s = 1;
    public $r = 1;

    public function addDenomination($s)
    {
        $s = $s + 1;
        $this->s = $s;
        array_push($this->denomination_inputs ,$s);
    }

    public function removeDenomination($s)
    {
        unset($this->denomination_inputs[$s]);
    }

    public function mount(){
        $this->invoices = Invoice::orderBy('invoice_number', 'asc')->get();
        $this->bills = Bill::orderBy('bill_number', 'asc')->get();
        $this->currencies = Currency::latest()->get();
        $this->bank_accounts = BankAccount::latest()->get();
    }
  


    public function payment($id){
        $this->invoice_id = $id ;
        $this->invoice = Invoice::find($id);
        $this->total = $this->invoice->total;
        $this->balance = $this->total - $this->amount;
        $this->dispatchBrowserEvent('show-paymentModal');
    }

    public function store(){

        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
        $payment->user_id = Auth::user()->id;
        $payment->payment_number = $this->paymentNumber();   
        $payment->name = $this->name;
        $payment->surname = $this->surname;
        $payment->mode_of_payment = $this->mode_of_payment;
        $payment->reference_code = $this->reference_code;
        $payment->bank_account_id = $this->bank_account_id;
        $payment->amount = $this->amount;
        $payment->date = $this->date;
        $payment->save();

        if (isset($this->invoice_id)) {
            foreach ($this->invoice_id as $key => $value) {
               $this->invoice = Invoice::find($this->invoice_id[$key]);
               $this->current_balance = $this->invoice->balance - $this->amount;
            }
        }elseif (isset($this->bill_id)) {
            foreach ($this->bill_id as $key => $value) {
                $this->bill = Bill::find($this->bill_id[$key]);
                $this->current_balance = $this->bill->balance - $this->amount;
             }
        }
      
        $payment = Payment::find($payment->id);
        $payment->balance = $this->current_balance;
        $payment->update();

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

        }
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
  

        $invoice = Invoice::find($this->invoice->id);
        $invoice->balance = $this->current_balance;
        if ($this->current_balance <= 0) {
            $invoice->status = "Paid";
        }else {
            $invoice->status = "Partial";
        }
        $invoice->update();

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

        $receipt =  new Receipt;
        $receipt->payment_id = $payment->id;
        $receipt->company_id = $payment->company_id;
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
        if ($this->total != "" && $this->amount != "") {
            $this->balance = $this->total - $this->amount;
        }
       
        $this->invoices = Invoice::where('invoice_number','LIKE', '%'.$this->search.'%')->latest()->get();
        return view('livewire.payments.create',[
            'invoices' => $this->invoices,
            'balance' => $this->balance,
        ]);
    }
}
