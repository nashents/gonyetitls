<?php

namespace App\Http\Livewire\Inventories;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Bin;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoodsReceived;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\Rack;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\UnitsOfMeasure;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;


    public $stores;
    public $store_id;
    public $bins;
    public $bin_id;
    public $goods_receiveds;
    public $selectedGoodsReceived;
    public $racks;
    public $rack_id;
    public $purchases;
    public $selectedPurchase;
    public $selectedPurchaseProduct;
    public $purchase_order;
    public $purchase_products;
    public $currencies;
    public $source;
    public $exchange_rate;
    public $exchange_amount;
    public $selectedCurrency;
    public $selected_currency;
    public $vendor_types;
    public $vendors;
    public $vendor_id;
    public $transfer_items;
    public $transfers;
    public $selectedTransfer;
    public $selectedTransferItem;
  
    public $purchase_date;
    public $residual_value;
    public $weight ;
  
    public $life;
    public $depreciation_type;
    public $warranty_exp_date;
    public $condition;
    public $inventory_number;
    public $inventory;
   
    public $purchase_type;
    public $description;
    public $user_id;
    // Items Vars

    public $products;
    public $selectedProduct ;
    public $measurement ;
    public $serial_number ;
    public $tax_rate ;
    public $selectedTax ;
    public $qty  ;
    public $item_description  ;
    public $amount ;
    public $cost ;
    public $tax_amount;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    public $balance;

    public $to_bills;
  
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $selectedAccount;
    //store vars

    public $status;
    public $store_name;
    public $country;
    public $city;
    public $suburb;
    public $street_address;
    public $company;

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
    public $units_of_measures;

    public $category_id;
    public $rate;
    public $part_number;
    public $inventory_id;


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

    private function resetInputFields(){
        $this->store_name = '';
        $this->vendor_name = '';
        $this->country = '';
        $this->city = '';
        $this->suburb = '';
        $this->street_address = '';
    }

    public function storeStore(){
        $store = new Store;
        $store->user_id = Auth::user()->id;
        $store->name = $this->store_name;
        $store->country = $this->country;
        $store->city = $this->city;
        $store->suburb = $this->suburb;
        $store->street_address = $this->street_address;
        $store->status = '1';
        $store->save();
        $this->dispatchBrowserEvent('hide-storeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Store Created Successfully!!"
        ]);
    }

    public function mount($inventory){
        $this->company = Auth::user()->employee->company;
        $this->inventory = $inventory;
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->stores = Store::orderBy('name','asc')->get();
        $this->racks = Rack::orderBy('name','asc')->get();
        $this->bins = Bin::orderBy('name','asc')->get();
       
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
         $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
      
       
        $this->balance = $inventory->balance;
        $this->stores = Store::latest()->get();
      
        $this->category_id = $inventory->category_id;
        $this->purchase_date = $inventory->purchase_date;
        $this->qty = $inventory->qty;
        $this->amount = $inventory->amount;
        $this->measurement = $inventory->measurement;
        $this->cost = $inventory->cost;
        $product = $inventory->product;
        $this->selectedPurchaseProduct = $inventory->purchase_product_id;
        $this->selectedProduct = $inventory->product_id;
        $this->selectedAccount = $inventory->account_id;
        $this->item_description = $product->description;
        
        $this->to_bills = $inventory->bill ? True : False;

        $this->weight = $inventory->weight ?: 1;
       
        $this->source = $inventory->purchase_id ? "Purchase" : ($inventory->transfer_id ? "Transfer" : "Purchase");
        $this->store_id = $inventory->store_id;
        $this->bin_id = $inventory->bin_id;
        $this->rack_id = $inventory->rack_id;
        $this->status = $inventory->status;
        $this->rate = $inventory->rate;
        $this->residual_value = $inventory->residual_value;
        $this->purchase_type = $inventory->purchase_type;
        $this->selectedTax = $inventory->tax_id;
        $tax = Account::find($inventory->tax_id);
        if (isset($tax)) {
            $this->tax_rate = $tax->rate;
        }
       
        $this->description = $inventory->description;
        $this->depreciation_type = $inventory->depreciation_type;
        $this->inventory_number = $inventory->inventory_number;
        $this->part_number = $inventory->part_number;
        $this->serial_number = $inventory->serial_number;
        $this->selectedGoodsReceived = $inventory->goods_received_id;
        $this->selectedTransfer = $inventory->transfer_id;
        $this->selectedPurchase = $inventory->purchase_id;
        if ($this->selectedPurchase) {
          $this->vendor_id = Purchase::find($this->selectedPurchase)->vendor->id;
          $this->selectedCurrency = Purchase::find($this->selectedPurchase)->currency->id;
          $this->selected_currency = Currency::find($this->selectedCurrency);
        }else{
            $this->vendor_id = $inventory->vendor_id;
            $this->selectedCurrency = $inventory->currency_id;
        }
        $this->exchange_rate = $inventory->exchange_rate;
        $this->purchase_order = Purchase::find($inventory->purchase_id);
        if (isset($this->purchase_order)) {
            $this->purchase_products = $this->purchase_order->purchase_products; 
        }
        $this->condition = $inventory->condition;
        $this->warranty_exp_date = $inventory->warranty_exp_date;
        $this->life = $inventory->life;
        $this->inventory_id = $inventory->id;
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
                    $tax = Account::find($purchase_product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate = $tax->rate;
                    }
                }
                
            }
           
        }
    }

    public function updatedSelectedTransfer($id)
    {
        if (!is_null($id) ) {
            $transfer = Transfer::find($id);
            if(isset($transfer)){
                $this->store_id = $transfer->to;
                $this->selectedAccount = $transfer->account_id;
                $this->transfer_items = $transfer->transfer_items;
            }
        }
    }

    public function updatedSelectedTransferItem($id){
        if (!is_null($id)) {
            $transfer_item = TransferItem::find($id);
            if (isset($transfer_item)) {
                $this->selectedProduct = $transfer_item->product_id;
                $this->amount = $transfer_item->amount;
                $this->item_description = $transfer_item->product->description;
                $this->measurement = $transfer_item->product->unit_of_measure;
                if($this->department == "inventory"){
                      $this->serial_number = $transfer_item->inventory->serial_number;
                       $this->amount = $transfer_item->inventory->amount;
                        $this->weight = $transfer_item->inventory->weight;
                        $this->selectedCurrency = $transfer_item->inventory->currency_id;
                        $this->vendor_id = $transfer_item->inventory->vendor_id;
                }elseif($this->department == "tyre"){
                      $this->serial_number = $transfer_item->tyre->serial_number;
                       $this->amount = $transfer_item->tyre->amount;
                        $this->weight = $transfer_item->tyre->weight;
                         $this->selectedCurrency = $transfer_item->tyre->currency_id;
                        $this->vendor_id = $transfer_item->tyre->vendor_id;
                }
              
               
                $this->qty = $transfer_item->qty;
               

                if($transfer_item->tax_id){
                    $this->selectedTax = $transfer_item->tax_id;
                    $tax = Account::find($transfer_item->tax_id);
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
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedTax($id){
        if(!is_null($id)){
            $tax = Account::find($id);
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
            ->where('department', 'inventory')
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

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        
        'selectedProduct' => 'required',
        'qty' => 'required',
        'amount' => 'required',
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

    public function calculateExchangeAmount($total = null){
        if (($total && is_numeric($total)) && ($this->exchange_rate && is_numeric($this->exchange_rate)) ) {
            $this->exchange_amount = $this->exchange_rate * $total;
        }
    }

    public function updatedSerialNumber($value)
    {
        if (filled($value)) {
            $this->qty = 1;
        }
    }

    public function updatedQty($value)
    {
        if ((int)$value > 1) {
            $this->serial_number = null; // clear it if qty goes above 1
        }
    }


    public function update(){

        DB::transaction(function () {

        $inventory = Inventory::find($this->inventory_id);
        $inventory->user_id = Auth::user()->id;
        $inventory->vendor_id = $this->vendor_id ?: null;
        $inventory->goods_received_id = $this->selectedGoodsReceived ?: null;
        $inventory->store_id = $this->store_id ?: null;
        $inventory->bin_id = $this->bin_id ?: null;
        $inventory->rack_id = $this->rack_id ?: null;
        $inventory->product_id = $this->selectedProduct ?: null;
        $inventory->purchase_product_id = $this->selectedPurchaseProduct ?: null;
        $inventory->currency_id = $this->selectedCurrency ?: null;
        $inventory->measurement = $this->measurement ?: null;
        $inventory->amount = $this->amount;
        $inventory->cost = $this->cost;
        $inventory->qty = $this->qty;
        $inventory->weight = $this->weight;
        if(isset($this->qty) && is_numeric($this->qty) && isset($this->weight) && is_numeric($this->weight) ){
              $inventory->balance = $this->qty * $this->weight ;
        }
        $inventory->tax_rate = $this->tax_rate;
        $inventory->tax_id = $this->selectedTax;

        $subtotal = 0;
        $subtotal_incl = 0;
        $total = 0;

        if(isset($this->qty) && is_numeric($this->qty) && isset($this->amount) && is_numeric($this->amount) ){
            if (isset($this->cost) && is_numeric($this->cost)) {
                $subtotal = ($this->qty * $this->amount) + $this->cost;
                $inventory->subtotal = $subtotal ;
            }else{
                $subtotal = ($this->qty * $this->amount);
                $inventory->subtotal = $subtotal;
            }
        }
       
        if (isset($this->tax_rate) && is_numeric($this->tax_rate) && isset($this->selectedTax)) {
                $tax_amount = ($subtotal * ($this->tax_rate / 100 ));
                $inventory->tax_amount = $tax_amount;
                $total = $tax_amount + $subtotal;
                $inventory->subtotal_incl =  $total;
                $inventory->total =  $total;
        }else{
            $total = $subtotal;
            $inventory->subtotal_incl = $total;
            $inventory->total = $total;
        }

        $this->calculateExchangeAmount($total);

        $inventory->exchange_rate = $this->exchange_rate;
        $inventory->exchange_amount = $this->exchange_amount;
       
        $inventory->account_id = $this->selectedAccount;
        $inventory->residual_value = $this->residual_value;
        $inventory->depreciation_type = $this->depreciation_type;
        $inventory->purchase_date = $this->purchase_date;
        $inventory->purchase_type = $this->purchase_type;

        $inventory->purchase_id = null;
        $inventory->transfer_id = null;

        if ($this->source === 'Purchase') {
            $inventory->purchase_id = $this->selectedPurchase;
        }

        if ($this->source === 'Transfer') {
            $inventory->transfer_id = $this->selectedTransfer;
        }
        $inventory->condition = $this->condition;
        $inventory->serial_number = $this->serial_number;
        $inventory->warranty_exp_date = $this->warranty_exp_date;
        $inventory->life = $this->life;
        $inventory->description = $this->description;
        $inventory->status = $this->status;
        $inventory->disposed = 0;
        $inventory->update();

        if ($this->to_bills) {
            
            $bill = $inventory->bill;

            if($bill){     

            $bill->inventory_id = $inventory->id;
            $bill->category = "Inventory Item";
            $bill->bill_date = $inventory->purchase_date;
            $bill->account_id = $inventory->account_id;
            $account = Account::find($inventory->account_id);
            $account_type = $account ?  $account->account_type : "";
            if (isset($account_type)) {
                $bill->account_type_id = $account_type->id;
            }
            $bill->currency_id = $inventory->currency_id;
            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = "pending";
         
            $bill->total = $inventory->subtotal_incl;
            $bill->balance = $inventory->subtotal_incl;
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
                    $bill_expense->product_id = $inventory->product_id;
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $inventory->amount;
                    $bill_expense->subtotal = $inventory->subtotal;
                    $bill_expense->tax_amount = $inventory->tax_amount;
                    $bill_expense->subtotal_incl = $inventory->subtotal_incl;
                    $bill_expense->update();
                }
            }else{
                $bill = new Bill;
                $bill->user_id = Auth::user()->id;
                $bill->bill_number = $this->billNumber();
                $bill->inventory_id = $inventory->id;
                $bill->category = "Inventory Item";
                $bill->bill_date = $inventory->purchase_date;
                $bill->account_id = $inventory->account_id;
                $account = Account::find($inventory->account_id);
                $account_type = $account ?  $account->account_type : "";
                if (isset($account_type)) {
                    $bill->account_type_id = $account_type->id;
                }
                $bill->currency_id = $inventory->currency_id;
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = "pending";
             
                $bill->total = $inventory->subtotal_incl;
                $bill->balance = $inventory->subtotal_incl;
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
                $bill_expense->product_id = $inventory->product_id;
                $bill_expense->qty = 1;
                $bill_expense->amount = $inventory->amount;
                $bill_expense->subtotal = $inventory->subtotal;
                $bill_expense->tax_amount = $inventory->tax_amount;
                $bill_expense->subtotal_incl = $inventory->subtotal_incl;
                $bill_expense->save();
            }
        }

        Session::flash('success','Invetory Updated Successfully!!');
        return redirect(route('inventories.index'));
       
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

        
      
        $this->goods_receiveds = GoodsReceived::where('status',1)->where('department','inventory')->where('created_at', '>=', Carbon::now()->subMonth())->orderBy('created_at','desc')->get();
        $this->products = Product::with('brand')
        ->where('department', 'inventory')
        ->where('status', true)
        ->where('buy', true)
        ->get()
        ->sortBy([
            ['name', 'asc'],          // first sort by product name
            ['brand.name', 'asc'],    // then sort by brand name
        ]);
        $this->purchases = Purchase::where('department','inventory')->where('status',1)->where('created_at', '>=', Carbon::now()->subMonth())->where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->stores = Store::orderBy('name','asc')->get();
         $this->transfers = Transfer::where('department','inventory')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        return view('livewire.inventories.edit',[
            'products' => $this->products,
            'purchases' => $this->purchases,
            'vendors' => $this->vendors,
            'currencies' => $this->currencies,
            'stores' => $this->stores,
            'transfers' => $this->transfers,
           
        ]);
    }
}
