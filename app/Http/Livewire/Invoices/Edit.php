<?php

namespace App\Http\Livewire\Invoices;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Rental;
use App\Models\Account;
use App\Models\Booking;
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
use App\Models\Transporter;
use App\Models\ExchangeRate;
use App\Models\TripDocument;
use App\Models\InventoryDispatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{


    public $searchBooking;
    public $searchTrip;
    public $searchRental;
    protected $queryString = ['searchTrip','searchBooking','searchRental'];
    public $from;
    public $to;

    public $inventories;
    public $selectedInventory = [];
    public $selectedTrip = [];

    public $fiscalize_invoice;
    public $base_currency;
    public $reason;
    public $trip_filter;
    public $booking_filter;
    public $rental_filter;
    public $selectedBooking = [];
    public $selectedRental= [];

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
    public $transporters;
    public $selectedTransporter;

        //discount vars
        public $discount_name;
        public $is_discount;
        public $discount_description;
        public $discount_unit;
        public $discount_amount;

    
    public $selectedCurrentTax;
    public $selectedCurrentProduct;
    public $selectedCurrentTrip;
    public $selectedCurrentBooking;
    public $selectedCurrentRental;
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
    public $source;
    public $recorded_payments;
    public $exchange_rate;
    public $from_inventory;
    public $from_bookings;
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
    public $invoice_to;
   
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
        unset($this->selectedRental[$value]);
        unset($this->selectedBooking[$value]);
        unset($this->selectedInventory[$value]);
        
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

    public function storeItem(){
        
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
    
        }

         public function updatedSelectedRental($id, $key){
      
        if (!is_null($id)) {
            
            $rental = Rental::find($id);
          
            if (isset($rental)) {
                $this->amount[$key] = $rental->rate_amount ; 
                $this->description[$key] = $this->setRentalDescription($id); 
                $this->qty[$key] = 1; 
                $this->selectedAccount[$key] =  $this->income_account_id;
            }
           
            
        }
   
    }
         public function updatedSelectedCurrentRental($id, $key){
      
        if (!is_null($id)) {
            
            $rental = Rental::find($id);
          
            if (isset($rental)) {
                $this->current_amount[$key] = $rental->rate_amount ; 
                $this->current_description[$key] = $this->setRentalDescription($id); 
                $this->current_qty[$key] = 1; 
                $this->selectedCurrentAccount[$key] =  $this->income_account_id;
            }
           
            
        }
   
    }

    public function updatedSelectedTrip($id, $key){
      
        if (!is_null($id)) {
            
            $trip = Trip::find($id);
            $delivery_note = $trip?->delivery_note;

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
                    $this->amount[$key] = $trip->trip_expenses->where('category','Transporter')->sum(function ($expense) use ($base_currency_id) {
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
    public function updatedSelectedCurrentTrip($id, $key){
      
        if (!is_null($id)) {
            
            $trip = Trip::find($id);
            $delivery_note = $trip?->delivery_note;
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
                    $this->current_description[$key] = $this->setTransporterDescription($id); 
                    $base_currency_id = $this->base_currency->id;
                    $this->current_amount[$key] = $trip->trip_expenses->sum(function ($expense) use ($base_currency_id) {
                        return $expense->currency_id == $base_currency_id
                            ? (float) $expense->amount
                            : (float) $expense->exchange_amount;
                    });
                }elseif($this->invoice_to == "Customer"){
                    if (isset($this->total_customer_expenses) && $this->total_customer_expenses > 0) {
                    if($this->values == "scheduled"){
                        $this->current_amount[$key] = $trip->freight + $this->total_customer_expenses; 
                    }elseif($this->values == "loading"){
                        $this->current_amount[$key] = $delivery_note->loaded_freight + $this->total_customer_expenses; 
                    }elseif($this->values == "offloading"){
                        $this->current_amount[$key] =  $delivery_note->offloaded_freight + $this->total_customer_expenses; 
                    }
                 
                }else{
                    if($this->values == "scheduled"){
                        $this->current_amount[$key] = $trip->freight ; 
                    }elseif($this->values == "loading"){
                        $this->current_amount[$key] = $delivery_note->loaded_freight ; 
                    }elseif($this->values == "offloading"){
                        $this->current_amount[$key] = $delivery_note->offloaded_freight ; 
                    }
                   
                }
                    $this->current_description[$key] = $this->setDescription($id); 
                }
                $this->current_qty[$key] = 1; 
                $this->selectedCurrentAccount[$key] =  $this->income_account_id;
            }
           
            
        }

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
        $this->base_currency = $this->company->currency;
        $this->trip_filter = "created_at";
        $this->booking_filter = "created_at";
        $this->rental_filter = "created_at";
        $this->invoice_id = $invoice->id;
        $this->invoice = $invoice;
        $this->recorded_payments =  $invoice->payments->count();
        $this->user_id = $invoice->user_id;
      
        $this->selectedTransporter = $invoice->transporter_id;
        $this->selectedCurrency = $invoice->currency_id;
        $this->footer = $invoice->footer;
        $this->invoice_to = $invoice->invoice_to;
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
        $this->source = $invoice->source;
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
            $this->selectedCurrentRental[] = $item->rental_id;
            $this->selectedCurrentBooking[] = $item->booking_id;
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
        $this->transporters = Transporter::orderBy('name','asc')->get();
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
       public function updatedSelectedCurrentBooking($id, $key){
      
        if (!is_null($id)) {
            
            $booking = Booking::find($id);
            $ticket = $booking?->ticket;

            if (!$booking || !$ticket) {
                return ;
            }
            $dispatches = $ticket?->dispatches;
            $this->current_amount[$key] = $dispatches?->sum('total');
            $this->current_description[$key] = $this->setBookingDescription($id); 
            $this->current_qty[$key] = 1; 
            $this->selectedCurrentAccount[$key] =  $this->income_account_id;
           
            
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
                $invoice->customer_id = $this->selectedCustomer ?? Null;
                $invoice->transporter_id = $this->selectedTransporter ?? Null;
                $invoice->invoice_number = $this->invoice_number;
                $invoice->date = $this->date;
                $invoice->fiscalize = $this->fiscalize_invoice;
                $invoice->expiry = $this->expiry;
                $invoice->source = $this->source;
                $invoice->invoicing_values = $this->values;
                $invoice->from_inventory = $this->from_inventory;
                $invoice->purchase_order_number = $this->purchase_order_number;
                $invoice->sales_order_number = $this->sales_order_number;
                $invoice->pat_number = $this->pat_number;
                $invoice->memo = $this->memo;
                $invoice->footer = $this->footer;
                $invoice->subheading = $this->subheading;
                $invoice->update();
                $validAccounts = BankAccount::whereIn('id', (array) $this->bank_account_id)->pluck('id')->toArray();

                if (!empty($validAccounts)) {
                    $invoice->bank_accounts()->sync($validAccounts);
                }
            
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

                if($this->source == "Trip"){

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
            
                    foreach($this->selectedTrip as $key => $value){
                    
                        $invoice_item = new InvoiceItem;
                        $invoice_item->invoice_id = $invoice->id;
            
                        if (isset($this->selectedTrip[$key])) {
                            $invoice_item->trip_id = $this->selectedTrip[$key];
                        }
                        if (isset($this->selectedProduct[$key])) {
                            $invoice_item->product_id = $this->selectedProduct[$key];
                        }
                        if (isset($this->is_custom_item[$key])) {
                            $invoice_item->is_custom_item = $this->is_custom_item[$key];
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
                elseif($this->source == "Rental"){

                    foreach($this->invoice_items as $key => $item){
                            
                        $invoice_item = InvoiceItem::find($item->id);
                        
                        if (isset($this->selectedCurrentRental[$key])) {
                            $invoice_item->rental_id = $this->selectedCurrentRental[$key];
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
            
                    foreach($this->selectedRental as $key => $value){
                    
                        $invoice_item = new InvoiceItem;
                        $invoice_item->invoice_id = $invoice->id;
            
                        if (isset($this->selectedRental[$key])) {
                            $invoice_item->rental_id = $this->selectedRental[$key];
                        }
                        if (isset($this->selectedProduct[$key])) {
                            $invoice_item->product_id = $this->selectedProduct[$key];
                        }
                        if (isset($this->is_custom_item[$key])) {
                            $invoice_item->is_custom_item = $this->is_custom_item[$key];
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
                
                elseif ($this->source == "Booking") {

                 foreach($this->invoice_items as $key => $item){
                            
                        $invoice_item = InvoiceItem::find($item->id);
                        
                        if (isset($this->selectedCurrentBooking[$key])) {
                            $invoice_item->rental_id = $this->selectedCurrentBooking[$key];
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

                }elseif ($this->source == "Inventory") {

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

                }else{
                
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

    public function getRentalsProperty(){

            $query = Rental::query()
            ->with('transporter','customer','driver','vehicle')
            ->whereIn('status',['Active','Reserved']);
                 // Date window
            if ($this->from && $this->to ) {
                $from = Carbon::parse($this->from)->startOfDay();
                $to   = Carbon::parse($this->to)->endOfDay();
                $query->whereBetween($this->rental_filter, [$from, $to]);
            } else {
                $query->whereBetween($this->rental_filter, [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]);
            }

            if (filled($this->searchRental)) {
            $term = '%'.$this->searchRental.'%';

            $query->where(function ($q) use ($term) {
                $q->where('car_rental_number', 'like', $term)
                ->orWhere('pickup_at', 'like', $term)
                ->orWhere('due_at', 'like', $term)
                ->orWhere('rate_amount', 'like', $term)
                ->orWhereHas('customer', function ($qq) use ($term) {
                return $qq->where('name', 'like', $term);
                })
                ->orWhereHas('transporter', function ($qq) use ($term) {
                return $qq->where('name', 'like', $term);
                })
                ->orWhereHas('vehicle', function ($qq) use ($term)  {
                return $qq->where('registration_number', 'like', $term)
                            ->where('fleet_number', 'like', $term);
                });
            });
        }

         return $query->orderByDesc($this->rental_filter)->get();

    }
    
 public function getTripsProperty(){

            $query = Trip::query()
            ->with('transporter:id,name','customer:id,name','loading_point:id,name','offloading_point:id,name','currency')
            ->where('authorization','approved')
            ->where('trip_status','!=', 'Cancelled')
            ->when($this->invoice_to === 'Customer', function ($q) {
                $q->where('currency_id', $this->selectedCurrency);
            })
            ->when($this->invoice_to === 'Transporter', function ($q) {
                $q->where('transporter_agreement', True);
            });

                 // Date window
            if ($this->from && $this->to ) {
                $from = Carbon::parse($this->from)->startOfDay();
                $to   = Carbon::parse($this->to)->endOfDay();
                $query->whereBetween($this->trip_filter, [$from, $to]);
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

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->invoice_amount) && $this->invoice_amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->invoice_amount;

        }


        $this->invoice_items = InvoiceItem::where('invoice_id',$this->invoice_id)->get();

        
        return view('livewire.invoices.edit',[
            'trips' => $this->trips,
            'bookings' => $this->bookings,
              'rentals' => $this->rentals,
            'invoice_items' => $this->invoice_items,
        ]);
    }
}
