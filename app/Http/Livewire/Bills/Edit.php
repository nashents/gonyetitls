<?php

namespace App\Http\Livewire\Bills;


use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Trip;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\BillExpense;
use App\Models\Transporter;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
    public $bill;
    public $bill_id;
    public $bill_number;
    public $exchange_rate;
    public $exchange_amount;
    public $products;
    
    public $selectedProduct = [];
    public $selectedAccount = [];
    public $description = []; 
    public $qty = [];
    public $amount = [];
    public $selectedTax = [];
    public $tax_rate = [];

    public $selectedCurrentProduct = [];
    public $selectedCurrentAccount = [];
    public $current_description = []; 
    public $current_qty = [];
    public $current_amount = [];
    public $selectedCurrentTax = [];
    public $current_tax_rate = [];

    public $bill_for;
    public $vendors;
    public $selectedVendor;
    public $transporters;
    public $transporter_id;
    public $horses;
    public $horse_id;
    public $drivers;
    public $driver_id;
    public $trailers;
    public $trailer_id;
    public $vehicles;
    public $vehicle_id;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $bill_date;
    public $due_date;
    public $notes;
    public $accounts;
    public $company;
  
  

    
    public $subtotal;
    public $total;
    public $bill_total;
    public $tax_amount;
    public $user_id;
    public $bill_expenses;

    public $item_key;
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
    public $sell = False;
    public $buy = True;

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
        $this->sell = False;
        $this->buy = True;
    }
   

    public function billNumber(){

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

        $bill = Bill::latest()->orderBy('id','desc')->first();

        if (!$bill) {
            $bill_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $bill->id + 1;
            $bill_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $bill_number;


    }


    public function updatedSelectedVendor($id){
        if(!is_null($id)){
            $vendor = Vendor::find($id);
            if (isset($vendor->currency_id)) {
                $this->currency_id = $vendor->currency_id;
            }
            
        }
    }

    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$key] = $product->price;
                }
                if ($product->description) {
                    $this->description[$key] = $product->description;
                }
                if ($product->expense_account_id) {
                    $this->selectedAccount[$key] = $product->expense_account_id;
                }
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

    public function showItem($key){
        $this->item_key = $key;
        $this->dispatchBrowserEvent('show-product_serviceModal');
    }

    public function storeItem(){
        // try{
       
                $product = new Product;
                $product->user_id = Auth::user()->id;
                $product->name = $this->item_name;
                $product->description = $this->item_description;
                $product->price = $this->buy_price;
                $product->sell_price = $this->sell_price;
                $product->sell = $this->sell;
                $product->buy = $this->buy;
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

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate[$key] = $tax->rate;
            }
           
        }
    }

    public function updatedSelectedCurrentProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->current_amount[$key] = $product->price;
                }
                if ($product->description) {
                    $this->current_qtydescription[$key] = $product->description;
                }
                if ($product->expense_account_id) {
                    $this->selectedCurrentAccount[$key] = $product->expense_account_id;
                }
                if ($product->tax_id) {
                    $this->selectedCurrentTax[$key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->current_tax_rate[$key] = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedCurrentTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->current_tax_rate[$key] = $tax->rate;
            }
           
        }
    }

    public function mount($id){
        $this->company = Auth::user()->employee->company;
        $this->bill_id = $id;
        $this->bill = Bill::find($id);
        $this->trips = Trip::latest()->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->expenses = Expense::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->drivers = Driver::all();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->products = Product::where('buy',True)->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->selectedCurrency = $this->bill->currency_id;
        $this->exchange_rate = $this->bill->exchange_rate;
        $this->exchange_amount = $this->bill->exchange_amount;
        $this->bill_for = $this->bill->bill_for;
        $this->horse_id = $this->bill->horse_id;
        $this->trailer_id = $this->bill->trailer_id;
        $this->vehicle_id = $this->bill->vehicle_id;
        $this->driver_id = $this->bill->driver_id;
        $this->trip_id = $this->bill->trip_id;
        $this->selectedVendor = $this->bill->vendor_id;
        $this->bill_expenses = $this->bill->bill_expenses;
        if(isset($this->bill_expenses)){
                foreach($this->bill_expenses as $expense){
                    $this->selectedCurrentProduct[] = $expense->product_id;
                    $this->selectedCurrentAccount[] = $expense->account_id;
                    $this->current_description[] = $expense->description; 
                    $this->current_qty[] = $expense->qty;
                    $this->current_amount[] = $expense->amount;
                    $this->selectedCurrentTax[] = $expense->tax_id;
                    $this->current_tax_rate[] = $expense->tax_rate;
                }
        }
        $this->account_id = $this->bill->account_id;
        $this->account_type_id = $this->bill->account_type_id;
        $this->transporter_id = $this->bill->transporter_id;
        $this->bill_date = $this->bill->bill_date;
        $this->bill_number = $this->bill->bill_number;
        $this->due_date = $this->bill->due_date;
        $this->notes = $this->bill->notes;


    }

    public function updated($value){
        $this->validateOnly($value);
    }
   

    protected $rules = [
        'selectedCurrency' => 'required',
        'bill_date' => 'required',
        'due_date' => 'required',
        'bill_date' => 'required',
    ];

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


    public function removeShow($id){
        $this->bill_expense = BillExpense::find($id);
        $this->tax_amount = $this->bill->tax_amount;
        $this->total = $this->bill->total;
        $this->subtotal = $this->bill->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeBillItem(){ 

        if (is_numeric($this->subtotal) && $this->bill_expense->subtotal) {
            $this->subtotal = $this->subtotal - $this->bill_expense->subtotal;
        }
        if (is_numeric($this->total) && $this->bill_expense->subtotal_incl) {
            $this->total = $this->total - $this->bill_expense->subtotal_incl;
        }
        if (is_numeric($this->tax_amount) && $this->bill_expense->tax_amount) {
            $this->tax_amount = $this->tax_amount - $this->bill_expense->tax_amount;

        }

        $bill =  Bill::find($this->bill->id);
        $bill->total = $this->total;
        $bill->subtotal = $this->subtotal;
        $bill->tax_amount = $this->tax_amount;
        $total_paid = $bill->payments->where('amount','!=','')->where('amount','!=',Null)->sum('amount');
        if((isset($total_paid) && is_numeric($total_paid) && $total_paid > 0) && $this->total > $total_paid){
            $bill->balance = $this->total - $total_paid;
           
        }else{
            $bill->balance = $this->total;
        }
        $bill->update();

        $this->bill_expense->delete();
        $this->bill_expenses = BillExpense::where('bill_id',$this->bill_id)->get();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Deleted Successfully!!"
        ]);
    

   

    }

    public function update(){

        DB::transaction(function () {

        $bill =  Bill::find($this->bill_id);
        $bill->vendor_id = $this->selectedVendor;
        $bill->bill_for = $this->bill_for;
        if ($this->bill_for == "Transporter") {
            $bill->category = "Transporter";
            $bill->transporter_id = $this->transporter_id;
            $bill->horse_id = Null;
            $bill->vehicle_id = Null;
            $bill->trailer_id = Null;
            $bill->driver_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Horse") {
            $bill->category = "Horse";
            $bill->transporter_id = Null;
            $bill->horse_id = $this->horse_id;
            $bill->vehicle_id = Null;
            $bill->trailer_id = Null;
            $bill->driver_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Asset") {
            $bill->category = "Asset";
            $bill->transporter_id = Null;
            $bill->asset_id = $this->asset_id;
            $bill->horse_id = Null;
            $bill->vehicle_id = Null;
            $bill->trailer_id = Null;
            $bill->driver_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Trailer") {
            $bill->category = "Trailer";
            $bill->transporter_id = Null;
            $bill->horse_id = Null;
            $bill->vehicle_id = Null;
            $bill->trailer_id = $this->trailer_id;
            $bill->driver_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Driver") {
            $bill->category = "Driver";
            $bill->transporter_id = Null;
            $bill->horse_id = Null;
            $bill->vehicle_id = Null;
            $bill->trailer_id = Null;
            $bill->driver_id = $this->driver_id;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Vehicle") {
            $bill->category = "Vehicle";
            $bill->transporter_id = Null;
            $bill->horse_id = Null;
            $bill->vehicle_id = $this->vehicle_id;
            $bill->trailer_id = Null;
            $bill->driver_id = Null;
            $bill->asset_id = Null;
        }
      
        $bill->currency_id = $this->selectedCurrency;
        $bill->bill_number = $this->bill_number;
        $bill->bill_date = $this->bill_date;
        $bill->due_date = $this->due_date;
        $bill->notes = $this->notes;
        $bill->update();

        if (isset($this->bill_expenses)) {
            foreach($this->bill_expenses as $key => $expense){

                $bill_expense = BillExpense::find($expense->id);

                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;

                if (isset($this->selectedCurrentProduct[$key])) {
                    $bill_expense->product_id = $this->selectedCurrentProduct[$key];
                }

                if (isset($this->selectedCurrentAccount[$key])) {
                    $account = Account::find($this->selectedCurrentAccount[$key]);
                    $bill_expense->account_id = $this->selectedCurrentAccount[$key];
                    $bill_expense->account_type_id = $account->account_type->id;
                }

                if (isset($this->current_description[$key])) {
                    $bill_expense->description = $this->current_description[$key];
                }

                if (isset($this->current_qty[$key])) {
                    $bill_expense->qty = $this->current_qty[$key];
                }

                if (isset($this->current_amount[$key])) {
                    $bill_expense->amount = $this->current_amount[$key];
                }

                if (isset($this->selectedCurrentTax[$key])) {
                    $bill_expense->tax_id = $this->selectedCurrentTax[$key];
                }

                if (is_numeric($this->current_amount[$key]) && is_numeric($this->current_qty[$key])) {
                    $current_expense_subtotal = $this->current_amount[$key]*$this->current_qty[$key];
                    $bill_expense->subtotal = $current_expense_subtotal;
                    $this->subtotal = $this->subtotal + $current_expense_subtotal;
                }
                if ((isset($this->current_tax_rate[$key]) && is_numeric($this->current_tax_rate[$key]) ) && isset($this->selectedCurrentTax[$key])) {
                    $current_expense_tax_amount = ($current_expense_subtotal * ($this->current_tax_rate[$key] / 100 ));
                    $bill_expense->tax_amount =  $current_expense_tax_amount;
                    $bill_expense->tax_rate =  $this->current_tax_rate[$key];
                    $current_expense_subtotal_incl = $current_expense_tax_amount + $current_expense_subtotal ;
                    $bill_expense->subtotal_incl =  $current_expense_subtotal_incl;
                    $this->tax_amount = $this->tax_amount + $current_expense_tax_amount;
                    $this->total = $this->total +  $current_expense_subtotal_incl ;
                    
                }else{
                    $current_expense_subtotal_incl = $current_expense_subtotal;
                    $bill_expense->subtotal_incl =  $current_expense_subtotal_incl;
                    $this->total = $this->total +  $current_expense_subtotal_incl;
                }

                if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                    $bill_expense->exchange_rate = $this->exchange_rate;
                    $bill_expense->exchange_amount = $this->exchange_rate * $current_expense_subtotal_incl ;
                 }

                 $bill_expense->update();
            }
        }


        if (isset($this->selectedProduct)) {
            foreach($this->selectedProduct as $key => $value){

                $bill_expense = new BillExpense;
                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;

                if (isset($this->selectedProduct[$key])) {
                    $bill_expense->product_id = $this->selectedProduct[$key];
                }

                if (isset($this->selectedAccount[$key])) {
                    $account = Account::find($this->selectedAccount[$key]);
                    $bill_expense->account_id = $this->selectedAccount[$key];
                    $bill_expense->account_type_id = $account->account_type->id;
                }

                if (isset($this->description[$key])) {
                    $bill_expense->description = $this->description[$key];
                }

                if (isset($this->qty[$key])) {
                    $bill_expense->qty = $this->qty[$key];
                }

                if (isset($this->amount[$key])) {
                    $bill_expense->amount = $this->amount[$key];
                }

                if (isset($this->selectedTax[$key])) {
                    $bill_expense->tax_id = $this->selectedTax[$key];
                }

                if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && (isset($this->qty[$key]) && is_numeric($this->qty[$key]))) {
                    $expense_subtotal = $this->amount[$key]*$this->qty[$key];
                    $bill_expense->subtotal = $expense_subtotal;
                    $this->subtotal = $this->subtotal + $expense_subtotal;
                }
                if ((isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) && isset($this->selectedTax[$key])) {
                    $expense_tax_amount = ($expense_subtotal * ($this->tax_rate[$key] / 100 ));
                    $bill_expense->tax_amount =  $expense_tax_amount;
                    $bill_expense->tax_rate =  $this->tax_rate[$key];
                    $expense_subtotal_incl = $expense_tax_amount + $expense_subtotal ;
                    $bill_expense->subtotal_incl =  $expense_subtotal_incl;
                    $this->tax_amount = $this->tax_amount + $expense_tax_amount;
                    $this->total = $this->total +  $expense_subtotal_incl ;
                    
                }else{
                    $expense_subtotal_incl = $expense_subtotal;
                    $bill_expense->subtotal_incl =  $expense_subtotal_incl;
                    $this->total = $this->total +  $expense_subtotal_incl;
                }

                if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                    $bill_expense->exchange_rate = $this->exchange_rate;
                    $bill_expense->exchange_amount = $this->exchange_rate * $expense_subtotal_incl ;
                 }

                 $bill_expense->save();
            }
        }

        $bill = Bill::find($bill->id);
        $bill->tax_amount = $this->tax_amount;
        $bill->subtotal = $this->subtotal;
        $bill->total = $this->total;
        $bill->exchange_rate = $this->exchange_rate;
        $bill->exchange_amount = $this->exchange_amount;
        $total_paid = $bill->payments->where('amount','!=','')->where('amount','!=',Null)->sum('amount');
        if((isset($total_paid) && is_numeric($total_paid) && $total_paid > 0) && $this->total > $total_paid){
            $bill->balance = $this->total - $total_paid;
           
        }else{
            $bill->balance = $this->total;
        }
        $bill->update();


        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Bill Updated Successfully!!"
        ]);

        return redirect()->route('bills.index');
        
    });
    }


    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }

        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->expenses = Expense::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->drivers = Driver::all();
        $this->products = Product::where('buy',True)->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->bill_expenses = BillExpense::where('bill_id',$this->bill_id)->get();
        return view('livewire.bills.edit',[
            'expenses' => $this->expenses,
            'currencies' => $this->currencies,
            'vendors' => $this->vendors,
            'drivers' => $this->drivers,
            'transporters' => $this->transporters,
            'products' => $this->products,
            'bill_expenses' => $this->bill_expenses,
        ]);





    }
}
