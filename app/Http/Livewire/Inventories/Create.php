<?php

namespace App\Http\Livewire\Inventories;


use App\Models\Account;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Bin;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryValue;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\ExchangeRate;
use App\Models\GoodsReceived;
use App\Models\inventorieserial;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\Rack;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\UnitsOfMeasure;
use App\Models\Vendor;
use App\Models\VendorType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
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
    public $selectedPurchaseProduct;
    public $goods_receiveds;
    public $selectedGoodsReceived;
    public $purchase_products;
    public $transfer_items;
    public $transfers;
    public $selectedTransfer;
    public $selectedTransferItem;
    public $source = "Purchase";
    public $currencies;
    public $exchange_rate;
    public $exchange_amount;
    public $selectedCurrency;
    public $selected_currency;
    public $vendor_types;
    public $vendors;
    public $vendor_id;
  
    public $purchase_date;
    public $residual_value;
    public $life;
    public $depreciation_type;
    public $warranty_exp_date;
    public $condition;
    public $inventory_number;
    public $units_of_measures;
   
    public $purchase_type;
    public $description;
    public $user_id;
    // Items Vars

    public $products;
    public $selectedProduct = [];
    public $measurement = [];
    public $serial_number = [];
    public $tax_rate = [];
    public $selectedTax = [];
    public $qty = [] ;
    public $weight = [];
    public $item_description = [] ;
    public $amount = [];
    public $cost = [];
    public $tax_amount;
    public $tax_id;
    public $tax;
    public $tax_accounts;
   
  
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

    public $to_bills = False;

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
    public $company;
    public $department;


    public $expires_at;
    public $title;
    public $file;

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

    public $documentInputs = [];
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


    public function mount(){
        $this->department = "inventory";
        $this->company = Auth::user()->employee->company;
        $this->stores = Store::orderBy('name','asc')->get();
        $this->racks = Rack::orderBy('name','asc')->get();
        $this->bins = Bin::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();


        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->store_name = '';
        $this->vendor_name = '';
        $this->country = '';
        $this->city = '';
        $this->suburb = '';
        $this->street_address = '';
    }

    public function updatedSelectedGoodsReceived($id){
        if(!is_null($id)){
            $goods_received = GoodsReceived::find($id);
            $this->vendor_id = $goods_received->vendor_id ?? null;
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

    public function vendorNumber(){

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

            $last_vendor_id = Vendor::latest()->pluck('id')->first();

        if (!$last_vendor_id) {
            $vendor_number =  $initials .'V'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $vendor_number = $last_vendor_id + 1;
            $vendor_number =  $initials .'V'. str_pad($vendor_number, 5, "0", STR_PAD_LEFT);
        }

        return  $vendor_number;


    }

    public function updatedSerialNumber($value, $key)
    {
        if (filled($value)) {
            $this->qty[$key] = 1;
        }
    }

    public function inventoryNumber(){
       
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
            $inventory = Inventory::orderBy('id', 'desc')->first();

        if (!$inventory) {
            $inventory_number =  $initials .'A'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $inventory->id + 1;
            $inventory_number =  $initials .'A'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $inventory_number;


    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedProduct' => 'required',
        'purchase_date' => 'required',
    ];
   

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

    
    public function storeVendor(){

        $vendor = new Vendor;
        $vendor->user_id = Auth::user()->id;
        $vendor->vendor_number = $this->vendorNumber();
        $vendor->name = $this->vendor_name;
        $vendor->email = $this->email;
        $vendor->phonenumber = $this->phonenumber;
        $vendor->currency_id = $this->selectedCurrency ? $this->selectedCurrency : NULL;
        $vendor->worknumber = $this->worknumber;
        $vendor->website = $this->website;
        $vendor->country = $this->country;
        $vendor->city = $this->city;
        $vendor->suburb = $this->suburb;
        $vendor->street_address = $this->street_address;
        $vendor->status = 1;
        $vendor->save();

        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->vendor_id = $vendor->id;
               $contact->category = 'vendor';
               if (isset($this->contact_name[$key])) {
                $contact->name = $this->contact_name[$key];
               }
               if (isset($this->contact_surname[$key])) {
                $contact->surname = $this->contact_surname[$key];
               }
                if (isset($this->contact_phonenumber[$key])) {
                    $contact->phonenumber = $this->contact_phonenumber[$key];
                }
                if (isset($this->contact_email[$key])) {
                    $contact->email = $this->contact_email[$key];
                }
              
               $contact->save();
            }
        }
    
        if (isset($this->file) && isset($this->title) && $this->file != "") {
           
            foreach ($this->file as $key => $value) {
              if(isset($this->file[$key])){
                  $file = $this->file[$key];
                  // get file with ext
                  $fileNameWithExt = $file->getClientOriginalName();
                  //get filename
                  $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                  //get extention
                  $extention = $file->getClientOriginalExtension();
                  //file name to store
                  $fileNameToStore= $filename.'_'.time().'.'.$extention;
                  $file->storeAs('/documents', $fileNameToStore, 'my_files');

              }
              $document = new Document;
              $document->vendor_id = $vendor->id;
              $document->category = 'vendor';
              if(isset($this->title[$key])){
              $document->title = $this->title[$key];
              }
              if (isset($fileNameToStore)) {
                  $document->filename = $fileNameToStore;
              }
              if(isset($this->expires_at[$key])){
                  $document->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  $today = now()->toDateTimeString();
                  $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  if ($today <=  $expire) {
                      $document->status = 1;
                  }else{
                      $document->status = 0;
                  }
              }else {
                $document->status = 1;
              }
              $document->save();

            }
                   # code...
          
        }

        $this->dispatchBrowserEvent('hide-vendorModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vendor Created Successfully!!"
        ]);

      
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
   

    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$key] = $product->price;
                }
                $this->item_description[$key] = $product->description;
                $this->qty[$key] = 1;
                $this->measurement[$key] = $product->unit_of_measure;
                $this->weight[$key] = 1;
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

    public function updatedSelectedPurchaseProduct($id, $key){
        if (!is_null($id)) {
            $purchase_product = PurchaseProduct::find($id);
            if (isset($purchase_product)) {
                $this->selectedProduct[$key] = $purchase_product->product_id;
                $this->amount[$key] = $purchase_product->amount;
                $this->item_description[$key] = $purchase_product->product->description;
                $this->measurement[$key] = $purchase_product->product->unit_of_measure;
                $this->qty[$key] = $purchase_product->qty;
                $this->weight[$key] = 1;
                if($purchase_product->tax_id){
                    $this->selectedTax[$key] = $purchase_product->tax_id;
                    $tax = Tax::find($purchase_product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$key] = $tax->rate;
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

    public function updatedSelectedTransferItem($id, $key){
        if (!is_null($id)) {
            $transfer_item = TransferItem::find($id);
            if (isset($transfer_item)) {
                $this->selectedProduct[$key] = $transfer_item->product_id;
                $this->amount[$key] = $transfer_item->amount;
                $this->item_description[$key] = $transfer_item->product->description;
                $this->measurement[$key] = $transfer_item->product->unit_of_measure;
                if($this->department == "inventory"){
                      $this->serial_number[$key] = $transfer_item->inventory->serial_number;
                       $this->amount[$key] = $transfer_item->inventory->amount;
                        $this->weight[$key] = $transfer_item->inventory->weight;
                        $this->selectedCurrency = $transfer_item->inventory->currency_id;
                        $this->vendor_id = $transfer_item->inventory->vendor_id;
                }elseif($this->department == "tyre"){
                      $this->serial_number[$key] = $transfer_item->tyre->serial_number;
                       $this->amount[$key] = $transfer_item->tyre->amount;
                        $this->weight[$key] = $transfer_item->tyre->weight;
                         $this->selectedCurrency = $transfer_item->tyre->currency_id;
                        $this->vendor_id = $transfer_item->tyre->vendor_id;
                }
              
               
                $this->qty[$key] = $transfer_item->qty;
               

                if($transfer_item->tax_id){
                    $this->selectedTax[$key] = $transfer_item->tax_id;
                    $tax = Tax::find($transfer_item->tax_id);
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
            }else{
                $this->tax_rate[$key] = "";
            }
           
        }
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

      public function goodsReceivedNumber(){

     if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
 
        $goods_received = GoodsReceived::orderBy('id','desc')->first();

        if (!$goods_received) {
            $goods_received_number =  $initials .'GR'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $goods_received->id + 1;
            $goods_received_number =  $initials .'GR'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $goods_received_number;

    }

        
    public function createGRV(){

        $goods_received = new GoodsReceived;
        $goods_received->goods_received_number = $this->goodsReceivedNumber();
        $goods_received->user_id = Auth::user()->id;
        $goods_received->department = $this->department;
        $goods_received->vendor_id = $this->vendor_id;
        $goods_received->employee_id = Auth::user()->employee->id;
        $goods_received->date = $this->purchase_date;
        $goods_received->save();

        $this->selectedGoodsReceived = $goods_received->id;

        return $this->selectedGoodsReceived;
    }

    public function calculateExchangeAmount($total = null){
        if (($total && is_numeric($total)) && ($this->exchange_rate && is_numeric($this->exchange_rate)) ) {
            $this->exchange_amount = $this->exchange_rate * $total;
        }
    }



    public function store(){

            DB::transaction(function () {

        if (isset($this->selectedProduct)) {
            
            foreach ($this->selectedProduct as $key => $value) {

            if (isset($this->qty[$key])) {
                     
                    $inventory = new Inventory;
                    $inventory->user_id = Auth::user()->id;
                    $inventory->vendor_id = $this->vendor_id ? $this->vendor_id : NULL;
                    $inventory->currency_id = $this->selectedCurrency ? $this->selectedCurrency : null;

                    $subtotal = 0;
                    $subtotal_incl = 0;
                    $total = 0;

                    if ($this->selectedGoodsReceived) {
                      $inventory->goods_received_id = $this->selectedGoodsReceived;
                    }else{
                        $inventory->goods_received_id = $this->createGRV();
                    }
                    
                    if (isset($this->selectedProduct[$key])) {
                        $inventory->product_id = $this->selectedProduct[$key];
                    }
                    if (isset($this->selectedPurchaseProduct[$key])) {
                        $inventory->purchase_product_id = $this->selectedPurchaseProduct[$key];
                    }
                  
                    if (isset($this->serial_number[$key])) {
                        $inventory->serial_number = $this->serial_number[$key];
                    }
                    if (isset($this->amount[$key])) {
                        $inventory->amount = $this->amount[$key];
                    }
                    if (isset($this->cost[$key])) {
                        $inventory->cost = $this->cost[$key];
                    }
                    if (isset($this->qty[$key])) {
                        $inventory->qty = $this->qty[$key];
                    }
                    if (isset($this->measurement[$key])) {
                        $inventory->measurement = $this->measurement[$key];
                    }
                    if (isset($this->weight[$key]) && isset($this->qty[$key]) && (is_numeric($this->weight[$key]) && is_numeric($this->qty[$key]))    ) {
                        $balance = $this->weight[$key] * $this->qty[$key];
                        $inventory->weight = $this->weight[$key];
                        $inventory->balance = $balance;
                    }

                    if (isset($this->tax_rate[$key])) {
                        $inventory->tax_rate = $this->tax_rate[$key];
                    }
                    if (isset($this->selectedTax[$key])) {
                        $inventory->tax_id = $this->selectedTax[$key];
                    }

                     if (isset($this->qty[$key]) && isset($this->amount[$key]) && is_numeric($this->amount[$key]) && is_numeric($this->qty[$key])) {
                        if (isset($this->cost[$key]) && is_numeric($this->cost[$key])) {
                            $subtotal = ($this->qty[$key] * $this->amount[$key]) + $this->cost[$key];
                            $inventory->subtotal = $subtotal ;
                        }else{
                            $subtotal =($this->qty[$key] * $this->amount[$key]);
                            $inventory->subtotal = $subtotal;
                        }
                        
                    }
                    
                    if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {

                            $tax_amount = ($subtotal * ($this->tax_rate[$key] / 100 ));
                            $inventory->tax_amount = $tax_amount;
                            $total = $tax_amount + $subtotal;
                            $inventory->subtotal_incl = $total;
                            $inventory->total = $total;

                    }else{
                            $total = $subtotal;
                            $inventory->subtotal_incl =  $total;
                            $inventory->total =  $total;
                       
                    }

                    $this->calculateExchangeAmount($total);

                    $inventory->exchange_rate = $this->exchange_rate;
                    $inventory->exchange_amount = $this->exchange_amount;
                    $inventory->account_id = $this->selectedAccount;
                    $inventory->residual_value = $this->residual_value;
                    $inventory->store_id = $this->store_id ?? null;
                    $inventory->bin_id = $this->bin_id ?? null;
                    $inventory->rack_id = $this->rack_id ?? null;
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
                    $inventory->inventory_number = $this->inventoryNumber();
                    $inventory->warranty_exp_date = $this->warranty_exp_date;
                    $inventory->life = $this->life;
                    $inventory->description = $this->description;
                    $inventory->status = 1;
                    $inventory->disposed = 0;
                    $inventory->save();

                    
                    if ($this->to_bills == True) {
                      
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
                    $bill->vendor_id =  $inventory->vendor_id;
                    $bill->authorized_by_id = Auth::user()->id;
                    $bill->authorization = "pending";
                 
                    $bill->total = $inventory->total;
                    $bill->balance = $inventory->total;
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
            }
            Session::flash('success','Inventory Item(s) Added Successfully!!');
            return redirect(route('inventories.index'));
           
            
    
            }else {
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Select Product(s) to continue!!"
                ]);
            }
        });
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

    public function render()
    {

        
     
        $this->products = Product::with('brand')
        ->where('department', 'inventory')
        ->where('status', true)
        ->where('buy', true)
        ->get()
        ->sortBy([
            ['name', 'asc'],          // first sort by product name
            ['brand.name', 'asc'],    // then sort by brand name
        ]);
        $this->stores = Store::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->goods_receiveds = GoodsReceived::where('status',1)->where('department','inventory')->where('created_at', '>=', Carbon::now()->subMonth())->orderBy('created_at','desc')->get();
        $this->purchases = Purchase::where('department','inventory')->where('status',1)->where('created_at', '>=', Carbon::now()->subMonth())->where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->transfers = Transfer::where('department','inventory')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        return view('livewire.inventories.create',[
            'products' => $this->products,
            'stores' => $this->stores,
            'vendors' => $this->vendors,
            'purchases' => $this->purchases,
            'transfers' => $this->transfers,
          
        ]);
    }
}
