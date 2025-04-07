<?php

namespace App\Http\Livewire\CreditNotes;


use App\Models\Invoice;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CreditNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{


    public $invoices;
    public $search;
    protected $queryString = ['search'];
    public $credit_note_reason;
    public $invoice_products;
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

    public function mount($id){

        $this->credit_note = CreditNote::find($id);
        $this->currencies = Currency::all();
        $this->invoices = Invoice::where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->invoice = Invoice::where('invoice_number', 'LIKE', '%'.$this->search.'%')
                                ->where('authorization','approved')->first();
        if (isset( $this->invoice)) {
            $this->selectedInvoice = $this->invoice->id;
        }

        $this->memo = $this->credit_note->memo;
        $this->footer = $this->credit_note->footer;
        $this->user_id = $this->credit_note->user_id;
        $this->company_id = $this->credit_note->company_id;
        $this->qty = 1;
        $this->currency_id = $this->credit_note->currency_id;
        $this->subheading = $this->credit_note->subheading;
        $this->date = $this->credit_note->date;
        $this->credit_note_id = $this->credit_note->id;
        $this->credit_note_reason = $this->credit_note->credit_note_reason;
        $this->tax_rate = $this->credit_note->tax_rate;
        $this->selectedInvoice = $this->credit_note->invoice_id;
        $this->invoice_amount = $this->credit_note->invoice_amount;
        $this->invoice_balance = $this->credit_note->invoice_balance;
        $this->credit_note_number = $this->credit_note->credit_note_number;
        $this->total = $this->credit_note->total;
        $this->amount = $this->credit_note->subtotal;
        $this->subtotal = $this->credit_note->subtotal;
    

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
        // 'memo' => 'required',
        // 'subheading' => 'required',
        'item' => 'required',
        'description' => 'required',
        'qty' => 'required',
        'amount' => 'required',
        'date' => 'required',
        // 'footer' => 'required',
    ];




    public function update()
    {
        if ($this->credit_note_id) {
            $invoice = Invoice::find($this->selectedInvoice);
            $credit_note = CreditNote::find($this->credit_note_id);
            $credit_note->user_id = Auth::user()->id;
            $credit_note->company_id = Auth::user()->employee->company_id;
            $credit_note->customer_id = $invoice->customer_id;
            $credit_note->currency_id = $this->currency_id;
            $credit_note->tax_rate = $this->tax_rate;
            $credit_note->tax_amount = $this->tax_amount;
            $credit_note->invoice_id = $this->selectedInvoice;
            $credit_note->credit_note_number = $this->credit_note_number;
            $credit_note->date = $this->date;
            $credit_note->memo = $this->memo;
            $credit_note->credit_note_reason = $this->credit_note_reason;
            $credit_note->footer = $this->footer;
            $credit_note->subheading = $this->subheading;
            $credit_note->exchange_rate = $this->exchange_rate;
            $credit_note->subtotal = $this->subtotal;
            $credit_note->total = $this->total;
            $credit_note->update();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Credit Note Updated Successfully!!"
            ]);
            return redirect()->route('credit_notes.index');

        }
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
        return view('livewire.credit-notes.edit');
    }
}
