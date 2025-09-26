<?php

namespace App\Http\Livewire\Invoices;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Product;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\BankAccount;
use App\Models\Destination;
use App\Models\InvoiceItem;
use App\Models\Measurement;
use App\Models\ExchangeRate;
use App\Models\TripDocument;
use App\Models\InventoryDispatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{


    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

    public $inventories;
    public $selectedInventory = [];
    // public $trips;
    public $selectedTrip = [];

    public $fiscalize_invoice;
    public $reason;
    public $trip_filter;

    public $products;
    public $inventory_products;
    public $weight = [];
    public $measurement = [];
    public $selectedProduct = [];
    public $description = [];
    public $selectedAccount = [];
    public $qty = [];
    public $amount = [];
    public $invoices;
    public $invoice_number;
    public $number;
    public $invoice_id;
    public $initials;
    public $values;
    public $customers;
    public $item_key;
    public $item_status;
    public $invoice_items;

        //discount vars
        public $discount_name;
        public $is_discount;
        public $discount_description;
        public $discount_unit;
        public $discount_amount;

    
    public $selectedCurrentTax;
    public $selectedCurrentProduct;
    public $selectedCurrentTrip;
    public $selectedCurrentInventory;
    public $selectedCurrentAccount;
    public $current_qty;
    public $current_description;
    public $current_measurement;
    public $old_weight;
    public $old_inventory_id;
    public $current_weight;
    public $current_amount;
    public $current_tax_rate;
    public $current_hs_code;
    public $current_tax_id;
    public $current_subtotal;
  
    public $recorded_payments;
    public $exchange_rate;
    public $from_inventory;
    public $from_trips;
    public $record_payment;
    public $exchange_amount;
    public $selectedCustomer;
    public $bank_accounts;
    public $bank_account_id;
    public $company_id;
    public $company;
    public $currencies;
    public $destinations;
    public $purchase_order_number;
    public $sales_order_number;
    public $pat_number;
    public $selectedCurrency;
    public $selected_currency;
    public $tax_rate = [];
    public $hs_code = [];
   
    public $measurements;
    public $invoice_amount = 0;
    public $invoice_sub_amount = 0;
    public $turnover = 0;
    public $invoice_total_amount = 0;
    public $date;
    public $expiry;
    public $subheading;
    public $memo;
    public $footer;

    public $item_name;
    public $item_description;
    public $sell_price;
    public $buy_price;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    public $selectedTax = [];
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $invoice;
    public $discount;
    public $sell = True;
    public $buy = False;

    // payment vars

    public $accounts;
    public $account_id;
    public $notes;
    public $invoice_balance;
    public $pop;
    public $reference_code;
    public $invoice_currency;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $customer_id;
    public $current_balance;
    public $mode_of_payment;
    public $payment_amount;

    //customer vars
    public $customer_name;
    public $phonenumber;
    public $currency_id;
    public $worknumber;
    public $email;
    public $country;
    public $tin_number;
    public $vat_number;
    public $city;
    public $suburb;
    public $street_address;
    public $invoice_item;

    // bank acc vars

    public $bank_name;
    public $bank_currency_id;
    public $type;
    public $account_name;
    public $account_number;
    public $branch;
    public $branch_code;
    public $swift_code;
    public $status;
       
    public $tax_amount;
    public $total_customer_expenses = 0;
    public $item_subtotal = 0;
    public $subtotal = 0;
    public $subtotal_incl = 0;
    public $total = 0;
    public $user_id;
  
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

    public function resetInputFields(){
        $this->total = Null;
        $this->subtotal = Null;
        $this->tax_amount = Null;

        $this->item_name = '';
        $this->item_description = '';
        $this->buy_price = '';
        $this->sell_price = '';
        $this->tax_id = '';
        $this->sell = True;
        $this->buy = False;
    }

    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
         
            $product = Product::find($id);
            $this->selectedAccount[$key] = $product->account_id;
            if (isset($product)) {
                if ($product->sell_price) {
                    $this->amount[$key] = $product->sell_price;
                }
                $this->qty[$key] = 1;
                $this->description[$key] = $product->description;
                if ($product->tax_id) {
                    $this->selectedTax[$key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$key] = $tax->rate;
                        $this->hs_code[$key] = $tax->hs_code;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            $product = $inventory->product;
            $this->qty[$key] = 1;
            $this->amount[$key] = $inventory->amount;
            $this->measurement[$key] = $inventory->measurement;
            $this->weight[$key] = $inventory->balance;
            $this->description[$key] = $product->description;
            if ($product->tax_id) {
                $this->selectedTax[$key] = $product->tax_id;
                $tax = Account::find($product->tax_id);
                if (isset($tax)) {
                    $this->tax_rate[$key] = $tax->rate;
                    $this->hs_code[$key] = $tax->hs_code;
                }
                
            }  
            
           
        }
    }

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate[$key] = $tax->rate;
                $this->hs_code[$key] = $tax->hs_code;
            }else{
                $this->tax_rate[$key] = "";
                $this->hs_code[$key] = "";
            }
           
        }
    }


       public function discount(){
        $this->is_discount = True;
    }
    public function removeDiscount($id){
        $invoice = Invoice::find($id);
        $discount = $invoice->discount;
        if($discount){
            $discount->delete();
        }

        $this->is_discount = False;
        $this->discount_amount = "";
        $this->discount_description = "";
        $this->discount_unit = "";
    }

    public function showItem($key){
        $this->item_key = $key;
        $this->income_account_id = Account::where('name','Sales')->first()->id;
        $this->dispatchBrowserEvent('show-product_serviceModal');
    }

    public function productNumber(){

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

        $product = Product::orderBy('id','desc')->first();

        if (!$product) {
            $product_number =  $initials .'AP'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $product->id + 1;
            $product_number =  $initials .'AP'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $product_number;


    }

    public function storeItem(){
        // try{
        
            $product = new Product;
            $product->user_id = Auth::user()->id;
            $product->product_number = $this->productNumber();
            $product->name = $this->item_name;
            $product->description = $this->item_description;
            $product->price = $this->buy_price;
            $product->sell_price = $this->sell_price;
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
    
            // }
            //     catch(\Exception $e){
            //     // Set Flash Message
            //     $this->dispatchBrowserEvent('alert',[
            //         'type'=>'error',
            //         'message'=>"Something went wrong while creating item!!"
            //     ]);
            //  }
        }

    public function updatedSelectedTrip($id, $key){
      
        if (!is_null($id)) {
            $trip = Trip::find($id);
            $delivery_note = $trip->delivery_note;
            if (isset($trip)) {

                foreach($trip->trip_expenses as $expense){

                    if (isset($expense->category)  && isset($expense->currency_id)) {
    
                        if ($expense->currency_id == $trip->currency_id) {
                            if ($expense->category == "Customer") {
                               if(is_numeric($expense->amount)){
                                $this->total_customer_expenses = $this->total_customer_expenses + $expense->amount;
                               }
                               
                            }
                           
                        }
                    }
                }
                if (isset($this->total_customer_expenses) && $this->total_customer_expenses > 0) {
                    if($this->values == "scheduled"){
                        $this->amount[$key] = $trip->freight + $this->total_customer_expenses; 
                    }elseif($this->values == "loading"){
                        $this->amount[$key] = $delivery_note->loaded_freight + $this->total_customer_expenses; 
                    }elseif($this->values == "offloading"){
                        $this->amount[$key] =  $delivery_note->offloaded_freight + $this->total_customer_expenses; 
                    }
                 
                }else{
                    if($this->values == "scheduled"){
                        $this->amount[$key] = $trip->freight ; 
                    }elseif($this->values == "loading"){
                        $this->amount[$key] = $delivery_note->loaded_freight ; 
                    }elseif($this->values == "offloading"){
                        $this->amount[$key] = $delivery_note->offloaded_freight ; 
                    }
                   
                }
               
                $this->description[$key] = $this->setDescription($id); 
                $this->qty[$key] = 1; 
                $this->selectedAccount[$key] =  $this->income_account_id;
            }
           
            
        }

        // if(isset($this->amount[$key])){
        //     $this->invoice_amount = $this->invoice_amount + $this->amount[$key];
        // }
       
    }
    public function updatedSelectedCurrentTrip($id, $key){
      
        if (!is_null($id)) {
            
            $trip = Trip::find($id);
            if (isset($trip)) {
                foreach($trip->trip_expenses as $expense){

                    if (isset($expense->category)  && isset($expense->currency_id)) {
    
                        if ($expense->currency_id == $trip->currency_id) {
                            if ($expense->category == "Customer") {
                               if(is_numeric($expense->amount)){
                                $this->total_customer_expenses = $this->total_customer_expenses + $expense->amount;
                               }
                               
                            }
                           
                        }
                    }
                }
                if (isset($this->total_customer_expenses) && $this->total_customer_expenses > 0) {
                    $this->current_amount[$key] = $trip->freight + $this->total_customer_expenses ; 
                }else{
                    $this->current_amount[$key] = $trip->freight ; 
                }
                
                $this->current_description[$key] = $this->setDescription($id); 
                $this->current_qty[$key] = 1; 
                $this->selectedCurrentAccount[$key] =  $this->income_account_id;
            }
           
            
        }

        // if(isset($this->current_amount[$key])){
        //     $this->invoice_amount = $this->invoice_amount + $this->current_amount[$key];
        // }
       
    }

    public function updatedSelectedCurrentInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            $product = $inventory->product;
            $this->current_qty[$key] = 1;
            $this->current_amount[$key] = $inventory->amount;
            $this->current_measurement[$key] = $inventory->measurement;
            $this->current_weight[$key] = $inventory->balance;
            $this->current_description[$key] = $product->description;
            if ($product->tax_id) {
                $this->selectedCurrentTax[$key] = $product->tax_id;
                $tax = Account::find($product->tax_id);
                if (isset($tax)) {
                    $this->current_tax_rate[$key] = $tax->rate;
                    $this->current_hs_code[$key] = $tax->hs_code;
                }
                
            }  
            
           
        }
    }

    public function updatedselectedCurrentProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->sell_price) {
                    $this->current_amount[$key] = $product->sell_price;
                }
                $this->selectedCurrentAccount[$key] = $product->account_id;
                $this->current_qty[$key] = 1;
                $this->current_description[$key] = $product->description;

                if ($product->tax_id) {
                    $this->selectedCurrentTax[$key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->current_tax_rate[$key] = $tax->rate;
                        $this->current_hs_code[$key] = $tax->hs_code;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedselectedCurrentTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->current_tax_rate[$key] = $tax->rate;
                $this->current_hs_code[$key] = $tax->hs_code;
            }else{
                $this->current_tax_rate[$key] = "";
                $this->current_hs_code[$key] = "";
            }
           
        }
    }

    

    public function setDescription($id){

        $trip = Trip::find($id);
                
        $cargo = $trip->cargo;
        $weight = $trip->weight.'tons' ;
        $cargo_name = $cargo ? $cargo->name : "";

        if (isset($cargo)) {
            if ($trip->cargo->type == "Solid") {
                $cargo_measurement = $trip->quantity.' '. $trip->measurement;
            }else {
                $cargo_measurement =  $trip->litreage_at_20.' '. $trip->measurement;
            }
            
        }else{
            $cargo_measurement = "";
        }
      
      

        if ($trip->horse) {
            $regnumber = $trip->horse ? $trip->horse->registration_number : "";
        }elseif ($trip->vehicle) {
            $regnumber = $trip->vehicle ? $trip->vehicle->registration_number : "";
        }else{
            $regnumber = "";
        }
        
        $origin = Destination::find($trip->from);
        $origin_city = $origin ? $origin->city : "";
        $destination = Destination::find($trip->to);
        $destination_city = $destination ? $destination->city : "";
        $symbol =  $trip->currency ? $trip->currency->symbol : "";

     

        if ($trip->freight_calculation) {
            
            if ($trip->freight_calculation == "flat_rate") {
               $formula = "R";
               $variables =  $symbol.$trip->rate;
            }
            elseif($trip->freight_calculation == "rate_weight"){
               
                if ($trip->cargo) {
                    if ($trip->cargo->type == "Solid") {
                        $formula = "R*W";
                        $variables = $symbol.$trip->rate.'*'.$trip->weight;
                    }elseif ($trip->cargo->type == "Liquid") {
                        $formula = "R*L";
                        $variables = $symbol.$trip->rate.'*'.$trip->litreage_at_20;
                    }
                }else{
                    $formula = "";
                    $variables = "";
                }
              
            }
            elseif($trip->freight_calculation == "rate_distance"){
                $formula = "R*D";
                $variables = $symbol.$trip->rate.'*'.$trip->distance;
            }
            elseif($trip->freight_calculation == "rate_weight_distance"){
                if ($trip->cargo) {
                    if ($trip->cargo->type == "Solid") {
                        $formula = "R*W*D";
                        $variables = $symbol.$trip->rate.'*'.$trip->weight.'*'.$trip->distance;
                    }elseif ($trip->cargo->type == "Liquid") {
                        $formula = "R*L*D";
                        $variables = $symbol.$trip->rate.'*'.$trip->litreage_at_20.'*'.$trip->distance;
                    }
                }else{
                    $formula = "";
                    $variables = "";
                }
            }else {
                $formula = "";
                $variables = "";
            }
        }else{
            $formula = "";
            $variables = "";
        }
        
        $lp = $trip->loading_point ? $trip->loading_point->name : "";
        $op = $trip->offloading_point ? $trip->offloading_point->name : "";
        $from = $origin_city.' '.$lp ;
        $to = $destination_city.' '.$op ;
        $rate = $trip->rate;
        $quantity = $trip->quantity.' '.$trip->measurement;
        $litreage = $trip->litreage_at_20.' '.$trip->measurement;
        $trip_number = $trip->trip_number;
        $document = TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
        $pod_number = $document ? $document->document_number : "";
      

        if (isset($cargo) && $cargo->type == "Solid") {
            $cargo_description = $cargo_name .' '.$weight.' '. $quantity;
        }elseif (isset($cargo) && $cargo->type == "Liquid") {
            $cargo_description =  $cargo_name.' '.$weight .' '. $litreage;
        }else {
            $cargo_description = "";
        }
       
        $load_details = $cargo_description .' '.$formula .' '.$variables;

       $trip_details = $load_details .' '.  $from .' to '.  $to .' '.  $regnumber.' '.$pod_number;

        return  $trip_details;
    }


    public function removeShow($id){
        $this->invoice_item = InvoiceItem::find($id);
        $this->tax_amount = $this->invoice->tax_amount;
        $this->total = $this->invoice->total;
        $this->subtotal = $this->invoice->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeInvoiceItem(){ 

        if (is_numeric($this->subtotal) && $this->invoice_item->subtotal) {
            $this->subtotal = $this->subtotal - $this->invoice_item->subtotal;
        }
        if (is_numeric($this->total) && $this->invoice_item->subtotal_incl) {
            $this->total = $this->total - $this->invoice_item->subtotal_incl;
        }
        if (is_numeric($this->tax_amount) && $this->invoice_item->tax_amount) {
            $this->tax_amount = $this->tax_amount - $this->invoice_item->tax_amount;

        }

        $invoice =  Invoice::find($this->invoice->id);
        $invoice->total = $this->total;
        $invoice->subtotal = $this->subtotal;
        $invoice->tax_amount = $this->tax_amount;
        $invoice->balance = $this->total;
        $invoice->update();

        $this->invoice_item->delete();
        $this->invoice_items = InvoiceItem::where('invoice_id',$this->invoice_id)->get();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Deleted Successfully!!"
        ]);
       

   

    }

    public function mount($invoice){
        $this->company = Auth::user()->employee->company;
        $this->trip_filter = "created_at";
        $this->invoice_id = $invoice->id;
        $this->invoice = $invoice;
        $this->recorded_payments =  $invoice->payments->count();
        $this->user_id = $invoice->user_id;
        $this->customer_id = $invoice->customer_id;
        $this->selectedCurrency = $invoice->currency_id;
        $this->footer = $invoice->footer;
        $this->selectedCustomer = $invoice->customer_id;
        $this->exchange_rate = $invoice->exchange_rate;
        $this->exchange_amount = $invoice->exchange_amount;
        $this->values = $invoice->invoicing_values;
        $this->fiscalize_invoice = $invoice->fiscalize;
        $this->purchase_order_number = $invoice->purchase_order_number;
        $this->sales_order_number = $invoice->sales_order_number;
        $this->pat_number = $invoice->pat_number;
        $this->from_inventory = $invoice->from_inventory;
        $this->from_trips = $invoice->from_trips;
        $this->memo = $invoice->memo;
        $this->date = $invoice->date;
        $this->expiry = $invoice->expiry;
        $this->company_id = $invoice->company_id;
        $this->invoice_id = $invoice->id;
        $this->invoice_number = $invoice->invoice_number;
        $this->discount = $invoice->discount;
        if(isset($this->discount)){
            $this->is_discount = True;   
            $this->discount_amount = $this->discount->amount;   
            $this->discount_description = $this->discount->description;   
            $this->discount_unit = $this->discount->unit;   
        }
    
        $this->invoice_items = $this->invoice->invoice_items;
    
        if (isset( $this->invoice_items)) {
           foreach($this->invoice_items as $item){
            $this->selectedCurrentProduct[] = $item->product_id;
            $this->selectedCurrentTrip[] = $item->trip_id;
            $this->old_inventory_id[] = $item->inventory_id;
            $this->selectedCurrentInventory[] = $item->inventory_id;
            $this->current_weight[] = $item->weight;
            $this->old_weight[] = $item->weight;
            $this->current_measurement[] = $item->measurement;
            $this->current_qty[] = $item->qty;
            $this->current_amount[] = $item->amount;
            if($item->description){
                $this->current_description[] = $item->description;
            }elseif($item->trip_details){
                $this->current_description[] = $item->trip_details;
            }
            
            $this->current_tax_rate[] = $item->tax_rate;
            $this->current_hs_code[] = $item->hs_code;
            $this->selectedCurrentTax[] = $item->tax_id;
           }
        }
    
        $invoice_bank_accounts = $invoice->bank_accounts;
        if (isset($invoice_bank_accounts)) {
            foreach ($invoice_bank_accounts as $account) {
                $this->bank_account_id[] = $account->id;
            }
        }
       
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::where('company_id',$this->company->id)->orderBy('name','asc')->get();
        $this->accounts = Account::where('account_type_id',1)->latest()->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->inventories = Inventory::with('product.brand')->where('status',1)->get()->sortBy('product.brand.name');
        $this->income_account_id = Account::where('name','Sales')->first()->id;
        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();

    }
    public function updated($value){
        $this->validateOnly($value);
    }
   
    public function invoiceDate(){
        if ($this->expiry == "") {
            $this->expiry  = $this->date;
        }
    }

    protected $rules = [
        'memo' => 'required',
        'subheading' => 'required',
        'expiry' => 'required',
        'date' => 'required',
        'footer' => 'required',
    ];

    public function update()
    {

        DB::transaction(function () {
            
        if ($this->invoice_id) {
            
            $invoice = Invoice::find($this->invoice_id);
            $invoice->company_id = $this->company_id;
            $invoice->bank_account_id = $this->bank_account_id;
            $invoice->currency_id = $this->selectedCurrency;
            if (isset($this->selectedCustomer)) {
                $invoice->customer_id = $this->selectedCustomer;
            }
            $invoice->invoice_number = $this->invoice_number;
            $invoice->date = $this->date;
            $invoice->fiscalize = $this->fiscalize_invoice;
            $invoice->expiry = $this->expiry;
            $invoice->invoicing_values = $this->values;
            $invoice->from_inventory = $this->from_inventory;
            $invoice->purchase_order_number = $this->purchase_order_number;
            $invoice->sales_order_number = $this->sales_order_number;
            $invoice->pat_number = $this->pat_number;
            $invoice->memo = $this->memo;
            $invoice->footer = $this->footer;
            $invoice->subheading = $this->subheading;
            $invoice->update();
            $invoice->bank_accounts()->detach();
            $invoice->bank_accounts()->sync($this->bank_account_id);

            if ($this->is_discount == True) {
                $discount = $invoice->discount;
                if (isset($discount)) {
                    $discount->invoice_id = $invoice->id;
                    $discount->name = 'Discount';
                    $discount->description = $this->discount_description;
                    $discount->unit = $this->discount_unit;
                    $discount->amount = $this->discount_amount;
                    $discount->update();
                }else{
                    $discount = new Discount;
                    $discount->invoice_id = $invoice->id;
                    $discount->name = 'Discount';
                    $discount->description = $this->discount_description;
                    $discount->unit = $this->discount_unit;
                    $discount->amount = $this->discount_amount;
                    $discount->save();
                }
               
            }


         if(isset($this->from_trips) && $this->from_trips == true){

            foreach($this->invoice_items as $key => $item){
                    
                $invoice_item = InvoiceItem::find($item->id);
                
                if (isset($this->selectedCurrentTrip[$key])) {
                    $invoice_item->trip_id = $this->selectedCurrentTrip[$key];
                }
                if (isset($this->selectedCurrentTax[$key])) {
                    $invoice_item->tax_id = $this->selectedCurrentTax[$key];
                }
                if (isset($this->selectedCurrentAccount[$key])) {
                    $invoice_item->account_id = $this->selectedCurrentAccount[$key];
                }
                if (isset($this->current_description[$key])) {
                    $invoice_item->description = $this->current_description[$key];
                }
                if (isset($this->current_tax_rate[$key])) {
                    $invoice_item->tax_rate = $this->current_tax_rate[$key];
                }
                if (isset($this->current_hs_code[$key])) {
                    $invoice_item->hs_code = $this->current_hs_code[$key];
                }
                if (isset($this->current_amount[$key])) {
                    $invoice_item->amount = $this->current_amount[$key];
                }
                if (isset($this->current_qty[$key])) {
                    $invoice_item->qty = $this->current_qty[$key];
                }
            
                if (is_numeric($this->current_amount[$key]) && is_numeric($this->current_qty[$key])) {
                    $current_item_subtotal = $this->current_amount[$key]*$this->current_qty[$key];
                    $invoice_item->subtotal = $current_item_subtotal;
                    $this->subtotal = $this->subtotal + $current_item_subtotal;
                }
                if ((isset($this->current_tax_rate) && is_numeric($this->current_tax_rate[$key])) && isset($this->selectedCurrentTax[$key])) {
                    $current_item_tax_amount = ($current_item_subtotal * ($this->current_tax_rate[$key] / 100 ));
                    $invoice_item->tax_amount =  $current_item_tax_amount;
                    $invoice_item->tax_rate =  $this->current_tax_rate[$key];
                    $current_item_subtotal_incl = $current_item_tax_amount + $current_item_subtotal ;
                    $invoice_item->subtotal_incl =  $current_item_subtotal_incl;
                    $this->tax_amount = $this->tax_amount + $current_item_tax_amount;
                    $this->total = $this->total +  $current_item_subtotal_incl ;
                    
                }else{
                    $current_item_subtotal_incl = $current_item_subtotal;
                    $invoice_item->subtotal_incl =  $current_item_subtotal_incl;
                    $this->total = $this->total +  $current_item_subtotal_incl;
                }

                if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                    $invoice_item->exchange_rate = $this->exchange_rate;
                    $invoice_item->exchange_amount = $this->exchange_rate * $current_item_subtotal_incl ;
                 }
        
                $invoice_item->update();
    
            
        
            }

          
    
            $invoice = Invoice::find($invoice->id);
            $invoice->tax_amount =  $this->tax_amount;
            $invoice->subtotal = $this->subtotal;
            $invoice->total = $this->total;
            $invoice->exchange_rate = $this->exchange_rate;
            $invoice->exchange_amount = $this->exchange_amount;          
            $invoice->update();

       
        if (isset($this->selectedTrip)) {
       
            foreach($this->selectedTrip as $key => $value){
               
                $invoice_item = new InvoiceItem;
                $invoice_item->invoice_id = $invoice->id;
    
                if (isset($this->selectedTrip[$key])) {
                    $invoice_item->trip_id = $this->selectedTrip[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $invoice_item->tax_id = $this->selectedTax[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $invoice_item->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->hs_code[$key])) {
                    $invoice_item->hs_code = $this->hs_code[$key];
                }
                if (isset($this->selectedAccount[$key])) {
                    $invoice_item->account_id = $this->selectedAccount[$key];
                }
                if (isset($this->description[$key])) {
                    $invoice_item->description = $this->description[$key];
                }
                if (isset($this->qty[$key])) {
                    $invoice_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $invoice_item->amount = $this->amount[$key];
                }
                if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {
                    $item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $invoice_item->subtotal = $item_subtotal;
                    $this->subtotal = $this->subtotal + $item_subtotal;
    
                }
                if ((isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) && isset($this->selectedTax[$key])) {
    
                    $item_tax_amount = ($item_subtotal * ($this->tax_rate[$key] / 100 ));
                    $invoice_item->tax_amount =  $item_tax_amount;
                    $this->tax_amount = $this->tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $invoice_item->subtotal_incl =  $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
    
                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $invoice_item->subtotal_incl = $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
                }

                if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                    $invoice_item->exchange_rate = $this->exchange_rate;
                    $invoice_item->exchange_amount = $this->exchange_rate * $item_subtotal_incl;
                 }
                $invoice_item->save();
      
            }
        }


        $discount = $invoice->discount;

        if (isset($discount)) {
            if($this->discount_unit == "currency"){
                $this->total = $this->total - $this->discount_amount;
            }elseif($this->discount_unit == "percentage"){
                if ((is_numeric($this->discount_amount) && $this->discount_amount > 0) && ( is_numeric($this->total) && $this->total > 0)) {
                    $discount_amount = ($this->discount_amount/100)*$this->total;
                    $this->total = $this->total - $discount_amount;
                }
               
            }
        }
    

        $invoice = Invoice::find($invoice->id);
        $invoice->tax_amount =  $this->tax_amount;
        $invoice->subtotal = $this->subtotal;
        $invoice->total = $this->total;
        $invoice->exchange_rate = $this->exchange_rate;
        $invoice->exchange_amount = $this->exchange_amount;
        
        $total_paid = $invoice->payments
        ->whereNotNull('amount')
        ->where('amount', '!=', '')
        ->sum('amount');

        $invoice->balance = (is_numeric($total_paid) && $total_paid > 0 && $this->total > $total_paid)
            ? $this->total - $total_paid
            : $this->total;

        $invoice->update();


        }elseif (isset($this->from_trips) && $this->from_trips == false) {
    
            if (isset($this->from_inventory) && $this->from_inventory == false) {

                foreach($this->invoice_items as $key => $item){
                    
                    $invoice_item = InvoiceItem::find($item->id);
                    
                    if (isset($this->selectedCurrentProduct[$key])) {
                        $invoice_item->product_id = $this->selectedCurrentProduct[$key];
                    }
                    if (isset($this->selectedCurrentTax[$key])) {
                        $invoice_item->tax_id = $this->selectedCurrentTax[$key];
                    }
                    if (isset($this->selectedCurrentAccount[$key])) {
                        $invoice_item->account_id = $this->selectedCurrentAccount[$key];
                    }
                    if (isset($this->current_description[$key])) {
                        $invoice_item->description = $this->current_description[$key];
                    }
                    if (isset($this->current_tax_rate[$key])) {
                        $invoice_item->tax_rate = $this->current_tax_rate[$key];
                    }
                    if (isset($this->current_hs_code[$key])) {
                        $invoice_item->hs_code = $this->current_hs_code[$key];
                    }
                    if (isset($this->current_amount[$key])) {
                        $invoice_item->amount = $this->current_amount[$key];
                    }
                    if (isset($this->current_qty[$key])) {
                        $invoice_item->qty = $this->current_qty[$key];
                    }
                
                    if (is_numeric($this->current_amount[$key]) && is_numeric($this->current_qty[$key])) {
                        $current_item_subtotal = $this->current_amount[$key]*$this->current_qty[$key];
                        $invoice_item->subtotal = $current_item_subtotal;
                        $this->subtotal = $this->subtotal + $current_item_subtotal;
                    }
                    if ((isset($this->current_tax_rate[$key]) && is_numeric($this->current_tax_rate[$key])) && isset($this->selectedCurrentTax[$key])) {
                        $current_item_tax_amount = ($current_item_subtotal * ($this->current_tax_rate[$key] / 100 ));
                        $invoice_item->tax_amount =  $current_item_tax_amount;
                        $invoice_item->tax_rate =  $this->current_tax_rate[$key];
                        $current_item_subtotal_incl = $current_item_tax_amount + $current_item_subtotal ;
                        $invoice_item->subtotal_incl =  $current_item_subtotal_incl;
                        $this->tax_amount = $this->tax_amount + $current_item_tax_amount;
                        $this->total = $this->total +  $current_item_subtotal_incl ;
                        
                    }else{
                        
                        $current_item_subtotal_incl = $current_item_subtotal;
                        $invoice_item->subtotal_incl =  $current_item_subtotal_incl;
                        $this->total = $this->total +  $current_item_subtotal_incl;
                    }

                    if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                        $invoice_item->exchange_rate = $this->exchange_rate;
                        $invoice_item->exchange_amount = $this->exchange_rate * $current_item_subtotal_incl ;
                     }
            
                    $invoice_item->update();
        
                
            
                }

               
        
                $invoice = Invoice::find($invoice->id);
                $invoice->tax_amount =  $this->tax_amount;
                $invoice->subtotal = $this->subtotal;
                $invoice->total = $this->total;
                $invoice->exchange_rate = $this->exchange_rate;
                $invoice->exchange_amount = $this->exchange_amount;
                $invoice->update();
    
           
            if (isset($this->selectedProduct)) {
           
                foreach($this->selectedProduct as $key => $value){
                   
                    $invoice_item = new InvoiceItem;
                    $invoice_item->invoice_id = $invoice->id;
        
                    if (isset($this->selectedProduct[$key])) {
                        $invoice_item->product_id = $this->selectedProduct[$key];
                    }
                    if (isset($this->selectedTax[$key])) {
                        $invoice_item->tax_id = $this->selectedTax[$key];
                    }
                    if (isset($this->tax_rate[$key])) {
                        $invoice_item->tax_rate = $this->tax_rate[$key];
                    }
                    if (isset($this->hs_code[$key])) {
                        $invoice_item->hs_code = $this->hs_code[$key];
                    }
                    if (isset($this->selectedAccount[$key])) {
                        $invoice_item->account_id = $this->selectedAccount[$key];
                    }
                    if (isset($this->description[$key])) {
                        $invoice_item->description = $this->description[$key];
                    }
                    if (isset($this->qty[$key])) {
                        $invoice_item->qty = $this->qty[$key];
                    }
                    if (isset($this->amount[$key])) {
                        $invoice_item->amount = $this->amount[$key];
                    }
                    if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {
    
                        $item_subtotal = $this->amount[$key]*$this->qty[$key];
                        $invoice_item->subtotal = $item_subtotal;
                        $this->subtotal = $this->subtotal + $item_subtotal;
        
                    }
                    if ((isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) && isset($this->selectedTax[$key])) {
        
                        $item_tax_amount = ($item_subtotal * ($this->tax_rate[$key] / 100 ));
                        $invoice_item->tax_amount =  $item_tax_amount;
                        $this->tax_amount = $this->tax_amount + $item_tax_amount;
                        $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                        $invoice_item->subtotal_incl =  $item_subtotal_incl;
                        $this->total =  $this->total + $item_subtotal_incl;
        
                    }else{
                        $item_subtotal_incl = $item_subtotal;
                        $invoice_item->subtotal_incl = $item_subtotal_incl;
                        $this->total =  $this->total + $item_subtotal_incl;
                    }

                    if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                        $invoice_item->exchange_rate = $this->exchange_rate;
                        $invoice_item->exchange_amount = $this->exchange_rate * $item_subtotal_incl ;
                     }
                    $invoice_item->save();
          
                }
            }
    

            $discount = $invoice->discount;

            if (isset($discount)) {
                if($this->discount_unit == "currency"){
                    $this->total = $this->total - $this->discount_amount;
                }elseif($this->discount_unit == "percentage"){
                    if ((is_numeric($this->discount_amount) && $this->discount_amount > 0) && ( is_numeric($this->total) && $this->total > 0)) {
                        $discount_amount = ($this->discount_amount/100)*$this->total;
                        $this->total = $this->total - $discount_amount;
                    }
                   
                }
            }
        

            $invoice = Invoice::find($invoice->id);
            $invoice->tax_amount =  $this->tax_amount;
            $invoice->subtotal = $this->subtotal;
            $invoice->total = $this->total;
            $invoice->exchange_rate = $this->exchange_rate;
            $invoice->exchange_amount = $this->exchange_amount;
            $total_paid = $invoice->payments
            ->whereNotNull('amount')
            ->where('amount', '!=', '')
            ->sum('amount');

            $invoice->balance = (is_numeric($total_paid) && $total_paid > 0 && $this->total > $total_paid)
                ? $this->total - $total_paid
                : $this->total;

                $invoice->update();
                


        }elseif (isset($this->from_inventory) && $this->from_inventory == true) {

            foreach($this->invoice_items as $key => $value){
                $invoice_item =  InvoiceItem::find($value->id);
                $invoice_item->invoice_id = $invoice->id;
                if($this->selectedCurrentInventory[$key]){
                    $inventory = Inventory::find($this->selectedCurrentInventory[$key]);
                    $product = $inventory->product;
                    $invoice_item->product_id = $product->id;
                    $invoice_item->inventory_id = $this->selectedCurrentInventory[$key];
                }

                if (isset($this->current_qty[$key])) {
                    $invoice_item->qty = $this->current_qty[$key];
                }
                if (isset($this->current_weight[$key])) {
                    $invoice_item->weight = $this->current_weight[$key];
                }
                if (isset($this->current_measurement[$key])) {
                    $invoice_item->measurement = $this->current_measurement[$key];
                }
                if (isset($this->current_tax_rate[$key])) {
                    $invoice_item->tax_rate = $this->current_tax_rate[$key];
                }
                if (isset($this->current_hs_code[$key])) {
                    $invoice_item->hs_code = $this->current_hs_code[$key];
                }
                if (isset($this->selectedCurrentTax[$key])) {
                    $invoice_item->tax_id = $this->selectedCurrentTax[$key];
                }
                if (isset($this->current_amount[$key])) {
                    $invoice_item->amount = $this->current_amount[$key];
                }
                if (isset($this->current_description[$key])) {
                    $invoice_item->description = $this->current_description[$key];
                }
                if ((isset($this->current_amount[$key]) && is_numeric($this->current_amount[$key])) && ( isset($this->current_qty[$key]) && is_numeric($this->current_qty[$key]) ) ) {

                    $item_subtotal = $this->current_amount[$key]*$this->current_qty[$key];
                    $invoice_item->subtotal = $item_subtotal;
                    $this->current_subtotal = $this->current_subtotal + $item_subtotal;
    
                }
                if (isset($this->current_tax_rate[$key]) && is_numeric($this->current_tax_rate[$key]) && isset($this->selectedCurrentTax[$key])) {
    
                    $item_tax_amount = ($item_subtotal * ($this->current_tax_rate[$key] / 100 ));
                    $invoice_item->tax_amount =  $item_tax_amount;
                    $this->tax_amount = $this->tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $invoice_item->subtotal_incl =  $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
    
                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $invoice_item->subtotal_incl = $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
                }
            
                $invoice_item->update();

                if ($this->old_inventory_id[$key] != $this->selectedCurrentInventory[$key]) {
                    $dispatch = new InventoryDispatch;
                    $dispatch->user_id = Auth::user()->id;
                    $dispatch->inventory_id = $this->selectedInventory[$key];
                    $dispatch->invoice_id = $invoice->id;
                    $dispatch->issue_date = $invoice->date;
                    $dispatch->weight = $this->current_weight[$key];
                    $dispatch->measurment = $this->current_measurement[$key];
                    $dispatch->save();
                }
              
        
                $inventory = Inventory::find($this->selectedInventory[$key]);
        
                if ((isset($inventory->balance) && is_numeric($inventory->balance) && $inventory->balance > 0) && (isset($this->weight[$key]) && is_numeric($this->weight[$key]) && $this->weight[$key] > 0)) {
                    if ($this->old_inventory_id[$key] == $this->selectedCurrentInventory[$key]) {
                       if ((isset($this->old_weight[$key]) && is_numeric($this->old_weight[$key]) && $this->old_weight[$key] > 0)) {
                        $inventory->balance = ($inventory->balance + $this->old_weight[$key]) - $this->weight[$key];
                        if ($inventory->balance <= 0) {
                            $inventory->status = 0;
                        }
                       }
                    }else{
                        $inventory->balance = $inventory->balance - $this->weight[$key];
                        if ($inventory->balance <= 0) {
                            $inventory->status = 0;
                        }
                    }
                }
                $inventory->update();

            }

            $invoice = Invoice::find($invoice->id);
            $invoice->tax_amount =  $this->tax_amount;
            $invoice->subtotal = $this->subtotal;
            $invoice->total = $this->total;
            $invoice->exchange_rate = $this->exchange_rate;
            $invoice->exchange_amount = $this->exchange_amount;
            $invoice->update();

            foreach($this->selectedInventory as $key => $value){
                $invoice_item = new InvoiceItem;
                $invoice_item->invoice_id = $invoice->id;
                if($this->selectedInventory[$key]){
                    $inventory = Inventory::find($this->selectedInventory[$key]);
                    $product = $inventory->product;
                    $invoice_item->product_id = $product->id;
                    $invoice_item->inventory_id = $this->selectedInventory[$key];
                }

                if (isset($this->qty[$key])) {
                    $invoice_item->qty = $this->qty[$key];
                }
                if (isset($this->weight[$key])) {
                    $invoice_item->weight = $this->weight[$key];
                }
                if (isset($this->measurement[$key])) {
                    $invoice_item->measurement = $this->measurement[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $invoice_item->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->hs_code[$key])) {
                    $invoice_item->hs_code = $this->hs_code[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $invoice_item->tax_id = $this->selectedTax[$key];
                }
                if (isset($this->amount[$key])) {
                    $invoice_item->amount = $this->amount[$key];
                }
                if (isset($this->description[$key])) {
                    $invoice_item->description = $this->description[$key];
                }
                if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {

                    $item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $invoice_item->subtotal = $item_subtotal;
                    $this->subtotal = $this->subtotal + $item_subtotal;
    
                }
                if ((isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) && isset($this->selectedTax[$key])) {
    
                    $item_tax_amount = ($item_subtotal * ($this->tax_rate[$key] / 100 ));
                    $invoice_item->tax_amount =  $item_tax_amount;
                    $this->tax_amount = $this->tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $invoice_item->subtotal_incl =  $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
    
                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $invoice_item->subtotal_incl = $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
                }
            
                $invoice_item->save();

            
                $dispatch = new InventoryDispatch;
                $dispatch->user_id = Auth::user()->id;
                $dispatch->inventory_id = $this->selectedInventory[$key];
                $dispatch->invoice_id = $invoice->id;
                $dispatch->issue_date = $invoice->date;
                $dispatch->weight = $this->weight[$key];
                $dispatch->measurment = $this->measurement[$key];
                $dispatch->save();
                
        
                $inventory = Inventory::find($this->selectedInventory[$key]);
        
                if ((isset($inventory->balance) && is_numeric($inventory->balance) && $inventory->balance > 0) && (isset($this->weight[$key]) && is_numeric($this->weight[$key]) && $this->weight[$key] > 0)) {
                    $inventory->balance = $inventory->balance - $this->weight[$key];
                    if ($inventory->balance <= 0) {
                        $inventory->status = 0;
                    }
                    $inventory->update();
                }

            }

            $discount = $invoice->discount;

            if (isset($discount)) {
                if($this->discount_unit == "currency"){
                    $this->total = $this->total - $this->discount_amount;
                }elseif($this->discount_unit == "percentage"){
                    if ((is_numeric($this->discount_amount) && $this->discount_amount > 0) && ( is_numeric($this->total) && $this->total > 0)) {
                        $discount_amount = ($this->discount_amount/100)*$this->total;
                        $this->total = $this->total - $discount_amount;
                    }
                   
                }
            }

            $invoice = Invoice::find($invoice->id);
            $invoice->tax_amount =  $this->tax_amount;
            $invoice->subtotal = $this->subtotal;
            $invoice->total = $this->total;
            $invoice->exchange_rate = $this->exchange_rate;
            $invoice->exchange_amount = $this->exchange_amount;
            $total_paid = $invoice->payments
            ->whereNotNull('amount')
            ->where('amount', '!=', '')
            ->sum('amount');

            $invoice->balance = (is_numeric($total_paid) && $total_paid > 0 && $this->total > $total_paid)
                ? $this->total - $total_paid
                : $this->total;

            $invoice->update();
        }
    }
    
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Updated Successfully!!"
            ]);
          
            return redirect()->route('invoices.index');

        }

    });
    }

    public function updatedSelectedCurrency($id){
        if (!is_null($id)) {
             $this->selected_currency = Currency::find($id);
             if($id != $this->company->currency_id){
                $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                    ->where('status', 1)
                    ->where('expiry', '>', Carbon::today())
                    ->first();
                if ($predefined_exchange_rate) {   
                    $this->exchange_rate = $predefined_exchange_rate->exchange_rate;
                }
            }
        }
    }

        public function getTripsProperty(){

            $query = Trip::query()
            ->with('customer:id,name','loading_point:id,name','offloading_point:id,name','currency')
            ->where('authorization','approved')
            ->where('trip_status','!=', 'Cancelled')
            ->where('currency_id', $this->selectedCurrency);

                 // Date window
            if ($this->from && $this->to ) {
                $from = Carbon::parse($this->from)->startOfDay();
                $to   = Carbon::parse($this->to)->endOfDay();
                $query->whereBetween($this->trip_filter, [$from, $to]);
            } else {
                $query->whereBetween($this->trip_filter, [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]);
            }

            if($this->selectedCustomer){
                $query->where('customer_id', $this->selectedCustomer);
            }

            if (filled($this->search)) {
            $term = '%'.$this->search.'%';

            $query->where(function ($q) use ($term) {
                $q->where('trip_number', 'like', $term)
                ->orWhere('start_date', 'like', $term)
                ->orWhere('turnover', 'like', $term)
                ->orWhere('freight', 'like', $term)
                ->orWhereHas('horse', function ($qq) use ($term) {
                return $qq->where('registration_number', 'like', $term)
                        ->where('fleet_number', 'like', $term);
                })
                ->orWhereHas('vehicle', function ($qq) use ($term)  {
                return $qq->where('registration_number', 'like', $term)
                            ->where('fleet_number', 'like', $term);
                })
                ->orWhereHas('trip_documents', function ($qq) use ($term) {
                return $qq->where('document_number', 'like', $term);
                });
            });
        }

         return $query->orderByDesc($this->trip_filter)->get();

    }
    
    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->invoice_amount) && $this->invoice_amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->invoice_amount;

        }

         $this->income_accounts = Account::whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
       
        $this->inventories = Inventory::with('product.brand')->where('status',1)->get()->sortBy('product.brand.name');
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::where('company_id',$this->company->id)->orderBy('name','asc')->get();
        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();

    
        $this->invoice_items = InvoiceItem::where('invoice_id',$this->invoice_id)->get();

        
        return view('livewire.invoices.edit',[
            'customers' => $this->customers,
            'currencies' => $this->currencies,
            'bank_accounts' => $this->bank_accounts,
            'products' => $this->products,
            'inventories' => $this->inventories,
            'trips' => $this->trips,
            'invoice_items' => $this->invoice_items,
        ]);
    }
}
