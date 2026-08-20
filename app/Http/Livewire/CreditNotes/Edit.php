<?php

namespace App\Http\Livewire\CreditNotes;


use App\Models\Invoice;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CreditNote;
use App\Models\InvoiceItem;
use App\Models\Tax;
use Illuminate\Support\Str;
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
    public $credit_note;
    public $invoice;
    public $credit_note_id;
    public $invoice_amount;
    public $invoice_balance;
    public $invoiceItems = [];
    public $rows = [];
    public $invoice_attached = 'Yes';
    public $tax_accounts = [];

    public function mount($id){

        $this->credit_note = CreditNote::find($id);
        $this->currencies = Currency::all();
        $this->invoices = Invoice::where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name', 'Value Added Tax');
        })->orderBy('name', 'asc')->get();

        $this->memo = $this->credit_note->memo;
        $this->footer = $this->credit_note->footer;
        $this->user_id = $this->credit_note->user_id;
        $this->company_id = $this->credit_note->company_id;
        $this->currency_id = $this->credit_note->currency_id;
        $this->subheading = $this->credit_note->subheading;
        $this->date = $this->credit_note->date;
        $this->credit_note_id = $this->credit_note->id;
        $this->credit_note_reason = $this->credit_note->credit_note_reason;
        $this->tax_rate = $this->credit_note->tax_rate;
        $this->tax_amount = $this->credit_note->tax_amount;
        $this->selectedInvoice = $this->credit_note->invoice_id;
        $this->invoice = $this->credit_note->invoice;
        $this->invoiceItems = $this->invoice ? $this->invoice->invoice_items : collect();
        $this->invoice_amount = $this->credit_note->invoice_amount;
        $this->invoice_balance = $this->credit_note->invoice_balance;
        $this->credit_note_number = $this->credit_note->credit_note_number;
        $this->total = $this->credit_note->total;
        $this->subtotal = $this->credit_note->subtotal;
        $this->selectedCustomer = $this->credit_note->customer_id;
        $this->invoice_attached = $this->credit_note->invoice_id ? 'Yes' : 'No';

        $this->rows = $this->credit_note->credit_note_items->map(function ($item) {
            return [
                'description' => $item->description,
                'amount' => $item->amount,
                'tax_id' => $item->tax_id,
                'tax_amount' => $item->tax_amount,
                'subtotal_incl' => $item->subtotal_inclusive,
            ];
        })->values()->toArray();

        if (empty($this->rows)) {
            $this->rows = [['description' => '', 'amount' => '', 'tax_id' => '', 'tax_amount' => 0, 'subtotal_incl' => 0]];
        }
    }

    public function updatedInvoiceAttached($value){
        $this->selectedInvoice = null;
        $this->invoice = null;
        $this->invoiceItems = [];
        $this->selectedCustomer = null;
        $this->customer = null;
        $this->currency_id = null;
        $this->rows = [];
        $this->recalculateTotals();
    }

    public function updatedSelectedInvoice($id){
        if (!is_null($id)) {
            $this->invoice = Invoice::find($this->selectedInvoice);
            $this->currency_id = $this->invoice->currency_id;
            $this->tax_rate = $this->invoice->tax_rate;
            $this->invoiceItems = $this->invoice->invoice_items;
        }
    }

    public function updatedSelectedCustomer($id){
        if ($this->invoice_attached === 'No' && !is_null($id)) {
            $this->customer = Customer::find($id);
        }
    }

    public function addFromInvoiceItem($invoiceItemId){
        $invoiceItem = InvoiceItem::where('invoice_id', $this->selectedInvoice)->find($invoiceItemId);
        if ($invoiceItem) {
            $index = count($this->rows);
            $this->rows[] = [
                'description' => $invoiceItem->description ?: $invoiceItem->trip_details,
                'amount' => $invoiceItem->subtotal,
                'tax_id' => $invoiceItem->tax_id,
                'tax_amount' => 0,
                'subtotal_incl' => 0,
            ];
            $this->recalculateRowTax($index);
            $this->recalculateTotals();
        }
    }

    public function addRow(){
        $this->rows[] = ['description' => '', 'amount' => '', 'tax_id' => '', 'tax_amount' => 0, 'subtotal_incl' => 0];
    }

    public function removeRow($index){
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->recalculateTotals();
    }

    private function recalculateRowTax($index){
        if (!isset($this->rows[$index])) {
            return;
        }

        $amount = (float) ($this->rows[$index]['amount'] ?? 0);
        $taxId = $this->rows[$index]['tax_id'] ?? null;
        $rate = 0;

        if ($taxId) {
            $tax = Tax::find($taxId);
            $rate = $tax->rate ?? 0;
        }

        $taxAmount = $amount * ($rate / 100);
        $this->rows[$index]['tax_amount'] = $taxAmount;
        $this->rows[$index]['subtotal_incl'] = $amount + $taxAmount;
    }

    private function recalculateTotals(){
        $this->subtotal = collect($this->rows)->sum(fn($row) => (float) ($row['amount'] ?? 0));
        $this->tax_amount = collect($this->rows)->sum(fn($row) => (float) ($row['tax_amount'] ?? 0));
        $this->total = $this->subtotal + $this->tax_amount;
    }

    public function updated($value){
        $this->validateOnly($value);
        if (Str::startsWith($value, 'rows.')) {
            $segments = explode('.', $value);
            $index = $segments[1] ?? null;
            if ($index !== null && isset($this->rows[$index])) {
                $this->recalculateRowTax($index);
            }
            $this->recalculateTotals();
        }
    }
    protected $messages =[

        'trip_id.required' => 'Trip field is required',
        'bank_account_id.required' => 'Bank Account field is required',

    ];

    protected function rules(){
        $rules = [
            'currency_id' => 'required',
            'credit_note_number' => 'required',
            'date' => 'required',
            'rows' => 'required|array|min:1',
            'rows.*.description' => 'required|string',
            'rows.*.amount' => 'required|numeric',
            'rows.*.tax_id' => 'required',
        ];

        if ($this->invoice_attached === 'Yes') {
            $rules['selectedInvoice'] = 'required';
        } else {
            $rules['selectedCustomer'] = 'required';
        }

        return $rules;
    }




    public function update()
    {
        if ($this->credit_note_id) {
            $this->validate();
            $this->recalculateTotals();

            $credit_note = CreditNote::find($this->credit_note_id);
            $credit_note->user_id = Auth::user()->id;
            $credit_note->company_id = Auth::user()->employee->company_id;
            $credit_note->currency_id = $this->currency_id;
            $credit_note->tax_rate = $this->tax_rate;
            $credit_note->tax_amount = $this->tax_amount;
            $credit_note->credit_note_number = $this->credit_note_number;
            $credit_note->date = $this->date;
            $credit_note->memo = $this->memo;
            $credit_note->credit_note_reason = $this->credit_note_reason;
            $credit_note->footer = $this->footer;
            $credit_note->subheading = $this->subheading;
            $credit_note->exchange_rate = $this->exchange_rate;
            $credit_note->subtotal = $this->subtotal;
            $credit_note->total = $this->total;

            if ($this->invoice_attached === 'Yes') {
                $invoice = Invoice::find($this->selectedInvoice);
                $credit_note->customer_id = $invoice->customer_id;
                $credit_note->invoice_id = $this->selectedInvoice;
                $credit_note->invoice_amount = $invoice->total;
                $credit_note->invoice_balance = $invoice->total - $this->total;
            } else {
                $credit_note->customer_id = $this->selectedCustomer;
                $credit_note->invoice_id = null;
                $credit_note->invoice_amount = null;
                $credit_note->invoice_balance = null;
            }

            $credit_note->update();

            $credit_note->credit_note_items()->delete();
            foreach ($this->rows as $row) {
                $credit_note->credit_note_items()->create([
                    'user_id' => Auth::user()->id,
                    'description' => $row['description'],
                    'qty' => 1,
                    'amount' => $row['amount'],
                    'subtotal' => $row['amount'],
                    'tax_id' => $row['tax_id'] ?: null,
                    'tax_amount' => $row['tax_amount'] ?? 0,
                    'subtotal_inclusive' => $row['subtotal_incl'] ?? $row['amount'],
                ]);
            }

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
