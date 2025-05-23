<?php

namespace App\Http\Livewire\CreditNotes;

use App\Models\Trip;
use App\Models\Cargo;
use App\Models\Invoice;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CreditNote;
use App\Models\BankAccount;
use App\Models\Destination;
use App\Models\Notification;
use App\Models\CreditNoteItem;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;
use Illuminate\Support\Facades\Session;

class Create extends Component
{


    public $invoices;
    public $search;
    protected $queryString = ['search'];
    public $reason;
    public $invoice_products;
    public $invoice;
    public $invoice_product_id;
    public $credit_note_number;
    public $selectedInvoice;
    public $customers;
    public $customer_id;
    public $company_id;
    public $currencies;
    public $currency_id;
    public $date;
    public $subheading;
    public $memo;
    public $footer;
    public $credit_note_reason;
    public $item;
    public $initials;
    public $tax_rate;
    public $tax_amount;
    public $credit_note_amount;
    public $exchange_rate;
    public $exchange_amount;
    public $description;
    public $amount;
    public $qty;
    public $subtotal;
    public $total;
    public $invoice_total;
    public $user_id;
    public $type;
    public $selectedCustomer;
    public $customer;
    public $product_name;
    public $product_description;

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
    
    private function resetInputFields(){
        $this->product_name = '';
    }

    public function updatedSelectedInvoice($id){
        if (!is_null($id)) {
            $this->invoice = Invoice::find($this->selectedInvoice);
            $this->currency_id = $this->invoice->currency_id;
            $this->tax_rate =  $this->invoice->tax_;
            $this->tax_rate =  $this->invoice->tax_rate;
            $this->tax_amount =  $this->invoice->tax_amount;
            $this->amount =  $this->invoice->subtotal;
            $this->subtotal =  $this->invoice->subtotal;
            $this->total =  $this->invoice->total;
            $this->selectedCustomer =  $this->invoice->customer_id;
            $this->customer = Customer::find( $this->invoice->customer_id);
            $this->initials = $this->customer->initials;
        }
    }

    public function storeProduct(){
        try{
    
            $product = new InvoiceProduct;
            $product->user_id = Auth::user()->id;
            $product->name = $this->product_name;
            $product->save(); 
    
            $this->dispatchBrowserEvent('hide-invoice_productModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Product Created Successfully!!"
            ]);
    
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while creating product!!"
                ]);
             }
        }

    public function credit_noteNumber(){
        if (Auth::user()->employee->company->invoice_serialize_by_customer == TRUE) {
            if (isset($this->initials) &&  $this->initials != NULL) {
            }else {
                $str = Auth::user()->employee->company->name;
                $words = explode(' ', $str);
                if (isset($words[1][0])) {
                    $this->initials = $words[0][0].$words[1][0];
                }else {
                    $this->initials = $words[0][0];
                }
            }
            $credit_note = CreditNote::where('customer_id', $this->selectedCustomer)->orderBy('id', 'desc')->get()->first();
    
            if (!$credit_note) {
                $this->number = 1;
                $credit_note_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                if ($credit_note->number) {
                    $this->number = $credit_note->number + 1;
                }else{
                    $this->number = $credit_note->id + 1;
                }
               
                $credit_note_number = $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $credit_note_number;
        }else {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $this->initials = $words[0][0].$words[1][0];
            }else {
                $this->initials = $words[0][0];
            }
            $credit_note = CreditNote::orderBy('id','desc')->first();
            if (!$credit_note) {
                $this->number = 1;
                $credit_note_number =  $this->initials .'CN'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                $this->number = $credit_note->id + 1;
                $credit_note_number =  $this->initials .'CN'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $credit_note_number;
        }
    
       
    
    
    }

    public function mount(){
        $this->credit_note_number = $this->credit_noteNumber();
        $this->currencies = Currency::all();
        $this->qty = 1;
        $this->invoice_products = InvoiceProduct::orderBy('name','asc')->get();
        $this->invoices = Invoice::where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->company_id = Auth::user()->employee->company->id;
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

        'trip_id.required' => 'Trip field is required',
        'bank_account_id.required' => 'Bank Account field is required',

    ];

    protected $rules = [
        'selectedInvoice' => 'required',
        'currency_id' => 'required',
        'credit_note_number' => 'required',
        'item' => 'required',
        'description' => 'required',
        'qty' => 'required',
        'amount' => 'required',
        'date' => 'required',
    ];

   

    public function store(){
        
        $credit_note = new CreditNote;
        $credit_note->user_id = Auth::user()->id;
        $credit_note->company_id = Auth::user()->employee->company_id;
        $credit_note->customer_id = $this->invoice->customer_id;
        $credit_note->currency_id = $this->currency_id;
        $credit_note->tax_rate = $this->tax_rate;
        $credit_note->tax_amount = $this->tax_amount;
        $credit_note->invoice_id = $this->selectedInvoice;
        $credit_note->credit_note_number = $this->credit_note_number;
        $credit_note->date = $this->date;
        $credit_note->memo = $this->memo;
        $credit_note->footer = $this->footer;
        $credit_note->subheading = $this->subheading;
        $credit_note->credit_note_reason = $this->credit_note_reason;
        $credit_note->exchange_rate = $this->exchange_rate;
        $credit_note->subtotal = $this->subtotal;
        $credit_note->total = $this->total;
        $credit_note->save();

        $notifications = Notification::where('category','Credit Note Authorization')->where('status',1)->get();
        $company =  $this->company;
        
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($company, $notification, $credit_note));
                }
                }
            }
        }


        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Credit Note Created Successfully!!"
        ]);

        return redirect()->route('credit_notes.index');
    }


    public function render()
    {
        if (isset($this->search)) {
            $this->invoices = Invoice::query()->with('customer:id,name')
                                ->where('authorization', 'approved')
                                ->where('invoice_number', 'like', '%'.$this->search.'%')
                                ->orWhere('date', 'like', '%'.$this->search.'%')
                                ->orWhereHas('customer', function ($query) {
                                 return $query->where('name', 'like', '%'.$this->search.'%');
                                })->orderBy('created_at','desc')->get();
        }
               
        return view('livewire.credit-notes.create');



    }
}
