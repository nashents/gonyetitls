<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Tax;
use App\Models\Trip;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Count;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\BankAccount;
use App\Models\Destination;
use App\Models\InvoiceItem;
use App\Models\InvoiceTrip;
use App\Models\Measurement;
use App\Models\Denomination;
use App\Models\InvoiceCount;
use App\Models\Notification;
use App\Models\TripDocument;
use App\Models\InvoicePayment;
use App\Models\ProductService;
use App\Models\InventoryDispatch;
use App\Models\InventoryRequisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

    public $inventories;
    public $selectedInventory = [];


    public $fiscalize_invoice;
    public $reason;
    public $trip_filter;

    public $products;
    public $inventory_products;
    public $weight = [];
    public $measurement = [];
    public $selectedProduct = [];
    public $description = [];
    public $qty = [];
    public $amount = [];
    public $is_discount = False;
    public $invoices;
    public $invoice_number;
    public $number;
    public $invoice_id;
    public $initials;
    public $customers;
    public $trips;
    public $from_trips = false;
    public $exchange_rate;
    public $from_inventory = false;
    public $record_payment = false;
    public $exchange_amount;
    public $trip;
    public $invoice_trip;
    public $trip_id;
    public $selectedCustomer;
    public $bank_accounts;
    public $bank_account_id;
    public $company_id;
    public $currencies;
    public $destinations;
    public $selectedCurrency;
    public $selectedTrip = [];
    public $trip_sum = [];
    public $tax_rate = [];
    public $tax_amount;
    public $total_tax_amount;
    public $measurements;
    public $invoice_amount = 0;
    public $invoice_sub_amount = 0;
    public $turnover = 0;
    public $invoice_total_amount = 0;
    public $date;
    public $expiry;
    public $subheading;
    public $memo;
    public $values = "scheduled";
    public $footer;
    public $purchase_order_number;
    public $sales_order_number;
    public $pat_number;
    public $selectedAccount = [];
        //discount vars
    
        public $discount_name;
        public $discount_description;
        public $discount_unit;
        public $discount_amount;
    
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
        public $sell = True;
        public $buy = False;
    
        public $item_key;

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
    public $company;
       
    
    
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

    public function customerNumber(){
       
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

            $customer = Customer::orderBy('id', 'desc')->first();

        if (!$customer) {
            $customer_number =  $initials .'C'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $customer->id + 1;
            $customer_number =  $initials .'C'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $customer_number;


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

    public function generatePIN($digits = 4){
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while($i < $digits){
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return $pin;
    }

    public function storeCustomer()
    {

            $pin = $this->generatePIN();

            $user = new User;
            $user->name = $this->customer_name;
            $user->category = 'customer';
            $user->email = $this->email;
            $user->password = Hash::make($pin);
            $user->save();

            if (isset(Auth::user()->company)) {
                $company = Auth::user()->company;
            }elseif (isset(Auth::user()->employee->company)) {
                $company = Auth::user()->employee->company;
            }

            $customer = new Customer;
            $customer->customer_number = $this->customerNumber();
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

    public function updatedSelectedCustomer($id){

        $this->customer = Customer::find($id);
        $this->initials = $this->customer->initials;
        $this->invoice_number = $this->invoiceNumber();
     
    
    }

 

    public function invoiceNumber(){
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
            $invoice = Invoice::where('customer_id', $this->selectedCustomer)->orderBy('id', 'desc')->get()->first();
    
            if (!$invoice) {
                $this->number = 1;
                $invoice_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                if ($invoice->number) {
                    $this->number = $invoice->number + 1;
                }else{
                    $this->number = $invoice->id + 1;
                }
               
                $invoice_number = $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $invoice_number;
        }else {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $this->initials = $words[0][0].$words[1][0];
            }else {
                $this->initials = $words[0][0];
            }
            $invoice = Invoice::orderBy('id','desc')->first();
            if (!$invoice) {
                $this->number = 1;
                $invoice_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                $this->number = $invoice->id + 1;
                $invoice_number =  $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $invoice_number;
        }
    
    }

    
    public function mount(){

        $this->trip_filter = "created_at";
        $this->company = Auth::user()->employee->company;

        $this->accounts = Account::where('account_type_id',1)->latest()->get();

        $this->invoice_number = $this->invoiceNumber();
        $this->invoices = Invoice::latest()->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
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
     
        $this->bank_accounts = BankAccount::orderBy('name','asc')->get();
        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();


        $this->currencies = Currency::orderBy('name','asc')->get();
      
        $this->customers = Customer::orderBy('name','asc')->get();
        if (Auth::user()->employee->company) {
            $this->memo = Auth::user()->employee->company->invoice_memo;
            $this->footer = Auth::user()->employee->company->invoice_footer;
            $this->company_id = Auth::user()->employee->company->id;
            $this->fiscalize_invoice = Auth::user()->employee->company->fiscalize;
        }
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
                }
                
            }  
            
           
        }
    }

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate[$key] = $tax->rate;
            }else{
                $this->tax_rate[$key] = "";
            }
           
        }
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

   


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

        'trip_id.required' => 'Trip field is required',
        'bank_account_id.required' => 'Bank Account field is required',

    ];

    protected $rules = [
        'trip_id' => 'required',
        'invoice_number' => 'required',
        'memo' => 'required',
        'subheading' => 'required',
        'expiry' => 'required',
        'date' => 'required',
        'footer' => 'required',
        'item_name' => 'required',
        'customer_name' => 'required|unique:customers,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

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


 
  

    public function store(){

        $invoice = new Invoice;
        $invoice->user_id = Auth::user()->id;
        $invoice->company_id = $this->company_id;
        $invoice->currency_id = $this->selectedCurrency;
        $invoice->fiscalize = $this->fiscalize_invoice;
        $invoice->customer_id = $this->selectedCustomer;
        $invoice->invoice_number = $this->invoice_number;
        $invoice->number = $this->number;
        $invoice->account_id = $this->income_account_id;
        $invoice->date = $this->date;
        $invoice->expiry = $this->expiry;
        $invoice->invoicing_values = $this->values;
        $invoice->purchase_order_number = $this->purchase_order_number;
        $invoice->sales_order_number = $this->sales_order_number;
        $invoice->pat_number = $this->pat_number;
        $invoice->memo = $this->memo;
        $invoice->footer = $this->footer;
        $invoice->from_inventory = $this->from_inventory;
        $invoice->from_trips = $this->from_trips;
        $invoice->save();
        $invoice->bank_accounts()->sync($this->bank_account_id);
        $this->invoice_id = $invoice->id;

        $discount_account = Account::where('name','Sales Discounts')->first();

        if ($this->is_discount == True) {
            $discount = new Discount;
            $discount->user_id = Auth::user()->id;
            $discount->invoice_id = $invoice->id;
            $discount->account_id = $discount_account ? $discount_account->id : "";
            $discount->name = 'Discount';
            $discount->description = $this->discount_description;
            $discount->unit = $this->discount_unit;
            $discount->amount = $this->discount_amount;
            $discount->save();
        }

        if ($this->from_trips == true) {
     
            foreach($this->selectedTrip as $key => $value){
               
                $invoice_item = new InvoiceItem;
                $invoice_item->invoice_id = $invoice->id;
                if (isset($this->selectedAccount[$key])) {
                    $invoice_item->account_id = $this->selectedAccount[$key];
                }
                if (isset($this->selectedTrip[$key])) {
                    $invoice_item->trip_id = $this->selectedTrip[$key];
                }
                if (isset($this->qty[$key])) {
                    $invoice_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $invoice_item->amount = $this->amount[$key];
                }
                if (isset($this->description[$key])) {
                    $invoice_item->description = $this->description[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $invoice_item->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $invoice_item->tax_id = $this->selectedTax[$key];
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
            $invoice->balance = $this->total;
            $invoice->update();
        

     
       

    }elseif ($this->from_trips == false) {

        if (isset($this->from_inventory) && $this->from_inventory == false) {

            foreach($this->selectedProduct as $key => $value){
               
                $invoice_item = new InvoiceItem;
                $invoice_item->invoice_id = $invoice->id;
                if (isset($this->selectedAccount[$key])) {
                    $invoice_item->account_id = $this->selectedAccount[$key];
                }
                if (isset($this->selectedProduct[$key])) {
                    $invoice_item->product_id = $this->selectedProduct[$key];
                }
                if (isset($this->qty[$key])) {
                    $invoice_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $invoice_item->amount = $this->amount[$key];
                }
                if (isset($this->description[$key])) {
                    $invoice_item->description = $this->description[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $invoice_item->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $invoice_item->tax_id = $this->selectedTax[$key];
                }
                 if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {

                    $item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $invoice_item->subtotal = $item_subtotal;
                    $this->subtotal = $this->subtotal + $item_subtotal;

                }
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {

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
            $invoice->balance = $this->total;
            $invoice->update();

 

        }elseif (isset($this->from_inventory) && $this->from_inventory == true) {

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
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {
    
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
            $invoice->balance = $this->total;
            $invoice->update();


        }
       
    }

    $notifications = Notification::where('category','Invoice Authorization')->where('status',1)->get();
       
        
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                   $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $invoice));
                }
                }
            }
        }


        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Invoice Created Successfully!!"
        ]);

        return redirect()->route('invoices.index');

        
        //     }
        //     catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while creating invoice!!"
        //     ]);
        // }
    }

    public function invoiceDate(){
        if ($this->expiry == "") {
            $this->expiry  = $this->date;
        }
    }


    public function render()
    {

        $this->invoice_currency = $this->selectedCurrency;

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

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
        $this->bank_accounts = BankAccount::orderBy('name','asc')->get();
        $this->products = Product::where('sell',True)->orderBy('name','asc')->get();

        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                $this->trips = Trip::query()->with('customer:id,name','loading_point:id,name','offloading_point:id,name','currency')->where('authorization','approved')->whereBetween($this->trip_filter,[$this->from, $this->to] )
                            ->where('authorization', 'approved')
                            ->where('trip_status','!=', 'Cancelled')
                            ->where('trip_number', 'like', '%'.$this->search.'%')
                            ->orWhere('start_date', 'like', '%'.$this->search.'%')
                            ->orWhere('turnover', 'like', '%'.$this->search.'%')
                            ->orWhere('freight', 'like', '%'.$this->search.'%')
                            ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('trip_documents', function ($query) {
                            return $query->where('document_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                            })->orderby($this->trip_filter,'desc')->get();
            }else {
                $this->trips = Trip::query()->with('customer:id,name','loading_point:id,name','offloading_point:id,name','currency')->where('trip_status','!=', 'Cancelled')->where('authorization','approved')->whereBetween($this->trip_filter,[$this->from, $this->to] )->orderBy($this->trip_filter,'desc')->get();
               
            }
           
        }
        elseif (isset($this->search)) {
         
            $this->trips = Trip::query()->with('customer','loading_point','offloading_point','currency')
                ->where('authorization', 'approved')
                ->where('trip_status','!=', 'Cancelled')
                ->where('trip_number', 'like', '%'.$this->search.'%')
                ->orWhere('start_date', 'like', '%'.$this->search.'%')
                ->orWhere('turnover', 'like', '%'.$this->search.'%')
                ->orWhere('freight', 'like', '%'.$this->search.'%')
                ->orWhereHas('customer', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trip_documents', function ($query) {
                    return $query->where('document_number', 'like', '%'.$this->search.'%');
                    })
                ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                ->orWhereHas('currency', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
                })->orderby($this->trip_filter,'desc')->get();
        }
        else{
            $this->trips = Trip::query()->with('customer:id,name','loading_point:id,name','offloading_point:id,name','currency')->whereYear($this->trip_filter,date('Y'))->where('trip_status','!=', 'Cancelled')->where('authorization','approved')->orderBy($this->trip_filter,'desc')->get();
        }
       
           
        return view('livewire.invoices.create',[
            'trips' => $this->trips,
            'trip_id' => $this->trip_id,
            'invoice_amount' =>$this->invoice_amount,
            'customers' => $this->customers,
            'currencies' => $this->currencies,
            'bank_accounts' => $this->bank_accounts,
            'products' => $this->products,
            'inventories' => $this->inventories,
            'trips' => $this->trips,
        ]);


    }
}
