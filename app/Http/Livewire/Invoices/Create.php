<?php

namespace App\Http\Livewire\Invoices;

use Carbon\Carbon;
use App\Models\Tax;
use App\Models\Trip;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Count;
use App\Models\Account;
use App\Models\Booking;
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
use App\Models\Transporter;
use App\Models\Denomination;
use App\Models\ExchangeRate;
use App\Models\InvoiceCount;
use App\Models\Notification;
use App\Models\TripDocument;
use App\Models\InvoicePayment;
use App\Models\ProductService;
use App\Models\InventoryDispatch;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryRequisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    public $searchBooking;
    public $searchTrip;
    protected $queryString = ['searchTrip','searchBooking'];
    public $from;
    public $to;

    public $inventories;
    public $selectedInventory = [];

    public $fiscalize_invoice;
    public $reason;
    public $trip_filter;
    public $invoice_to = "Customer";
    public $booking_filter;

    public $products;
    public $inventory_products;
    public $weight = [];
    public $measurement = [];
    public array $is_custom_item = [];
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
    public $transporters;
    public $selectedTransporter;
    public $source = "Generic";
    public $from_trips = false;
    public $exchange_rate;
    public $from_inventory = false;
    public $from_bookings = false;
    public $record_payment = false;
    public $exchange_amount;
    public $trip;
    public $invoice_trip;
    public $trip_id;
    public $selectedBooking;
    public $selectedCustomer;
    public $bank_accounts;
    public $bank_account_id;
    public $company_id;
    public $currencies;
    public $destinations;
    public $selectedCurrency;
    public $selected_currency;
    public $selectedTrip = [];
    public $trip_sum = [];
    public $tax_rate = [];
    public $hs_code = [];
    public $tax_amount;
    public $total_tax_amount;
    public $measurements;
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
    public $base_currency;

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
        $this->is_custom_item[$i] = false;
        array_push($this->inputs ,$i);
    }

    public function remove($i, $value)
    {
      
        unset($this->inputs[$i]);
        unset($this->is_custom_item[$value]);
        unset($this->description[$value]);
        unset($this->qty[$value]);
        unset($this->amount[$value]);
        unset($this->selectedTax[$value]);
        unset($this->selectedProduct[$value]);
        unset($this->selectedTrip[$value]);
        unset($this->selectedInventory[$value]);
        
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

    public function updatedInvoiceTo($value){
        if(!is_null($value)){
            if($value == "Transporter"){
                $this->source = "Booking";
                $this->selectedCustomer = Null;
                $this->selectedCurrency = Auth::user()->employee->company->currency_id;
                $this->bank_accounts = BankAccount::where('currency_id',$this->selectedCurrency)->where('company_id',$this->company->id)->orderBy('name','asc')->get();
            }elseif($value == "Customer"){
                 $this->selectedTransporter = Null;
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

        $customer = Customer::find($id);
        $this->initials = $customer?->initials;
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

            if($this->invoice_to == "Customer"){
                 $invoice = Invoice::where('customer_id', $this->selectedCustomer)->orderBy('id', 'desc')->get()->first();
            }elseif($this->invoice_to == "Transporter"){
                 $invoice = Invoice::where('transporter_id', $this->selectedTransporter)->orderBy('id', 'desc')->get()->first();
            }
           

           
    
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
        $this->booking_filter = "created_at";
        $this->company = Auth::user()->employee->company;
        $this->base_currency = $this->company->currency;

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
     
        $this->bank_accounts = collect();
        $this->products = Product::where('sell',True)->where('status',True)->orderBy('name','asc')->get();


        $this->currencies = Currency::orderBy('name','asc')->get();
      
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
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
                $this->hs_code[$key] = $tax->rate;
            }else{
                $this->tax_rate[$key] = "";
                $this->hs_code[$key] = "";
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
                

                if($this->invoice_to == "Transporter"){
                    $this->description[$key] = $this->setTransporterDescription($id); 
                    $base_currency_id = $this->base_currency->id;
                    $this->amount[$key] = $trip->trip_expenses->sum(function ($expense) use ($base_currency_id) {
                        return $expense->currency_id == $base_currency_id
                            ? (float) $expense->amount
                            : (float) $expense->exchange_amount;
                    });
                }elseif($this->invoice_to == "Customer"){
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
                }
                
                $this->qty[$key] = 1; 
                $this->selectedAccount[$key] =  $this->income_account_id;
            }
           
            
        }
   
    }

     public function updatedSelectedBooking($id, $key){
      
        if (!is_null($id)) {
            
            $booking = Booking::find($id);
            $ticket = $booking?->ticket;

            if (!$booking || !$ticket) {
                return ;
            }
            $dispatches = $ticket?->dispatches;
            $this->amount[$key] = $dispatches?->sum('total');
            $this->description[$key] = $this->setBookingDescription($id); 
            $this->qty[$key] = 1; 
            $this->selectedAccount[$key] =  $this->income_account_id;
           
            
        }
   
    }


    public function setBookingDescription($id)
    {
        $booking = Booking::with([
            'ticket.dispatches.dispatch_items.inventory.product',
            'ticket.dispatches.dispatch_items.asset.product',
            'ticket.dispatches.dispatch_items.product',
            'ticket.dispatches.dispatch_items.tyre.product',
            'ticket.dispatches.dispatch_items.currency',
        ])->find($id);

        if (! $booking || ! $booking->ticket) {
            return '';
        }

        $parts = [];

        foreach ($booking->ticket->dispatches as $dispatch) {

            foreach ($dispatch->dispatch_items as $dispatch_item) {

                $name = null;

                if ($dispatch_item->inventory?->product) {
                    $name = $dispatch_item->inventory->product->name;
                } elseif ($dispatch_item->asset?->product) {
                    $name = $dispatch_item->asset->product->name;
                } elseif ($dispatch_item->product) {
                    $name = $dispatch_item->product->name;
                } elseif ($dispatch_item->tyre?->product) {
                    $name = $dispatch_item->tyre->product->name;
                }

                if (! $name) {
                    continue; // skip items without a proper name
                }

                $qty = $dispatch_item->qty ?? 0;

                $currencyName   = $dispatch_item->currency?->name   ?? '';
                $currencySymbol = $dispatch_item->currency?->symbol ?? '';
                $unit_cost          = number_format($dispatch_item->unit_cost ?? 0, 2);

                // Build: Name x Qty (CurrencyName CurrencySymbolTotal)
                $parts[] = "{$name} x {$qty} ({$currencyName} {$currencySymbol}{$unit_cost})";
            }
        }

        return implode(', ', $parts);
    }
   
    public function setTransporterDescription($id)
    {
        $trip = Trip::with([
            'trip_expenses'
        ])->find($id);

        if (! $trip || ! $trip->trip_expenses) {
            return '';
        }

        $parts = [];

        foreach ($trip->trip_expenses as $trip_expense) {

                $name = null;

                if ($trip_expense->allowance) {
                    $name = $trip_expense->allowance->name;
                } elseif ($trip_expense->expense) {
                    $name = $trip_expense->expense->name;
                } 

                if (! $name) {
                    continue; // skip items without a proper name
                }

                $defaultCurrencyName = $this->base_currency?->name ?? '';
                $defaultCurrencySymbol = $this->base_currency?->symbol ?? '';
                $currencyName   = $trip_expense->currency?->name   ?? '';
                $currencySymbol = $trip_expense->currency?->symbol ?? '';
                $amount = number_format($trip_expense->amount ?? 0, 2);
                $exchange_amount = number_format($trip_expense->exchange_amount ?? 0, 2);
                $exchange_rate = $trip_expense->exchange_rate;

                // Build: Name x Qty (CurrencyName CurrencySymbolTotal)
                if($trip_expense->exchange_amount && $trip_expense->exchange_rate){
                     $parts[] = "{$name} ({$currencyName} {$currencySymbol}{$amount} Exc at {$exchange_rate} {$defaultCurrencyName} {$defaultCurrencySymbol}{$exchange_amount} ) ";
                }else{
                    $parts[] = "{$name} ({$currencyName} {$currencySymbol}{$amount}) ";
                }
               
       
        }

         // Registration number (horse first, then vehicle)
        $regNumber = $trip->horse?->registration_number
            ?? $trip->vehicle?->registration_number
            ?? '';

        // Origin / destination
        $originCity      = Destination::find($trip->from)?->city ?? '';
        $destinationCity = Destination::find($trip->to)?->city ?? '';

        $lp = $trip->loading_point?->name ?? '';
        $op = $trip->offloading_point?->name ?? '';

        $from = trim("{$originCity} {$lp}");
        $to   = trim("{$destinationCity} {$op}");

        $route = $from && $to
            ? "{$from} to {$to}"
            : trim($from . ' ' . $to);

        $trip_details = $route ." (".$regNumber.") : ";

        return $trip_details . " ".implode(', ', $parts);
    }
  
    

    public function setDescription($id)
    {
        $trip = Trip::find($id);

        if (! $trip) {
            return '';
        }

        $cargo      = $trip->cargo;
        $cargoName  = $cargo?->name ?? '';
        $cargoType  = $cargo?->type ?? null;
        $measurement = $trip->measurement ?? '';

        // Weight with unit
        $weightText = $trip->weight
            ? $trip->weight . ' tons'
            : '';

        // Quantity / litreage text
        $quantityText = ($trip->quantity && $measurement)
            ? $trip->quantity . ' ' . $measurement
            : '';

        $litreageText = ($trip->litreage_at_20 && $measurement)
            ? $trip->litreage_at_20 . ' ' . $measurement
            : '';

        // Cargo description based on type
        if ($cargoType === 'Solid') {
            $cargoDescription = trim("{$cargoName} {$weightText} {$quantityText}");
        } elseif ($cargoType === 'Liquid') {
            $cargoDescription = trim("{$cargoName} {$weightText} {$litreageText}");
        } else {
            $cargoDescription = '';
        }

        // Registration number (horse first, then vehicle)
        $regNumber = $trip->horse?->registration_number
            ?? $trip->vehicle?->registration_number
            ?? '';

        // Origin / destination
        $originCity      = Destination::find($trip->from)?->city ?? '';
        $destinationCity = Destination::find($trip->to)?->city ?? '';

        $lp = $trip->loading_point?->name ?? '';
        $op = $trip->offloading_point?->name ?? '';

        $from = trim("{$originCity} {$lp}");
        $to   = trim("{$destinationCity} {$op}");

        $route = $from && $to
            ? "{$from} to {$to}"
            : trim($from . ' ' . $to);

        // Freight calculation: formula + variables
        $symbol    = $trip->currency?->symbol ?? '';
        $formula   = '';
        $variables = '';

        if ($trip->freight_calculation) {
            switch ($trip->freight_calculation) {
                case 'flat_rate':
                    $formula   = 'R';
                    $variables = $symbol . $trip->rate;
                    break;

                case 'rate_weight':
                    if ($cargoType === 'Solid') {
                        $formula   = 'R*W';
                        $variables = $symbol . $trip->rate . '*' . $trip->weight;
                    } elseif ($cargoType === 'Liquid') {
                        $formula   = 'R*L';
                        $variables = $symbol . $trip->rate . '*' . $trip->litreage_at_20;
                    }
                    break;

                case 'rate_distance':
                    $formula   = 'R*D';
                    $variables = $symbol . $trip->rate . '*' . $trip->distance;
                    break;

                case 'rate_weight_distance':
                    if ($cargoType === 'Solid') {
                        $formula   = 'R*W*D';
                        $variables = $symbol . $trip->rate . '*' . $trip->weight . '*' . $trip->distance;
                    } elseif ($cargoType === 'Liquid') {
                        $formula   = 'R*L*D';
                        $variables = $symbol . $trip->rate . '*' . $trip->litreage_at_20 . '*' . $trip->distance;
                    }
                    break;
            }
        }

        $formulaBlock = trim(
            ($formula ? $formula : '') .
            ($formula && $variables ? ' ' : '') .
            ($variables ?: '')
        );

        // POD document
        $document   = TripDocument::where('trip_id', $trip->id)
                        ->where('title', 'POD')
                        ->first();
        $podNumber  = $document?->document_number ?? '';

        // Build the final description from meaningful chunks
        $segments = array_filter([
            $cargoDescription,
            $formulaBlock,
            $route,
            $regNumber,
            $podNumber,
        ]);

        return implode(' ', $segments);
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

        DB::transaction(function () {

                $invoice = new Invoice;
                $invoice->user_id = Auth::user()->id;
                $invoice->company_id = $this->company_id ?? Null;
                $invoice->currency_id = $this->selectedCurrency ?? Null;
                $invoice->fiscalize = $this->fiscalize_invoice;
                $invoice->customer_id = $this->selectedCustomer ?? Null;
                $invoice->transporter_id = $this->selectedTransporter ?? Null;
                $invoice->invoice_number = $this->invoice_number;
                $invoice->number = $this->number;
                $invoice->invoice_to = $this->invoice_to;
                $invoice->account_id = $this->income_account_id;
                $invoice->date = $this->date;
                $invoice->expiry = $this->expiry;
                $invoice->invoicing_values = $this->values;
                $invoice->purchase_order_number = $this->purchase_order_number;
                $invoice->sales_order_number = $this->sales_order_number;
                $invoice->pat_number = $this->pat_number;
                $invoice->source = $this->source;
                $invoice->memo = $this->memo;
                $invoice->footer = $this->footer;
                $invoice->from_inventory = $this->from_inventory;
                $invoice->from_trips = $this->from_trips;
                $invoice->save();
                
                $validAccounts = BankAccount::whereIn('id', (array) $this->bank_account_id)->pluck('id')->toArray();
                if (!empty($validAccounts)) {
                    $invoice->bank_accounts()->sync($validAccounts);
                }
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

            if ($this->source == "Trip") {
            
                    foreach($this->qty as $key => $value){

                            $invoice_item = new InvoiceItem;
                            $invoice_item->invoice_id = $invoice->id;
                            if (isset($this->selectedAccount[$key])) {
                                $invoice_item->account_id = $this->selectedAccount[$key];
                            }
                            if (isset($this->selectedTrip[$key])) {
                                $invoice_item->trip_id = $this->selectedTrip[$key];
                            }
                            if (isset($this->selectedProduct[$key])) {
                                $invoice_item->product_id = $this->selectedProduct[$key];
                            }
                            if (isset($this->is_custom_item[$key])) {
                                $invoice_item->is_custom_item = $this->is_custom_item[$key];
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
                            if (isset($this->hs_code[$key])) {
                                $invoice_item->hs_code = $this->hs_code[$key];
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
                    
            }elseif ($this->source == "Booking") {
            
                    foreach($this->selectedBooking as $key => $value){

                            $invoice_item = new InvoiceItem;
                            $invoice_item->invoice_id = $invoice->id;
                            if (isset($this->selectedAccount[$key])) {
                                $invoice_item->account_id = $this->selectedAccount[$key];
                            }
                            if (isset($this->selectedBooking[$key])) {
                                $invoice_item->booking_id = $this->selectedBooking[$key];
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
                            if (isset($this->hs_code[$key])) {
                                $invoice_item->hs_code = $this->hs_code[$key];
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
                    
            }
            elseif ($this->source == "Generic") {

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
                    if (isset($this->hs_code[$key])) {
                        $invoice_item->hs_code = $this->hs_code[$key];
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

            }elseif ($this->source == "Inventory") {

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

           
        
            $notifications = Notification::where('when','before')->where('category','Invoice Authorization')->where('status',1)->get();

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

        });
    
    }

    public function invoiceDate(){
        if ($this->expiry == "") {
            $this->expiry  = $this->date;
        }
    }

    public function updatedSelectedCurrency($id){
        if (!is_null($id)) {
            $this->selected_currency = Currency::find($id);
            $this->invoice_currency = $this->selectedCurrency;
            $this->bank_accounts = BankAccount::where('currency_id',$id)->where('company_id',$this->company->id)->orderBy('name','asc')->get();
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

      public function refresh($category){

        if($category == "customers"){

            $this->customers = Customer::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customers Refreshed Successfully!!."
            ]);

        }
        elseif($category == "transporters"){

            $this->transporters = Transporter::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporters Refreshed Successfully!!."
            ]);

        }
        elseif($category == "products"){

            $this->products = Product::where('sell',True)->where('status',True)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Products & Services Refreshed Successfully!!."
            ]);

        }
        elseif($category == "bank_accounts"){
            $this->bank_accounts = BankAccount::where('currency_id',$this->selectedCurrency)->where('company_id',$this->company->id)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bank Accounts Refreshed Successfully!!."
            ]);
        }
    }

    public function getTripsProperty(){

            $query = Trip::query()
            ->with('transporter:id,name','customer:id,name','loading_point:id,name','offloading_point:id,name','currency')
            ->where('authorization','approved')
            ->where('trip_status','!=', 'Cancelled')
            ->when($this->invoice_to === 'Customer', function ($q) {
                $q->where('currency_id', $this->selectedCurrency);
            });

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
            if($this->selectedTransporter){
                $query->where('transporter_id', $this->selectedTransporter);
            }

            if (filled($this->searchTrip)) {
            $term = '%'.$this->searchTrip.'%';

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

    public function updatedSource($value){
            if(!$value){
                return;
            }
            if($value == "Trip"){
                $this->from_trips = True;
            }elseif($value == "Inventory"){
                 $this->from_inventory = True;
            }
    }
 
    public function getBookingsProperty(){

            $query = Booking::query()
            ->with('transporter','horse','trailer','vehicle','asset','service_type','ticket')
            ->where('authorization','approved')
            ->where('transaction_type','income')
            ->where('status', 0);
            if($this->invoice_to == "Transporter"){
                $query->where('transporter_id', $this->selectedTransporter);
            }

                 // Date window
            if ($this->from && $this->to ) {
                $from = Carbon::parse($this->from)->startOfDay();
                $to   = Carbon::parse($this->to)->endOfDay();
                $query->whereBetween($this->booking_filter, [$from, $to]);
            } else {
                $query->whereBetween($this->booking_filter, [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]);
            }

           

            if (filled($this->searchBooking)) {
            $term = '%'.$this->searchBooking.'%';

            $query->where(function ($q) use ($term) {
                $q->where('booking_number', 'like', $term)
                ->orWhere('in_date', 'like', $term)
                ->orWhere('turnover', 'like', $term)
                ->orWhere('freight', 'like', $term)
                ->orWhereHas('horse', function ($qq) use ($term) {
                return $qq->where('registration_number', 'like', $term)
                        ->where('fleet_number', 'like', $term);
                })
                ->orWhereHas('trailer', function ($qq) use ($term) {
                return $qq->where('registration_number', 'like', $term)
                        ->where('fleet_number', 'like', $term);
                })
                ->orWhereHas('vehicle', function ($qq) use ($term)  {
                return $qq->where('registration_number', 'like', $term)
                            ->where('fleet_number', 'like', $term);
                });
            });
        }

         return $query->orderByDesc($this->booking_filter)->get();

    }

   


    public function render()
    {

      

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }

        return view('livewire.invoices.create',[
            'trips' => $this->trips,
            'bookings' => $this->bookings,
        ]);


    }
}
