<?php

namespace App\Http\Livewire\Assets;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Bin;
use App\Models\Category;
use App\Models\CategoryValue;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoodsReceived;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\Rack;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Vendor;
use App\Models\VendorType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;


    public $stores;
    public $store_id;
    public $bins;
    public $bin_id;
    public $racks;
    public $rack_id;
    public $purchases;
    public $selectedPurchase;
    public $purchase_products;
    public $currencies;
    public $exchange_rate;
    public $exchange_amount;
    public $selectedCurrency;
    public $selected_currency;
    public $goods_receiveds;
    public $selectedGoodsReceived;
    public $vendor_types;
    public $vendors;
    public $vendor_id;
    public $company;

    public $asset_id;
    public $subtotal;
    public $subtotal_incl;
    public $balance;
  
    public $purchase_date;
    public $residual_value;
    public $weight ;

    public $life;
    public $depreciation_type;
    public $warranty_exp_date;
    public $condition;
    public $asset_number;
   
    public $purchase_type;
    public $description;
    public $user_id;
    // Items Vars

    public $products;
    public $measurement ;
    public $selectedProduct ;
    public $selectedPurchaseProduct ;
    public $serial_number ;
    public $tax_rate ;
    public $selectedTax ;
    public $selectedAccount ;
    public $qty  ;
    public $item_description  ;
    public $amount ;
    public $cost ;
    public $tax_amount;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    
    public $to_bills;
  
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;

    //store vars

    public $status;
    public $store_name;
    public $country;
    public $city;
    public $suburb;
    public $street_address;

    // vendor vars

    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $vendor_name;
    public $phonenumber;
    public $worknumber;
    public $email;
    public $website;


    public $expires_at;
    public $title;
    public $file;

    public $inputs ;
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

    public $documentInputs ;
    public $m = 1;
    public $o = 1;
    public function documentsAdd($m)
    {
        $m = $m + 1;
        $this->m = $m;
        array_push($this->documentInputs ,$m);
    }

    public function documentsRemove($m)
    {
        unset($this->documentInputs[$m]);
    }

    public function mount($asset){
         $this->company = Auth::user()->employee->company;
      
        $this->vendor_types = VendorType::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::latest()->get();
      
        $this->stores = Store::orderBy('name','asc')->get();
        $this->racks = Rack::orderBy('name','asc')->get();
        $this->bins = Bin::orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
        $this->vendor_id = $asset->vendor_id;
        $this->selectedCurrency = $asset->currency_id;
        $this->selectedProduct = $asset->product_id;
        $this->selectedPurchaseProduct = $asset->purchase_product_id;
        $this->selectedAccount = $asset->account_id;
        $this->purchase_date = $asset->purchase_date;
        $this->selectedPurchase = $asset->purchase_id;
        $this->measurement = $asset->measurement;
        $this->selectedGoodsReceived = $asset->goods_received_id;
        $this->qty = $asset->qty;
        $this->to_bills = $asset->bill ? True : False;
        $this->amount = $asset->amount;
        $this->cost = $asset->cost;
        $this->tax_amount = $asset->tax_amount;
        $this->selectedTax = $asset->tax_id;
        $this->residual_value = $asset->residual_value;
        $this->serial_number = $asset->serial_number;
        $this->purchase_type = $asset->purchase_type;
        $this->description = $asset->description;
        $this->depreciation_type = $asset->depreciation_type;
        $this->asset_number = $asset->asset_number;
     
        $this->weight = $asset->weight;
        $this->store_id = $asset->store_id;
        $this->bin_id = $asset->bin_id;
        $this->rack_id = $asset->rack_id;
        $this->balance = $asset->balance;
        $this->condition = $asset->condition;
        $this->warranty_exp_date = $asset->warranty_exp_date;
        $this->life = $asset->life;
        $this->status = $asset->status;
        $this->asset_id = $asset->id;
        $this->exchange_rate = $asset->exchange_rate;

         if ($this->selectedPurchase) {
          $this->vendor_id = Purchase::find($this->selectedPurchase)->vendor->id;
          $this->selectedCurrency = Purchase::find($this->selectedPurchase)->currency->id;
          $this->purchase_products = Purchase::find($this->selectedPurchase)->purchase_products;
          $this->selected_currency = Currency::find($this->selectedCurrency);
        }
    }

    
       public function updatedSelectedPurchase($id)
    {
        if (!is_null($id) ) {
            $purchase_order = Purchase::find($id);
            if(isset($purchase_order)){
                $this->selectedCurrency = $purchase_order->currency_id;
                $this->exchange_rate = $purchase_order->exchange_rate;
                $this->selected_currency = Currency::find($this->selectedCurrency);
                $this->vendor_id = $purchase_order->vendor_id;
                $this->selectedAccount = $purchase_order->account_id;
                $this->purchase_products = $purchase_order->purchase_products;
            }
        }
    }

          public function updatedSelectedPurchaseProduct($id){
        if (!is_null($id)) {
            $purchase_product = PurchaseProduct::find($id);
            if (isset($purchase_product)) {
                 $this->selectedProduct = $purchase_product->product_id;
                $this->amount = $purchase_product->amount;
                $this->item_description = $purchase_product->product->description;
                $this->measurement = $purchase_product->product->unit_of_measure;
              
                if($purchase_product->tax_id){
                    $this->selectedTax = $purchase_product->tax_id;
                    $tax = Tax::find($purchase_product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate = $tax->rate;
                    }
                }
                
            }
           
        }
    }

    public function updatedSelectedProduct($id){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount = $product->price;
                }
                $this->item_description = $product->description;
                $this->measurement = $product->unit_of_measure;
            
                if ($product->tax_id) {
                    $this->selectedTax = $product->tax_id;
                    $tax = Tax::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedTax($id){
        if(!is_null($id)){
            $tax = Tax::find($id);
            if (isset($tax)) {
                $this->tax_rate = $tax->rate;
            }else{
                $this->tax_rate = "";
            }
           
        }
    }

      public function refresh($category){

        if($category == "racks"){
            $this->racks = Rack::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Racks Refreshed Successfully!!."
            ]);
        }elseif($category == "bins"){
            $this->bins = Bin::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bins Refreshed Successfully!!."
            ]);
        }elseif($category == "goods_receiveds"){
            $this->goods_receiveds = GoodsReceived::orderBy('id','desc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"GRVs Refreshed Successfully!!."
            ]);
        }
        elseif($category == "products"){
            $this->products = Product::with('brand')
            ->where('department', 'asset')
            ->where('status', true)
            ->where('buy', true)
            ->get()
            ->sortBy([
                ['name', 'asc'],          // first sort by product name
                ['brand.name', 'asc'],    // then sort by brand name
            ]);
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Products Refreshed Successfully!!."
            ]);
        }
        elseif($category == "stores"){
            $this->stores = Store::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Stores Refreshed Successfully!!."
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

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedProduct' => 'required',
        'purchase_date' => 'required',
    ];

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

    public function calculateExchangeAmount($total = null){
        if (($total && is_numeric($total)) && ($this->exchange_rate && is_numeric($this->exchange_rate)) ) {
            $this->exchange_amount = $this->exchange_rate * $total;
        }
    }



    public function update(){

    DB::transaction(function () {

        $asset = Asset::find($this->asset_id);
        $asset->user_id = Auth::user()->id;
        $asset->vendor_id = $this->vendor_id ?? null;
        $asset->goods_received_id = $this->selectedGoodsReceived ? $this->selectedGoodsReceived : null;
        $asset->store_id = $this->store_id ?? null;
        $asset->bin_id = $this->bin_id ?? null;
        $asset->rack_id = $this->rack_id ?? null;
        $asset->product_id = $this->selectedProduct ?? null;
        $asset->purchase_product_id = $this->selectedPurchaseProduct ?? null;
        $asset->currency_id = $this->selectedCurrency ?? null;
        $asset->amount = $this->amount;
        $asset->cost = $this->cost;
        $asset->measurement = $this->measurement;
        $asset->qty = $this->qty;
        $asset->weight = $this->weight;
        if(isset($this->qty) && is_numeric($this->qty) && isset($this->weight) && is_numeric($this->weight) ){
              $asset->balance = $this->qty * $this->weight ;
        }
        $asset->tax_rate = $this->tax_rate;
        $asset->tax_id = $this->selectedTax;

        $subtotal = 0;
        $subtotal_incl = 0;
        $total = 0;

        if(isset($this->qty) && is_numeric($this->qty) && isset($this->amount) && is_numeric($this->amount) ){
            if (isset($this->cost) && is_numeric($this->cost)) {
                $subtotal = ($this->qty * $this->amount) + $this->cost;
                $asset->subtotal = $subtotal ;
            }else{
                $subtotal = ($this->qty * $this->amount);
                $asset->subtotal = $subtotal;
            }
        }
       
        if (isset($this->tax_rate) && is_numeric($this->tax_rate) && isset($this->selectedTax)) {
                $tax_amount = ($subtotal * ($this->tax_rate / 100 ));
                $asset->tax_amount = $tax_amount;
                $total = $tax_amount + $subtotal;
                $asset->subtotal_incl =  $total;
                $asset->total =  $total;
        }else{
            $total = $subtotal;
            $asset->subtotal_incl = $total;
            $asset->total = $total;
        }

        $this->calculateExchangeAmount($total);

        $asset->exchange_rate = $this->exchange_rate;
        $asset->exchange_amount = $this->exchange_amount;
       
        $asset->account_id = $this->selectedAccount;
        $asset->residual_value = $this->residual_value;
        $asset->depreciation_type = $this->depreciation_type;
        $asset->purchase_date = $this->purchase_date;
        $asset->purchase_type = $this->purchase_type;
        $asset->purchase_id = $this->selectedPurchase ? $this->selectedPurchase : null;
        $asset->condition = $this->condition;
        $asset->serial_number = $this->serial_number;
        $asset->warranty_exp_date = $this->warranty_exp_date;
        $asset->life = $this->life;
        $asset->description = $this->description;
        $asset->status = $this->status;
        $asset->disposed = 0;
        $asset->update();

        if ($this->to_bills) {
            
            $bill = $asset->bill;

            if($bill){     

            $bill->asset_id = $asset->id;
            $bill->category = "asset Item";
            $bill->bill_date = $asset->purchase_date;
            $bill->account_id = $asset->account_id;
            $account = Account::find($asset->account_id);
            $account_type = $account ?  $account->account_type : "";
            if (isset($account_type)) {
                $bill->account_type_id = $account_type->id;
            }
            $bill->currency_id = $asset->currency_id;
            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = "pending";
         
            $bill->total = $asset->subtotal_incl;
            $bill->balance = $asset->subtotal_incl;
            $bill->to_be_paid = True;
            $bill->update();

                $bill_expense = $bill->bill_expenses->first();
                if($bill_expense){
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->currency_id = $bill->currency_id;
                    $bill_expense->account_id = $bill->account_id;
                    $account = Account::find($bill->account_id);
                    $account_type = $account ? $account->account_type : "";
                    if (isset($account_type)) {
                        $bill_expense->account_type_id = $account_type->id;
                    }
                    $bill_expense->product_id = $asset->product_id;
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $asset->amount;
                    $bill_expense->subtotal = $asset->subtotal;
                    $bill_expense->tax_amount = $asset->tax_amount;
                    $bill_expense->subtotal_incl = $asset->subtotal_incl;
                    $bill_expense->update();
                }
            }else{
                $bill = new Bill;
                $bill->user_id = Auth::user()->id;
                $bill->bill_number = $this->billNumber();
                $bill->asset_id = $asset->id;
                $bill->category = "asset Item";
                $bill->bill_date = $asset->purchase_date;
                $bill->account_id = $asset->account_id;
                $account = Account::find($asset->account_id);
                $account_type = $account ?  $account->account_type : "";
                if (isset($account_type)) {
                    $bill->account_type_id = $account_type->id;
                }
                $bill->currency_id = $asset->currency_id;
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = "pending";
             
                $bill->total = $asset->subtotal_incl;
                $bill->balance = $asset->subtotal_incl;
                $bill->to_be_paid = True;
                $bill->save();

                $bill_expense = new BillExpense;
                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;
                $bill_expense->account_id = $bill->account_id;
                $account = Account::find($bill->account_id);
                $account_type = $account ? $account->account_type : "";
                if (isset($account_type)) {
                    $bill_expense->account_type_id = $account_type->id;
                }
                $bill_expense->product_id = $asset->product_id;
                $bill_expense->qty = 1;
                $bill_expense->amount = $asset->amount;
                $bill_expense->subtotal = $asset->subtotal;
                $bill_expense->tax_amount = $asset->tax_amount;
                $bill_expense->subtotal_incl = $asset->subtotal_incl;
                $bill_expense->save();
            }
        }

        return redirect(route('assets.index'));
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Asset Updated Successfully!!"
        ]);

    });
    }

     public function updatedSelectedGoodsReceived($id){
        if(!is_null($id)){
            $goods_received = GoodsReceived::find($id);
            $this->vendor_id = $goods_received->vendor_id ?? null;
        }
    }

    public function render()
    {
        
        $this->goods_receiveds = GoodsReceived::where('status',1)->where('department','asset')->where('created_at', '>=', Carbon::now()->subMonth())->orderBy('created_at','desc')->get();
       $this->products = Product::with('brand')
        ->where('department', 'asset')
        ->where('status', true)
        ->where('buy', true)
        ->get()
        ->sortBy([
            ['name', 'asc'],          // first sort by product name
            ['brand.name', 'asc'],    // then sort by brand name
        ]);
        $this->vendor_types = VendorType::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->purchases = Purchase::where('department','asset')->where('status',1)->where('created_at', '>=', Carbon::now()->subMonth())->where('authorization','approved')->orderBy('created_at','desc')->get();
            return view('livewire.assets.edit',[
                'products' => $this->products,
                'vendor_types' => $this->vendor_types,
                'vendors' => $this->vendors,
                'purchases' => $this->purchases,
            ]);
    }
}
