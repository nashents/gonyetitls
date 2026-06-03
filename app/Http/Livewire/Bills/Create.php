<?php

namespace App\Http\Livewire\Bills;


use App\Mail\PendingNotificationEmails;
use App\Models\Account;
use App\Models\Asset;
use App\Models\Bill;
use App\Models\BillAccount;
use App\Models\BillExpense;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\ExchangeRate;
use App\Models\Horse;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Create extends Component
{


    public $bill;
    public $bill_id;
    public $bill_number;
    public $exchange_rate;
    public $exchange_amount;
    public $products;
    public $selectedProduct = [];
    public $bill_for;
    public $vendors;
    public $selectedVendor;
    public $assets;
    public $asset_id;
    public $transporters;
    public $transporter_id;
    public $horses;
    public $horse_id;
    public $trailers;
    public $trailer_id;
    public $vehicles;
    public $vehicle_id;
    public $drivers;
    public $driver_id;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $bill_date;
    public $due_date;
    public $notes;
    public $accounts;
    public $selectedAccount = [];
    public $description;
    public $amount;
    public $qty;
    public $subtotal;
    public $total;
    public $bill_total;
    public $tax_rate = [];
    public $tax_amount;
    public $total_tax_amount;
    public $user_id;
    public $company;
    public $currency_id;

    public $item_key;
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
    
    private function resetInputFields(){
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

    public function mount(){
        $this->company = Auth::user()->employee->company;
        $this->bill_number = $this->billNumber();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->products = Product::where('buy',True)->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->assets = Asset::with('product')->get()->sortBy('product.name');
        $this->drivers = Driver::all();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
    }

    public function billDate(){
        if ($this->due_date == "") {
            $this->due_date  = $this->bill_date;
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
                    $tax = Tax::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$key] = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Tax::find($id);
            if (isset($tax)) {
                $this->tax_rate[$key] = $tax->rate;
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

            $this->selectedProduct[$this->item_key] = $product->id;
            $this->selectedAccount[$this->item_key] = $product->expense_account_id;
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$this->item_key] = $product->price;
                }
                $this->qty[$this->item_key] = 1;
                $this->description[$this->item_key] = $product->description;
                if ($product->tax_id) {
                    $this->selectedTax[$this->item_key] = $product->tax_id;
                    $tax = Tax::find($product->tax_id);
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

        
    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedCurrency' => 'required',
        'bill_date' => 'required',
        'due_date' => 'required',
        'selectedProduct.0' => 'required',
        'selectedAccount.0' => 'required',
        'description.0' => 'required',
        'qty.0' => 'required',
        'amount.0' => 'required',
        'selectedAccount.*' => 'required',
        'selectedProduct.*' => 'required',
        'description.*' => 'required',
        'qty.*' => 'required',
        'amount.*' => 'required',
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
    public function updatedSelectedVendor($id){
        if(!is_null($id)){
            $vendor = Vendor::find($id);
            if (isset($vendor->currency_id)) {
                $this->currency_id = $vendor->currency_id;
            }
            
        }
    }

    public function store(){

        DB::transaction(function () {

        $bill = new Bill;
        $bill->user_id = Auth::user()->id;
        $bill->vendor_id = $this->selectedVendor;
        $bill->bill_for = $this->bill_for;
        if ($this->bill_for == "Transporter") {
            $bill->category = "Transporter";
            $bill->transporter_id = $this->transporter_id;
            $bill->horse_id = Null;
            $bill->driver_id = Null;
            $bill->vehicle_id = Null;
            $bill->trailer_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Horse") {
            $bill->category = "Horse";
            $bill->transporter_id = Null;
            $bill->horse_id = $this->horse_id;
            $bill->vehicle_id = Null;
            $bill->driver_id = Null;
            $bill->trailer_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Asset") {
            $bill->category = "Asset";
            $bill->transporter_id = Null;
            $bill->asset_id = $this->asset_id;
            $bill->horse_id = Null;
            $bill->vehicle_id = Null;
            $bill->driver_id = Null;
            $bill->trailer_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Driver") {
            $bill->category = "Driver";
            $bill->transporter_id = Null;
            $bill->horse_id = Null;
            $bill->driver_id = $this->driver_id;
            $bill->vehicle_id = Null;
            $bill->trailer_id = Null;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Trailer") {
            $bill->category = "Trailer";
            $bill->transporter_id = Null;
            $bill->horse_id = Null;
            $bill->vehicle_id = Null;
            $bill->driver_id = Null;
            $bill->trailer_id = $this->trailer_id;
            $bill->asset_id = Null;
        }
        elseif ($this->bill_for == "Vehicle") {
            $bill->category = "Vehicle";
            $bill->transporter_id = Null;
            $bill->horse_id = Null;
            $bill->vehicle_id = $this->vehicle_id;
            $bill->driver_id = Null;
            $bill->trailer_id = Null;
            $bill->asset_id = Null;
        }
      
        $bill->currency_id = $this->selectedCurrency;
        $bill->bill_number = $this->bill_number;
        $bill->bill_date = $this->bill_date;
        $bill->due_date = $this->due_date;
        $bill->notes = $this->notes;
        $bill->save();

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
        $bill->balance = $this->total;
        $bill->exchange_rate = $this->exchange_rate;
        $bill->exchange_amount = $this->exchange_amount;
        $bill->update();

        $notifications = Notification::where('when','before')->where('category','Bill Authorization')->where('status',1)->get();
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                   $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $bill));
                }
                }
            }
        }


        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Bill Created Successfully!!"
        ]);

        return redirect()->route('bills.index');

    });
    }


    public function render()
    {
       

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }
            $this->products = Product::where('buy',True)->orderBy('name','asc')->get();
            $this->currencies = Currency::orderBy('name','asc')->get();
            $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
                return $query->where('name','Expenses');
            })->orderBy('name','asc')->get();
            $this->vendors = Vendor::orderBy('name','asc')->get();
            $this->transporters = Transporter::orderBy('name','asc')->get();
            $this->assets = Asset::with('product')->get()->sortBy('product.name');
            $this->horses = Horse::orderBy('registration_number','asc')->get();
            $this->trailers = Trailer::orderBy('registration_number','asc')->get();
            $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
            $this->drivers = Driver::all();
            $this->income_accounts = Account::whereHas('account_type', function($q){
                $q->where('name', 'Income');
             })->orderBy('name','asc')->get();
            $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
                return $query->where('name','Expenses');
            })->orderBy('name','asc')->get();

            return view('livewire.bills.create',[
                'accounts' => $this->accounts,
                'currencies' => $this->currencies,
                'vendors' => $this->vendors,
                'drivers' => $this->drivers,
                'transporters' => $this->transporters,
                'assets' => $this->assets,
                'products' => $this->products,
                'horses' => $this->horses,
                'trailers' => $this->trailers,
                'vehicles' => $this->vehicles,
            ]);



    }
}
