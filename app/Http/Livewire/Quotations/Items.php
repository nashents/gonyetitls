<?php

namespace App\Http\Livewire\Quotations;

use App\Models\Trip;
use App\Models\Account;
use App\Models\quotation;
use App\Models\Product;
use Livewire\Component;
use App\Models\Destination;
use App\Models\quotationItem;
use App\Models\IncomeStream;
use Illuminate\Support\Facades\Auth;

class Items extends Component
{
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
    public $quotation_total;
    public $quotation_subtotal;
    public $total;
    public $selectedTrip;
    public $description;
    public $quotation;
    public $quotation_id;
    public $quotation_items;
    public $quotation_item_id;
    public $accounts;
    public $quotation_item;
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
        $this->quotation_id = $id;
        $this->quotation = Quotation::find($id);
        $this->subtotal =  $this->quotation->subtotal;
        $this->total =   $this->quotation->total;
        $this->tax_amount =   $this->quotation->tax_amount; 
        $this->quotation_items = $this->quotation->quotation_items;

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
        
            $quotation_item = new QuotationItem;
            $quotation_item->quotation_id = $this->quotation->id;
            $quotation_item->tax_id = $this->selectedTax;
            $quotation_item->tax_rate = $this->tax_rate;
            $quotation_item->product_id = $this->selectedItem;
            $quotation_item->qty = $this->qty;
            $quotation_item->amount = $this->amount;

            if (is_numeric($this->amount) && is_numeric($this->qty)) {

                $quotation_item_subtotal = $this->amount*$this->qty;
                $quotation_item->subtotal = $quotation_item_subtotal;
                $this->subtotal = $this->subtotal + $quotation_item_subtotal;

            }

            if (is_numeric($quotation_item_subtotal) && is_numeric($this->tax_rate)) {
                $item_tax_amount = ($quotation_item_subtotal * ($this->tax_rate / 100 ));
                $quotation_item->tax_amount =  $item_tax_amount;
                $this->subtotal_incl = $item_tax_amount + $quotation_item_subtotal ;
                $this->tax_amount = $this->tax_amount + $item_tax_amount;
            }else{
                $this->subtotal_incl = $quotation_item_subtotal;
            }
        
            $quotation_item->subtotal_incl = $this->subtotal_incl;
            $quotation_item->save();

            $this->total = $this->total +  $this->subtotal_incl;
    

            $quotation = Quotation::find($this->quotation->id);
            $quotation->tax_amount =  $this->tax_amount;
            $quotation->subtotal = $this->subtotal;
            $quotation->total = $this->total;
            $quotation->update();



            $this->dispatchBrowserEvent('hide-addquotationItemModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item Added Successfully!!"
            ]);
    
    }





  
    public function removeShow($id){

        $this->quotation_item = QuotationItem::find($id);
        $this->tax_amount = $this->quotation->tax_amount;
        $this->total = $this->quotation->total;
        $this->subtotal = $this->quotation->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removequotationItem(){ 

        $this->subtotal = $this->subtotal - $this->quotation_item->subtotal;
        $this->total = $this->total - $this->quotation_item->subtotal_incl;
        $this->tax_amount = $this->tax_amount - $this->quotation_item->tax_amount;

        $quotation =  Quotation::find($this->quotation->id);
        $quotation->total = $this->total;
        $quotation->subtotal = $this->subtotal;
        $quotation->tax_amount = $this->tax_amount;
        $quotation->update();

        $this->quotation_item->delete();
        
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Deleted Successfully!!"
        ]);

   

    }


    public function edit($id){
        $this->quotation_item = QuotationItem::find($id);
        $this->selectedProduct = $this->quotation_item->product_id;
        $this->selectedTax = $this->quotation_item->tax_id;
        $this->tax_rate = $this->quotation_item->tax_rate;
        $this->qty = $this->quotation_item->qty;
        $this->amount = $this->quotation_item->amount;


        $this->current_item_amount = $this->quotation_item->amount;
        $this->current_item_tax_amount = $this->quotation_item->tax_amount;
        $this->current_item_subtotal = $this->quotation_item->subtotal;
        $this->current_item_subtotal_incl = $this->quotation_item->subtotal_incl;
        

        $this->tax_amount = $this->quotation->tax_amount;
        $this->total = $this->quotation->total;
        $this->subtotal = $this->quotation->subtotal;
       
      
        $this->quotation_item_id = $id;
        $this->dispatchBrowserEvent('show-editquotationItemModal');
    }

    public function update(){
        $quotation_item = QuotationItem::find($this->quotation_item_id);
        $quotation_item->product_id = $this->selectedProduct;
        $quotation_item->tax_id = $this->selectedTax;
        $quotation_item->tax_rate = $this->tax_rate;
        $quotation_item->amount = $this->amount;
        $quotation_item->qty = $this->qty;

        if (is_numeric($this->amount) && is_numeric($this->qty)) {
            $item_subtotal = $this->amount*$this->qty;
            $quotation_item->subtotal = $item_subtotal;
            $this->subtotal = ($this->subtotal - $this->current_item_subtotal) + $item_subtotal;
        }
        if (is_numeric($this->tax_rate)) {

            $item_tax_amount = ($item_subtotal * ($this->tax_rate / 100 ));
            $quotation_item->tax_amount =  $item_tax_amount;
            $quotation_item->tax_rate =  $this->tax_rate;
            $item_subtotal_incl = $item_tax_amount + $item_subtotal ;
            $quotation_item->subtotal_incl =  $item_subtotal_incl;
            $this->tax_amount =($this->tax_amount-$this->current_item_tax_amount)+$item_tax_amount;
            $this->total = ($this->total-$this->current_item_total)+$item_subtotal_incl;
            
        }else{
            $item_subtotal_incl = $item_subtotal ;
            $quotation_item->subtotal_incl =  $item_subtotal_incl;
            $this->total = ($this->total-$this->current_item_total)+$item_subtotal_incl;
        }

        $quotation_item->update();

        $quotation =  Quotation::find($this->quotation->id);
        $quotation->total = $this->total;
        $quotation->subtotal =  $this->subtotal;
        $quotation->tax_amount = $this->tax_amount;
        $quotation->tax_amount =  $this->total;
        $quotation->update();
        
        $this->dispatchBrowserEvent('hide-editquotationItemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Updated Successfully!!"
        ]);
    }

   
    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->quotation_total) && $this->quotation_total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }

        $this->quotation_items = QuotationItem::where('quotation_id',$this->quotation_id)->get();
        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();
        return view('livewire.quotations.items',[
            'quotation_items' => $this->quotation_items,
            'products' => $this->products,
            'tax_accounts' => $this->tax_accounts,
        ]);
    }
}
