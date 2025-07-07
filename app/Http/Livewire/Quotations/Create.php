<?php

namespace App\Http\Livewire\Quotations;


use Carbon\Carbon;
use App\Models\Cargo;
use App\Models\Account;
use App\Models\Product;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\BankAccount;
use App\Models\Destination;
use App\Models\ExchangeRate;
use App\Models\LoadingPoint;
use App\Models\QuotationItem;
use App\Models\OffloadingPoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Create extends Component
{


    public $quotations;
    public $quotation_number;
    public $quotation_id;
    public $customers;
    public $selectedCustomer;
    public $company_id;
    public $company;
    public $bank_accounts;
    public $bank_account_id;

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

    public $initials;
    public $currencies;
    public $selectedCurrency;
    public $currency_id;
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

    public $products;
    public $selectedProduct = [] ;
    public $selectedAccount = [];
    public $selectedTax = [];
    public $qty = [];
    public $amount = [];
    public $description = [];
    public $tax_rate;
    public $hs_code;
  

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

    public $item_key;

        //discount vars
        public $discount_name;
        public $is_discount;
        public $discount_description;
        public $unit;
        public $discount_amount;

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

    

public function updatedSelectedCustomer($id){
    $this->selectedCustomer = $id;
    $this->customer = Customer::find($id);
    $this->initials = $this->customer->initials;
    $this->quotation_number = $this->quotationNumber();
    $this->selectedCurrency = $this->customer->currency ? $this->customer->currency->id  : NULL;
}

public function updatedSelectedCargo($id){
    if (!is_null($id)) {
        $this->cargo_type = Cargo::find($id)->type;
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

public function quotationNumber(){
    
    if (Auth::user()->employee->company->quotation_serialize_by_customer == TRUE) {

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

        $quotation = Quotation::where('customer_id', $this->selectedCustomer)->orderBy('id', 'desc')->get()->first();

        if (!$quotation) {
            $this->number = 1;
            $quotation_number =  $this->initials .'Q'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            if ($quotation->number) {
                $this->number = $quotation->number + 1;
            }else{
                $this->number = $quotation->id + 1;
            }
           
            $quotation_number =  $this->initials .'Q'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $quotation_number;
    }else {

        $str = Auth::user()->employee->company->name;
        $words = explode(' ', $str);
        if (isset($words[1][0])) {
            $this->initials = $words[0][0].$words[1][0];
        }else {
            $this->initials = $words[0][0];
        }

        $quotation = Quotation::orderBy('id','desc')->first();
        if (!$quotation) {
            $this->number = 1;
            $quotation_number =  $this->initials .'Q'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $this->number = $quotation->id + 1;
            $quotation_number =  $this->initials .'Q'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $quotation_number;
    }

}
    public function mount(){
         $this->company = Auth::user()->employee->company;
        $this->for_trips = False;
        $this->quotation_number = $this->quotationNumber();
        $this->bank_accounts = BankAccount::where('company_id',$this->company->id)->orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
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


        $this->currencies = Currency::orderBy('name','asc')->get();
     
        
        if (Auth::user()->employee->company) {
            $this->memo = Auth::user()->employee->company->quotation_memo;
            $this->footer = Auth::user()->employee->company->quotation_footer;
            $this->vat = Auth::user()->employee->company->vat;
            $this->company_id = Auth::user()->employee->company->id;
        }elseif (Auth::user()->company) {
            $this->memo = Auth::user()->company->quotation_memo;
            $this->footer = Auth::user()->company->quotation_footer;
            $this->vat = Auth::user()->company->vat;
            $this->company_id = Auth::user()->company->id;
        }

    }

    public function quotationDate(){
        if ($this->expiry == "") {
            $this->expiry  = $this->date;
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

        'bank_account_id.required' => 'Bank Account field is required',
        
    ];

    protected $rules = [
        'selectedCustomer' => 'required',
        'quotation_number' => 'required',
        'bank_account_id' => 'required',
        'date' => 'required',
        'selectedCurrency' => 'required',
    ];

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

    public function showItem($key){
        $this->item_key = $key;
        $this->income_account_id = Account::where('name','Sales')->first()->id;
        $this->dispatchBrowserEvent('show-product_serviceModal');
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

        public function discount(){
            $this->is_discount = True;
        }
        public function removeDiscount(){
            $this->is_discount = False;
            $this->discount_amount = "";
            $this->discount_description = "";
            $this->discount_unit = "";
        }

    public function store(){

        $quotation = new Quotation;
        $quotation->user_id = Auth::user()->id;
        $quotation->company_id = $this->company_id;
        $quotation->customer_id = $this->selectedCustomer;
        $quotation->currency_id = $this->selectedCurrency;
        $quotation->quotation_number = $this->quotation_number;
        $quotation->number = $this->number;
        $quotation->date = $this->date;
        $quotation->expiry = $this->expiry;
        $quotation->memo = $this->memo;
        $quotation->footer = $this->footer;
        $quotation->for_trips = $this->for_trips;
        $quotation->save();
        $quotation->bank_accounts()->sync($this->bank_account_id);

      
        if ($this->is_discount == True) {
            $discount_account = Account::where('name','Sales Discounts')->first();
            $discount = new Discount;
            $discount->user_id = Auth::user()->id;
            $discount->quotation_id = $quotation->id;
            $discount->account_id = $discount_account->id;
            $discount->name = 'Discount';
            $discount->description = $this->discount_description;
            $discount->unit = $this->discount_unit;
            $discount->amount = $this->discount_amount;
            $discount->save();
        }

        $this->quotation_id = $quotation->id;

        if (isset($this->for_trips) && $this->for_trips == True) {
           

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
        
                $quotation = Quotation::find($quotation->id);
                $quotation->tax_amount =  $this->tax_amount;
                $quotation->subtotal = $this->subtotal;
                $quotation->total = $this->total;
                $quotation->exchange_rate = $this->exchange_rate;
                $quotation->exchange_amount = $this->exchange_amount;
                $quotation->update();
        
            }

           


        }else{

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
            if (isset($this->qty[$key])) {
                $quotation_item->qty = $this->qty[$key];
            }
            if (isset($this->description[$key])) {
                $quotation_item->description = $this->description[$key];
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

        $quotation = Quotation::find($quotation->id);
        $quotation->tax_amount =  $this->tax_amount;
        $quotation->subtotal = $this->subtotal;
        $quotation->total = $this->total;
        $quotation->exchange_rate = $this->exchange_rate;
        $quotation->exchange_amount = $this->exchange_amount;
        $quotation->update();

    }

       
       

        Session::flash('success','Quotation Created Successfully!!');

        $this->dispatchBrowserEvent('hide-quotationModal');

        return redirect()->route('quotations.index');
    }





    public function render()
    {


        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();

        if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))  &&  ( isset($this->total) && is_numeric($this->total))) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }

           $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();

        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::where('company_id',$this->company->id)->orderBy('name','asc')->get();

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
            return view('livewire.quotations.create',[
                'customers' => $this->customers,
                'currencies' => $this->currencies,
                'bank_accounts' => $this->bank_accounts,
            ]);
    }
}
