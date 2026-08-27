<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Trip;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Product;
use Livewire\Component;
use App\Models\Destination;
use App\Models\InvoiceItem;
use App\Models\IncomeStream;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InvoiceItems extends Component
{

     use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $income_streams;
    public $income_stream;
    public $products;
    public $product;
    public $selectedItem;
    public $income_stream_id;
    public $reason;
    public $qty;
    public $amount;
    public $trips;
    public $subtotal;
    public $subtotal_incl;
    public $item_subtotal;
    public $tax_rate;
    public $tax_accounts;
    public $selectedTax;
    public $tax_amount;
    public $total_tax_amount;
    public $invoice_total;
    public $invoice_subtotal;
    public $total;
    public $selectedTrip;
    public $description;
    public $invoice;
    public $invoice_id;
    protected $invoice_items;
    public $invoice_item_id;
    public $accounts;
    public $invoice_item;
    public $current_item_amount;
    public $current_item_tax_amount;
    public $current_item_subtotal;
    public $current_item_subtotal_incl;
    public $current_item_total;
    public $exchange_rate = 1;
    public $exchange_amount = 0;
    public $selectedProduct;

    public $item_name;
    public $item_description;
    public $sell_price;
    public $buy_price;
    public $tax_id;
    public $tax;
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $sell = True;
    public $buy = False;


    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    private function resetInputFields(){
        $this->selectedTrip = "" ;
        $this->description = "" ;
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount($id){
        $this->invoice_id = $id;
        $this->invoice = Invoice::find($id);
        $this->subtotal =  $this->invoice->subtotal;
        $this->total =   $this->invoice->total;
        $this->tax_amount =   $this->invoice->tax_amount; 
        $this->accounts = Account::where('account_type_id',1)->latest()->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->income_account_id = Account::where('name','Sales')->first()->id;
        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();
        $this->loadTrips();

    }

    private function loadTrips(){
        $this->trips = Trip::query()
            ->with('customer:id,name')
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->when($this->invoice->invoice_type !== 'advance', function ($q) {
                $q->where('trip_status', 'Offloaded');
            })
            ->when($this->invoice->customer_id, function ($q) {
                $q->where('customer_id', $this->invoice->customer_id);
            })
            ->orderBy('trip_number', 'desc')
            ->limit(200)
            ->get();
    }

    /**
     * Mirrors Invoices\Create::tripIsInvoiceable() — earned invoices require the
     * trip to be Offloaded, advance invoices only require approved & not cancelled.
     */
    private function tripIsInvoiceable(?Trip $trip): bool
    {
        if (!$trip || strcasecmp((string) $trip->authorization, 'approved') !== 0) {
            return false;
        }

        if (strcasecmp((string) $trip->trip_status, 'Cancelled') === 0) {
            return false;
        }

        if ($this->invoice->invoice_type !== 'advance' && strcasecmp((string) $trip->trip_status, 'Offloaded') !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Links an existing trip to this already-created invoice (e.g. a generic
     * prepayment invoice) as a zero-value line, purely so the trip's computed
     * is_invoiced status flips true — it does not touch the invoice's totals.
     */
    public function storeTrip(){
        $this->validate([
            'selectedTrip' => 'required|exists:trips,id',
        ]);

        $trip = Trip::find($this->selectedTrip);

        if (!$this->tripIsInvoiceable($trip)) {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"This trip is not eligible to be linked (it must be approved and not cancelled)."
            ]);
            return;
        }

        if (InvoiceItem::where('invoice_id', $this->invoice->id)->where('trip_id', $trip->id)->exists()) {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Trip #{$trip->trip_number} is already linked to this invoice."
            ]);
            return;
        }

        $invoice_item = new InvoiceItem;
        $invoice_item->invoice_id = $this->invoice->id;
        $invoice_item->trip_id = $trip->id;
        $invoice_item->description = $this->description ?: "Trip #{$trip->trip_number} - prepayment applied";
        $invoice_item->qty = 1;
        $invoice_item->amount = 0;
        $invoice_item->subtotal = 0;
        $invoice_item->tax_amount = 0;
        $invoice_item->subtotal_incl = 0;
        $invoice_item->save();

        $this->dispatchBrowserEvent('hide-linkTripModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip #{$trip->trip_number} linked to this invoice successfully!!"
        ]);
    }

    public function updatedSelectedItem($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->sell_price) {
                    $this->amount = $product->sell_price;
                }
                if ($product->tax_id) {
                    $this->selectedTax = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate = $tax->rate;
                    }
                }  
            }
        }

    }

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate = $tax->rate;
            }
           
        }
    }

    
    public function storeItem(){
        try{
        
            $product = new Product;
            $product->user_id = Auth::user()->id;
            $product->name = $this->item_name;
            $product->description = $this->item_description;
            $product->price = $this->buy_price;
            $product->sell_price = $this->sell_price;
            $product->department = "inventory";
            $product->sell = $this->sell;
            $product->buy = $this->buy;
            $product->account_id = $this->income_account_id;
            $product->expense_account_id = $this->expense_account_id;
            $product->tax_id = $this->tax_id;
            $product->save(); 
    
            $this->dispatchBrowserEvent('hide-product_serviceModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item Created Successfully!!"
            ]);
    
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while creating item!!"
                ]);
             }
        }


        public function store(){
        
            $invoice_item = new InvoiceItem;
            $invoice_item->invoice_id = $this->invoice->id;
            $invoice_item->tax_id = $this->selectedTax;
            $invoice_item->tax_rate = $this->tax_rate;
            $invoice_item->product_id = $this->selectedItem;
            $invoice_item->qty = $this->qty;
            $invoice_item->amount = $this->amount;

            if (is_numeric($this->amount) && is_numeric($this->qty)) {

                $invoice_item_subtotal = $this->amount*$this->qty;
                $invoice_item->subtotal = $invoice_item_subtotal;
                $this->subtotal = $this->subtotal + $invoice_item_subtotal;

            }

            if (is_numeric($invoice_item_subtotal) && is_numeric($this->tax_rate)) {
                $item_tax_amount = ($invoice_item_subtotal * ($this->tax_rate / 100 ));
                $invoice_item->tax_amount =  $item_tax_amount;
                $this->subtotal_incl = $item_tax_amount + $invoice_item_subtotal ;
                $this->tax_amount = $this->tax_amount + $item_tax_amount;
            }else{
                $this->subtotal_incl = $invoice_item_subtotal;
            }
        
            $invoice_item->subtotal_incl = $this->subtotal_incl;
            $invoice_item->save();

            $this->total = $this->total +  $this->subtotal_incl;
    

            $invoice = Invoice::find($this->invoice->id);
            $invoice->tax_amount =  $this->tax_amount;
            $invoice->subtotal = $this->subtotal;
            $invoice->total = $this->total;
            $invoice->update();



            $this->dispatchBrowserEvent('hide-addInvoiceItemModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item Added Successfully!!"
            ]);
    
    }





  
    public function removeShow($id){

        $this->invoice_item = InvoiceItem::find($id);
        $this->tax_amount = $this->invoice->tax_amount;
        $this->total = $this->invoice->total;
        $this->subtotal = $this->invoice->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeInvoiceItem(){ 

        $this->subtotal = $this->subtotal - $this->invoice_item->subtotal;
        $this->total = $this->total - $this->invoice_item->subtotal_incl;
        $this->tax_amount = $this->tax_amount - $this->invoice_item->tax_amount;

        $invoice =  Invoice::find($this->invoice->id);
        $invoice->total = $this->total;
        $invoice->subtotal = $this->subtotal;
        $invoice->tax_amount = $this->tax_amount;
        $invoice->update();

        $this->invoice_item->delete();
        
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Deleted Successfully!!"
        ]);

   

    }


    public function edit($id){
        $this->invoice_item = InvoiceItem::find($id);
        $this->selectedProduct = $this->invoice_item->product_id;
        $this->selectedTax = $this->invoice_item->tax_id;
        $this->tax_rate = $this->invoice_item->tax_rate;
        $this->qty = $this->invoice_item->qty;
        $this->amount = $this->invoice_item->amount;


        $this->current_item_amount = $this->invoice_item->amount;
        $this->current_item_tax_amount = $this->invoice_item->tax_amount;
        $this->current_item_subtotal = $this->invoice_item->subtotal;
        $this->current_item_subtotal_incl = $this->invoice_item->subtotal_incl;
        

        $this->tax_amount = $this->invoice->tax_amount;
        $this->total = $this->invoice->total;
        $this->subtotal = $this->invoice->subtotal;
       
      
        $this->invoice_item_id = $id;
        $this->dispatchBrowserEvent('show-editInvoiceItemModal');
    }

    public function update(){
        $invoice_item = InvoiceItem::find($this->invoice_item_id);
        $invoice_item->product_id = $this->selectedProduct;
        $invoice_item->tax_id = $this->selectedTax;
        $invoice_item->tax_rate = $this->tax_rate;
        $invoice_item->amount = $this->amount;
        $invoice_item->qty = $this->qty;

        if (is_numeric($this->amount) && is_numeric($this->qty)) {
            $item_subtotal = $this->amount*$this->qty;
            $invoice_item->subtotal = $item_subtotal;
            $this->subtotal = ($this->subtotal - $this->current_item_subtotal) + $item_subtotal;
        }
        if (is_numeric($this->tax_rate)) {

            $item_tax_amount = ($item_subtotal * ($this->tax_rate / 100 ));
            $invoice_item->tax_amount =  $item_tax_amount;
            $invoice_item->tax_rate =  $this->tax_rate;
            $item_subtotal_incl = $item_tax_amount + $item_subtotal ;
            $invoice_item->subtotal_incl =  $item_subtotal_incl;
            $this->tax_amount =($this->tax_amount-$this->current_item_tax_amount)+$item_tax_amount;
            $this->total = ($this->total-$this->current_item_total)+$item_subtotal_incl;
            
        }else{
            $item_subtotal_incl = $item_subtotal ;
            $invoice_item->subtotal_incl =  $item_subtotal_incl;
            $this->total = ($this->total-$this->current_item_total)+$item_subtotal_incl;
        }

        $invoice_item->update();

        $invoice =  Invoice::find($this->invoice->id);
        $invoice->total = $this->total;
        $invoice->subtotal =  $this->subtotal;
        $invoice->tax_amount = $this->tax_amount;
        $invoice->tax_amount =  $this->total;
        $invoice->update();
        
        $this->dispatchBrowserEvent('hide-editInvoiceItemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Updated Successfully!!"
        ]);
    }

   
    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->invoice_total) && $this->invoice_total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }

        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();
        return view('livewire.invoices.invoice-items',[
            'invoice_items' => InvoiceItem::where('invoice_id',$this->invoice_id)->paginate(10),
            'products' => $this->products,
            'tax_accounts' => $this->tax_accounts,
        ]);
    }
}
