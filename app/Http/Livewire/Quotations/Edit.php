<?php

namespace App\Http\Livewire\Quotations;

use Carbon\Carbon;
use App\Models\Cargo;
use App\Models\Account;
use App\Models\Product;
use Livewire\Component;
use App\Models\CostItem;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Quotation;
use App\Models\BankAccount;
use App\Models\Destination;
use App\Models\ExchangeRate;
use App\Models\LoadingPoint;
use App\Models\QuotationItem;
use App\Models\AdditionalCost;
use App\Models\OffloadingPoint;
use App\Models\QuotationProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{


    public $quotation_items;
    public $quotations;
    public $quotation_number;
    public $custom_ref;
    public $quotation_id;
    public $customers;
    public $selectedCustomer;
    public $company_id;
    public $company;
    public $bank_accounts;
    public $bank_account_id;
    public $initials;
    public $currencies;
    public $currency_id;
    public $selectedCurrency;
    public $selected_currency;
    public $date;
    public $expiry;
    public $memo;
    public $footer;
    public $subtotal = 0;
    public $total = 0;
    public $tax_amount = 0; 
    public $exchange_rate;
    public $exchange_amount;

    public $loading_points;
    public $loading_point_id;
    public $offloading_points;
    public $offloading_point_id;
    public $cargos;
    public $selectedCargo;
    public $destinations;
    public $from;
    public $cargo_type;
    public $for_trips;
    public $to;
    public $weight;

    public $products;
    public $selectedProduct = [] ;
    public $selectedAccount = [];
    public $selectedTax = [];
    public $qty = [];
    public $amount = [];
    public $description = [];
    public $tax_rate;
    public $hs_code;

        //discount vars
        public $discount_name;
        public $is_discount;
        public $discount_description;
        public $discount_unit;
        public $discount_amount;

        public $item_key;
        public $item_status;


    public $currentSelectedTax;
    public $currentSelectedProduct;
    public $current_qty;
    public $current_amount;
    public $current_description;
    public $current_tax_rate;
    public $current_hs_code;
    public $current_tax_id;

    public $currentSelectedCargo;
    public $current_from;
    public $current_to;
    public $current_loading_point_id;
    public $current_offloading_point_id;
    public $current_weight;

    public $item_name;
    public $item_description;
    public $sell_price;
    public $buy_price;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $sell = True;
    public $buy = False;



    //customer vars
    public $customer_name;
    public $phonenumber;
    public $worknumber;
    public $email;
    public $country;
    public $tin_number;
    public $vat_number;
    public $city;
    public $suburb;
    public $street_address;

    public $quotation;
    public $discount;
    public $accounts;

    public $quotation_item;

  

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

    public $cost_items;
    public $current_additional_costs;
    public $current_cost_item_id = [];
    public $current_cost_total = [];
    public $cost_item_id = [];
    public $cost_total = [];

    public $customer;


    public $user_id;
    public $item_subtotal = 0;
    public $subtotal_incl = 0;
  
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

    public $cost_inputs = [];
    public $c = 1;
    public $d = 1;

    public function addCost($c)
    {
        $c = $c + 1;
        $this->c = $c;
        array_push($this->cost_inputs ,$c);
    }

    public function removeCost($c)
    {
        unset($this->cost_inputs[$c]);
    }




    public function updatedSelectedCustomer($id){
        $this->selectedCustomer = $id;
        $this->customer = Customer::find($this->selectedCustomer);
        $this->selectedCurrency = $this->customer->currency ? $this->customer->currency->id  : NULL;
    }

    public function updatedSelectedCargo($id){
        if (!is_null($id)) {
            $this->cargo_type = Cargo::find($id)->type;
        }
       
    
    }


    public function refresh($category){

        if($category == "customers"){
            $this->customers = Customer::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customers Refreshed Successfully!!."
            ]);
        }
        elseif($category == "destinations"){
            $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }
         elseif($category == 'loading_points'){
            $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loading Points Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'offloading_points'){
            $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Offloading Points Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'cargos'){
            $this->cargos = Cargo::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargos Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'products'){
            $this->products = Product::where('sell',True)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Products Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'taxes'){
             $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Sales Taxes Refreshed Successfully!!."
            ]);
        }
    }


    public function updatedSelectedCurrency($id){
        if(!is_null($id)){
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

    public function mount($quotation){
        $this->company = Auth::user()->employee->company;
         $this->cost_items = CostItem::orderBy('name','asc')->get();
        $this->quotation = $quotation;
        $this->for_trips = $this->quotation->for_trips;
        $this->user_id = $this->quotation->user_id;
        $this->selectedCustomer = $this->quotation->customer_id;
        $this->selectedCurrency = $this->quotation->currency_id;
        $this->company_id = $this->quotation->company_id;
        $this->quotation_number = $this->quotation->quotation_number;
        $this->custom_ref = $this->quotation->custom_ref;
        $this->footer = $this->quotation->footer;
        $this->memo = $this->quotation->memo;
        $this->date = $this->quotation->date;
        $this->expiry = $this->quotation->expiry;
        $this->quotation_id = $this->quotation->id;

        $this->discount = $this->quotation->discount;
        $this->for_trips = $this->quotation->for_trips;
        if(isset($this->discount)){
            $this->is_discount = True;   
            $this->discount_amount = $this->discount->amount;   
            $this->discount_description = $this->discount->description;   
            $this->discount_unit = $this->discount->unit;   
        }
       
        $this->current_additional_costs = $this->quotation->additional_costs;
        if(isset($this->current_additional_costs)){
            foreach($this->current_additional_costs as $cost){
                $this->current_cost_item_id[] = $cost->cost_item_id;
                $this->current_cost_total[] = $cost->total;
            }
        }

        $this->quotation_items = $this->quotation->quotation_items;

        if (isset( $this->quotation_items)) {
           foreach($this->quotation_items as $item){
            $this->currentSelectedProduct[] = $item->product_id;
            $this->current_qty[] = $item->qty;
            $this->current_description[] = $item->description;
            $this->current_amount[] = $item->amount;
            $this->current_tax_rate[] = $item->tax_rate;
            $this->current_hs_code[] = $item->hs_code;
            $this->currentSelectedTax[] = $item->tax_id;

            $this->currentSelectedCargo[] = $item->cargo_id;
            $this->current_loading_point_id[] = $item->loading_point_id;
            $this->current_offloading_point_id[] = $item->offloading_point_id;
            $this->current_from[] = $item->origin;
            $this->current_to[] = $item->destination;
            $this->current_weight[] = $item->weight;
           }
        }

        $quotation_bank_accounts = $quotation->bank_accounts;
        if (isset($quotation_bank_accounts)) {
            foreach ($quotation_bank_accounts as $account) {
                $this->bank_account_id[] = $account->id;
            }
        }
       
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::where('company_id',$this->company->id)->orderBy('name','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->accounts = Account::where('account_type_id',1)->latest()->get();

        $this->income_accounts = Account::whereHas('account_type.account_type_group', function($q){
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

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[
        'rate.*.required' => 'Rate field is required',
    ];

    protected $rules = [
        'selectedCustomer' => 'required',
        'quotation_number' => 'required',
        'date' => 'required',
        'selectedCurrency' => 'required',

    ];

    private function resetInputFields(){
        $this->item_name = '';
        $this->item_description = '';
        $this->sell_price = '';
        $this->buy_price = '';
        $this->tax_id = '';
        $this->income_account_id = '';
        $this->expense_account_id = '';
        $this->sell = True;
        $this->buy = False;

        //bank vars
        $this->branch = "" ;
        $this->bank_currency_id = "" ;
        $this->branch_code = "";
        $this->swift_code = "";
        $this->bank_name = "";
        $this->account_number = "";
        $this->account_name = "";
        $this->status = "";
        
        //customer vars
        $this->customer_name = "" ;
        $this->phonenumber = "" ;
        $this->worknumber = "";
        $this->email = "";
        $this->country = "";
        $this->tin_number = "";
        $this->vat_number = "";
        $this->city = "";
        $this->suburb = "";
        $this->street_address = "";
        $this->currency_id = "";
    }


    public function updatedCurrentSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->sell_price) {
                    $this->current_amount[$key] = $product->sell_price;
                }
                $this->current_description[$key] = $product->description;
                $this->current_qty[$key] = 1;
                if ($product->tax_id) {
                    $this->currentSelectedTax[$key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->current_tax_rate[$key] = $tax->rate;
                        $this->current_hs_code[$key] = $tax->hs_code;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedCurrentSelectedTax($id, $key){
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
    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->sell_price) {
                    $this->amount[$key] = $product->sell_price;
                }
                $this->description[$key] = $product->description;
                $this->qty[$key] = 1;

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

    public function showItem($status, $key){
        $this->item_key = $key;
        $this->item_status = $status;
        $this->dispatchBrowserEvent('show-product_serviceModal');
    }

    public function storeItem(){
        try{
        
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

            if($this->item_status == "current"){
                $this->selectedCurrentProduct[$this->item_key] = $product->id;
                $this->selectedCurrentAccount[$this->item_key] = $product->account_id;
                if (isset($product)) {
                    if ($product->sell_price) {
                        $this->current_amount[$this->item_key] = $product->sell_price;
                    }
                    $this->current_qty[$this->item_key] = 1;
                    $this->current_description[$this->item_key] = $product->description;
                    if ($product->tax_id) {
                        $this->selectedCurrentTax[$this->item_key] = $product->tax_id;
                        $tax = Account::find($product->tax_id);
                        if (isset($tax)) {
                            $this->current_tax_rate[$this->item_key] = $tax->rate;
                            $this->current_hs_code[$this->item_key] = $tax->hs_code;
                        }
                        
                    }  
                }
            }elseif($this->item_status == "new"){
                $this->selectedProduct[$this->item_key] = $product->id;
                $this->selectedAccount[$this->item_key] = $product->account_id;
                if (isset($product)) {
                    if ($product->sell_price) {
                        $this->amount[$this->item_key] = $product->sell_price;
                    }
                    $this->qty[$this->item_key] = 1;
                    $this->description[$this->item_key] = $product->description;
                    if ($product->tax_id) {
                        $this->selectedTax[$this->item_key] = $product->tax_id;
                        $tax = Account::find($product->tax_id);
                        if (isset($tax)) {
                            $this->tax_rate[$this->item_key] = $tax->rate;
                            $this->hs_code[$this->item_key] = $tax->hs_code;
                        }
                        
                    }  
                }
            }
            
    
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

    public function storeBankAccount(){
        try{
            $bank_account = new BankAccount;
            $bank_account->user_id = Auth::user()->id;
            $bank_account->company_id = $this->company_id;
            $bank_account->name = $this->bank_name;
            $bank_account->currency_id = $this->bank_currency_id;
            $bank_account->type = $this->type;
            $bank_account->account_number = $this->account_number;
            $bank_account->account_name = $this->account_name;
            $bank_account->branch = $this->branch;
            $bank_account->branch_code = $this->branch_code;
            $bank_account->swift_code = $this->swift_code;
            $bank_account->status = 1;
            $bank_account->save();

            $this->bank_account_id[] = $bank_account->id;
           
            $this->dispatchBrowserEvent('hide-bank_accountModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bank Account(s) Uploaded Successfully!!"
            ]);
        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating bank account(s)!!"
            ]);
        }
    }

    public function storeCustomer()
    {
            $customer = new Customer;
            $customer->name = $this->customer_name;
            $customer->phonenumber = $this->phonenumber;
            $customer->vat_number = $this->vat_number;
            $customer->tin_number = $this->tin_number;
            $customer->email = $this->email;
            $customer->currency_id = $this->currency_id;
            $customer->country = $this->country;
            $customer->city = $this->city;
            $customer->suburb = $this->suburb;
            $customer->street_address = $this->street_address;
            $customer->save();

            $this->selectedCustomer = $customer->id;

            $this->dispatchBrowserEvent('hide-customerModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customer Created Successfully!!"
            ]);
         

        
    }

    public function discount(){
        $this->is_discount = True;
    }
    public function removeDiscount($id){
        $quotation = Quotation::find($id);
        $discount = $quotation->discount;
        if($discount){
            $discount->delete();
        }
        
        $this->is_discount = False;
        $this->discount_amount = "";
        $this->discount_description = "";
        $this->discount_unit = "";
    }


    public function update(){

         DB::transaction(function () {

        $quotation = Quotation::find($this->quotation_id);
        $quotation->company_id = $this->company_id;
        $quotation->customer_id = $this->selectedCustomer;
        $quotation->custom_ref = $this->custom_ref;
        $quotation->currency_id = $this->selectedCurrency;
        $quotation->bank_account_id = $this->bank_account_id;
        $quotation->date = $this->date;
        $quotation->expiry = $this->expiry;
        $quotation->memo = $this->memo;
        $quotation->footer = $this->footer;
        $quotation->for_trips = $this->for_trips;
        $quotation->update();
        $quotation->bank_accounts()->detach();
        $quotation->bank_accounts()->sync($this->bank_account_id);

        if($this->cost_item_id){
            foreach($this->cost_item_id as $index => $cost_item_id){
                $cost = new AdditionalCost;
                $cost->quotation_id = $this->quotation_id;
                $cost->cost_item_id = $cost_item_id;
                $cost->total = isset($this->cost_total[$index]) ? $this->cost_total[$index] : 0;
                $cost->save();
            }
        }
        if($this->current_cost_item_id){
            foreach($this->current_cost_item_id as $index => $cost_item_id){
                $cost =  AdditionalCost::where('quotation_id', $this->quotation_id)
                        ->where('cost_item_id', $cost_item_id)
                        ->first();
                $cost->quotation_id = $this->quotation_id;
                $cost->cost_item_id = $cost_item_id;
                $cost->total = isset($this->current_cost_total[$index]) ? $this->current_cost_total[$index] : 0;
                $cost->update();
            }
        }

        if ($this->is_discount == True) {
            $discount = $quotation->discount;
            if (isset($discount)) {
                $discount->quotation_id = $quotation->id;
                $discount->name = 'Discount';
                $discount->description = $this->discount_description;
                $discount->unit = $this->discount_unit;
                $discount->amount = $this->discount_amount;
                $discount->update();
            }else{
                $discount = new Discount;
                $discount->quotation_id = $quotation->id;
                $discount->name = 'Discount';
                $discount->description = $this->discount_description;
                $discount->unit = $this->discount_unit;
                $discount->amount = $this->discount_amount;
                $discount->save();
            }
           
        }

        if (isset($this->for_trips) && $this->for_trips == True) {
            # code...
        foreach($this->quotation_items as $key => $item){
               
            $quotation_item = QuotationItem::find($item->id);
            
            if (isset($this->currentSelectedCargo[$key])) {
                $quotation_item->cargo_id = $this->currentSelectedCargo[$key];
                $cargo = Cargo::find($this->currentSelectedCargo[$key]);
                $cargo_name = $cargo->name ? $cargo->name: "";
              }
              if (isset($this->current_to[$key])) {
                $quotation_item->destination = $this->current_to[$key];
                $to = Destination::find($this->current_to[$key]);
                $destination_country = $to->country ?  $to->country->name : "";
                $destination = $destination_country .' '. $to->city;
              }else {
                $destination = "";
              }
              if (isset($this->current_from[$key])) {
                $quotation_item->origin = $this->current_from[$key];
                $from = Destination::find($this->current_from[$key]);
                $origin_country = $from->country ?  $from->country->name : "";
                $origin = $origin_country .' '. $from->city;

              }else {
                $origin = "";
              }
              
              if (isset($this->current_loading_point_id[$key])) {
                $quotation_item->loading_point_id = $this->current_loading_point_id[$key];
                $loading_point = LoadingPoint::find($this->current_loading_point_id[$key]);
                $loading_point_name = $loading_point ? $loading_point->name : "";
              }else {
                $loading_point_name = "";
              }
              if (isset($this->current_offloading_point_id[$key])) {
                $quotation_item->offloading_point_id = $this->current_offloading_point_id[$key];
                $offloading_point = OffloadingPoint::find($this->current_offloading_point_id[$key]);
                $offloading_point_name = $offloading_point ? $offloading_point->name : "";
              }else {
                $offloading_point_name = "";
              }
              if (isset($this->current_weight[$key])) {
                $quotation_item->weight = $this->current_weight[$key];
                $weight = $this->current_weight[$key]."Tons";
            }else {
                $weight = "";
            }

              $description = $cargo_name. " " .$weight.",". " from " .$origin." ".$loading_point_name. " to " .$destination." ".$offloading_point_name.".";
              $quotation_item->description = $description;

            if (isset($this->currentSelectedTax[$key])) {
                $quotation_item->tax_id = $this->currentSelectedTax[$key];
            }
           
            if (isset($this->current_tax_rate[$key])) {
                $quotation_item->tax_rate = $this->current_tax_rate[$key];
            }
            if (isset($this->current_hs_code[$key])) {
                $quotation_item->hs_code = $this->current_hs_code[$key];
            }
            if (isset($this->current_amount[$key])) {
                $quotation_item->amount = $this->current_amount[$key];
            }
            if (isset($this->current_qty[$key])) {
                $quotation_item->qty = $this->current_qty[$key];
            }
         
            if (is_numeric($this->current_amount[$key]) && is_numeric($this->current_qty[$key])) {
                $current_item_subtotal = $this->current_amount[$key]*$this->current_qty[$key];
                $quotation_item->subtotal = $current_item_subtotal;
                $this->subtotal = $this->subtotal + $current_item_subtotal;
            }
            if (is_numeric($this->current_tax_rate[$key])) {
                $current_item_tax_amount = ($current_item_subtotal * ($this->current_tax_rate[$key] / 100 ));
                $quotation_item->tax_amount =  $current_item_tax_amount;
                $quotation_item->tax_rate =  $this->current_tax_rate[$key];
                $current_item_subtotal_incl = $current_item_tax_amount + $current_item_subtotal ;
                $quotation_item->subtotal_incl =  $current_item_subtotal_incl;
                $this->tax_amount = $this->tax_amount + $current_item_tax_amount;
                $this->total = $this->total +  $current_item_subtotal_incl ;
                
            }else{
                $current_item_subtotal_incl = $current_item_subtotal;
                $quotation_item->subtotal_incl =  $current_item_subtotal_incl;
                $this->total = $this->total +  $current_item_subtotal_incl;
            }
    
            if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                $quotation_item->exchange_rate = $this->exchange_rate;
                $quotation_item->exchange_amount = $this->exchange_rate * $current_item_subtotal_incl ;
             }
            $quotation_item->update();

           
       
        }

        $quotation = Quotation::find($quotation->id);
        $quotation->tax_amount =  $this->tax_amount;
        $quotation->subtotal = $this->subtotal;
        $quotation->total = $this->total;
        $quotation->exchange_rate = $this->exchange_rate;
        $quotation->exchange_amount = $this->exchange_amount;
        $quotation->update();


        if (isset($this->selectedCargo )) {
           
            foreach ($this->selectedCargo as $key => $value) {

                $quotation_item = new QuotationItem;
                $quotation_item->quotation_id =  $this->quotation_id;

                if (isset($this->selectedCargo[$key])) {
                    $quotation_item->cargo_id = $this->selectedCargo[$key];
                    $cargo = Cargo::find($this->selectedCargo[$key]);
                    $cargo_name = $cargo->name ? $cargo->name: "";
                  }
                  if (isset($this->to[$key])) {
                    $quotation_item->destination = $this->to[$key];
                    $to = Destination::find($this->to[$key]);
                    $destination_country = $to->country ?  $to->country->name : "";
                    $destination = $destination_country .' '. $to->city;
                  }else {
                    $destination = "";
                  }
                  if (isset($this->from[$key])) {
                    $quotation_item->origin = $this->from[$key];
                    $from = Destination::find($this->from[$key]);
                    $origin_country = $from->country ?  $from->country->name : "";
                    $origin = $origin_country .' '. $from->city;
    
                  }else {
                    $origin = "";
                  }
                  
                  if (isset($this->loading_point_id[$key])) {
                    $quotation_item->loading_point_id = $this->loading_point_id[$key];
                    $loading_point = LoadingPoint::find($this->loading_point_id[$key]);
                    $loading_point_name = $loading_point ? $loading_point->name : "";
                  }else {
                    $loading_point_name = "";
                  }
                  if (isset($this->offloading_point_id[$key])) {
                    $quotation_item->offloading_point_id = $this->offloading_point_id[$key];
                    $offloading_point = OffloadingPoint::find($this->offloading_point_id[$key]);
                    $offloading_point_name = $offloading_point ? $offloading_point->name : "";
                  }else {
                    $offloading_point_name = "";
                  }
                  if (isset($this->weight[$key])) {
                    $quotation_item->weight = $this->weight[$key];
                    $weight = $this->weight[$key]."Tons";
                }else {
                    $weight = "";
                }
    
                  $description = $cargo_name. " " .$weight.",". " from " .$origin." ".$loading_point_name. " to " .$destination." ".$offloading_point_name.".";
                  $quotation_item->description = $description;
    
    
                if (isset($this->qty[$key])) {
                    $quotation_item->qty = $this->qty[$key];
                }

                if (isset($this->amount[$key])) {
                    $quotation_item->amount = $this->amount[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $quotation_item->tax_id = $this->selectedTax[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $quotation_item->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->hs_code[$key])) {
                    $quotation_item->hs_code = $this->hs_code[$key];
                }
               
                if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {

                    $item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $quotation_item->subtotal = $item_subtotal;
                    $this->subtotal = $this->subtotal + $item_subtotal;
    
                }
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {
    
                    $item_tax_amount = ($item_subtotal * ($this->tax_rate[$key] / 100 ));
                    $quotation_item->tax_amount =  $item_tax_amount;
                    $this->tax_amount = $this->tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $quotation_item->subtotal_incl =  $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
    
                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $quotation_item->subtotal_incl = $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
                }
    
                if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                    $quotation_item->exchange_rate = $this->exchange_rate;
                    $quotation_item->exchange_amount = $this->exchange_rate * $item_subtotal_incl ;
                 }
            
              
                $quotation_item->save();
    
              
            }

       
    
        }

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

         if($this->cost_total){
            $total_cost = collect($this->cost_total)->sum();
            if(is_numeric($total_cost)){
                $this->total = $this->total + $total_cost;
            }
        }

        $quotation = Quotation::find($quotation->id);
        $quotation->tax_amount =  $this->tax_amount;
        $quotation->subtotal = $this->subtotal;
        $quotation->total = $this->total;
        $quotation->exchange_rate = $this->exchange_rate;
        $quotation->exchange_amount = $this->exchange_amount;
        $quotation->update();


    }elseif(isset($this->for_trips) && $this->for_trips == False){
      
        foreach($this->quotation_items as $key => $item){
                    
            $quotation_item = quotationItem::find($item->id);
            
            if (isset($this->selectedCurrentProduct[$key])) {
                $quotation_item->product_id = $this->selectedCurrentProduct[$key];
            }
            if (isset($this->selectedCurrentTax[$key])) {
                $quotation_item->tax_id = $this->selectedCurrentTax[$key];
            }
            if (isset($this->selectedCurrentAccount[$key])) {
                $quotation_item->account_id = $this->selectedCurrentAccount[$key];
            }
            if (isset($this->current_description[$key])) {
                $quotation_item->description = $this->current_description[$key];
            }
            if (isset($this->current_tax_rate[$key])) {
                $quotation_item->tax_rate = $this->current_tax_rate[$key];
            }
            if (isset($this->current_hs_code[$key])) {
                $quotation_item->hs_code = $this->current_hs_code[$key];
            }
            if (isset($this->current_amount[$key])) {
                $quotation_item->amount = $this->current_amount[$key];
            }
            if (isset($this->current_qty[$key])) {
                $quotation_item->qty = $this->current_qty[$key];
            }
        
            if (is_numeric($this->current_amount[$key]) && is_numeric($this->current_qty[$key])) {
                $current_item_subtotal = $this->current_amount[$key]*$this->current_qty[$key];
                $quotation_item->subtotal = $current_item_subtotal;
                $this->subtotal = $this->subtotal + $current_item_subtotal;
            }
            if (is_numeric($this->current_tax_rate[$key])) {
                $current_item_tax_amount = ($current_item_subtotal * ($this->current_tax_rate[$key] / 100 ));
                $quotation_item->tax_amount =  $current_item_tax_amount;
                $quotation_item->tax_rate =  $this->current_tax_rate[$key];
                $current_item_subtotal_incl = $current_item_tax_amount + $current_item_subtotal ;
                $quotation_item->subtotal_incl =  $current_item_subtotal_incl;
                $this->tax_amount = $this->tax_amount + $current_item_tax_amount;
                $this->total = $this->total +  $current_item_subtotal_incl ;
                
            }else{
                
                $current_item_subtotal_incl = $current_item_subtotal;
                $quotation_item->subtotal_incl =  $current_item_subtotal_incl;
                $this->total = $this->total +  $current_item_subtotal_incl;
            }

            if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                $quotation_item->exchange_rate = $this->exchange_rate;
                $quotation_item->exchange_amount = $this->exchange_rate * $current_item_subtotal_incl ;
             }
    
            $quotation_item->update();

        
    
        }

       
        

        $quotation = Quotation::find($quotation->id);
        $quotation->tax_amount =  $this->tax_amount;
        $quotation->subtotal = $this->subtotal;
        $quotation->total = $this->total;
        $quotation->exchange_rate = $this->exchange_rate;
        $quotation->exchange_amount = $this->exchange_amount;
        $quotation->update();
       
        if (isset($this->selectedProduct)) {
       
            foreach($this->selectedProduct as $key => $value){
               
                $quotation_item = new QuotationItem;
                $quotation_item->quotation_id = $quotation->id;
    
                if (isset($this->selectedProduct[$key])) {
                    $quotation_item->product_id = $this->selectedProduct[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $quotation_item->tax_id = $this->selectedTax[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $quotation_item->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->hs_code[$key])) {
                    $quotation_item->hs_code = $this->hs_code[$key];
                }
                if (isset($this->description[$key])) {
                    $quotation_item->description = $this->description[$key];
                }
                if (isset($this->qty[$key])) {
                    $quotation_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $quotation_item->amount = $this->amount[$key];
                }
                if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {

                    $item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $quotation_item->subtotal = $item_subtotal;
                    $this->subtotal = $this->subtotal + $item_subtotal;
    
                }
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {
    
                    $item_tax_amount = ($item_subtotal * ($this->tax_rate[$key] / 100 ));
                    $quotation_item->tax_amount =  $item_tax_amount;
                    $this->tax_amount = $this->tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $quotation_item->subtotal_incl =  $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
    
                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $quotation_item->subtotal_incl = $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
                }

                if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                    $quotation_item->exchange_rate = $this->exchange_rate;
                    $quotation_item->exchange_amount = $this->exchange_rate * $item_subtotal_incl ;
                 }
                $quotation_item->save();
    
               
            }
        }

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

         if($this->cost_total){
            $total_cost = collect($this->cost_total)->sum();
            if(is_numeric($total_cost)){
                $this->total = $this->total + $total_cost;
            }
        }

        $quotation = Quotation::find($quotation->id);
        $quotation->tax_amount =  $this->tax_amount;
        $quotation->subtotal = $this->subtotal;
        $quotation->total = $this->total;
        $quotation->exchange_rate = $this->exchange_rate;
        $quotation->exchange_amount = $this->exchange_amount;
        $quotation->update();

    }
        
    
        Session::flash('success','Quotation Updated Successfully!!');
        return redirect()->route('quotations.index');

    } );
    }


    public function removeShow($id){

        $this->quotation_item = QuotationItem::find($id);
        $this->tax_amount = $this->quotation->tax_amount;
        $this->total = $this->quotation->total;
        $this->subtotal = $this->quotation->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }
    
    public function removeAdditionalCostShow($id){

        $cost = AdditionalCost::find($id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeQuotationItem(){ 

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

        return redirect(request()->header('Referer'));
   

    }

    public function render()
    {

        if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))  &&  ( isset($this->total) && is_numeric($this->total))) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }
        $this->cost_items = CostItem::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::where('company_id',$this->company->id)->orderBy('name','asc')->get();
        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        
        $this->income_accounts = Account::whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        return view('livewire.quotations.edit',[
            'customers' => $this->customers,
            'currencies' => $this->currencies,
            'bank_accounts' => $this->bank_accounts,
            'products' => $this->products,
            'cargos' => $this->cargos,
            'loading_points' => $this->loading_points,
            'offloading_points' => $this->offloading_points,
            'destinations' => $this->destinations,
        ]);



    }
}
