<?php

namespace App\Http\Livewire\Assets;


use Carbon\Carbon;
use App\Models\Bin;
use App\Models\Rack;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Branch;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\Attribute;
use App\Models\Department;
use App\Models\VendorType;
use App\Models\AssetDetail;
use App\Models\AssetSerial;
use App\Models\Measurement;
use App\Models\ExchangeRate;
use App\Models\AssetDocument;
use App\Models\CategoryValue;
use App\Models\GoodsReceived;
use Livewire\WithFileUploads;
use App\Models\AttributeValue;
use App\Models\PurchaseProduct;
use App\Models\ProductAttribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    use WithFileUploads;


    public $stores;
    public $store_id;
    public $purchases;
    public $selectedPurchase;
    public $selectedPurchaseProduct;
    public $purchase_products;
    public $goods_receiveds;
    public $selectedGoodsReceived;
    public $currencies;
    public $exchange_rate;
    public $exchange_amount;
    public $selectedCurrency;
    public $selected_currency;
    public $vendor_types;
    public $vendors;
    public $vendor_id;
    public $company;
  
    public $purchase_date;
    public $total;
    public $residual_value;
    public $weight = [];
    public $measurement = [];
    public $measurements;
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
    public $selectedProduct = [];
    public $serial_number = [];
    public $tax_rate = [];
    public $selectedTax = [];
    public $qty = [] ;
    public $item_description = [] ;
    public $amount = [];
    public $cost = [];
    public $tax_amount;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    public $selectedAccount;
  
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
        $this->company = Auth::user()->employee->company;
        $this->department = "asset";
       
        $this->stores = Store::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
       

        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
         $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
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

    public function assetNumber(){
       
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
            $asset = asset::orderBy('id', 'desc')->first();

        if (!$asset) {
            $asset_number =  $initials .'A'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $asset->id + 1;
            $asset_number =  $initials .'A'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $asset_number;


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
        $goods_received->vendor_id = $this->vendor_id;
        $goods_received->department = $this->department;
        $goods_received->employee_id = Auth::user()->employee->id;
        $goods_received->date = $this->purchase_date;
        $goods_received->save();

        $this->selectedGoodsReceived = $goods_received->id;
        
        return $this->selectedGoodsReceived;
    }

    public function updatedSelectedPurchase($id)
    {
        if (!is_null($id) ) {
        $this->selectedCurrency = Purchase::find($id)->currency_id;
        $this->vendor_id = Purchase::find($id)->vendor->id;
        $this->selectedAccount = Purchase::find($id)->account_id;
        $this->purchase_products = Purchase::find($id)->purchase_products;
        }
    }

    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$key] = $product->price;
                    $this->item_description[$key] = $product->description;
                }
                $this->qty[$key] = 1;
                $this->weight[$key] = 1;
               
          
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

      public function updatedSelectedPurchaseProduct($id, $key){
        if (!is_null($id)) {
            $purchase_product = PurchaseProduct::find($id);
            if (isset($purchase_product)) {
                 $this->selectedProduct[$key] = $purchase_product->product_id;
                $this->amount[$key] = $purchase_product->amount;
                $this->item_description[$key] = $purchase_product->product->description;
                $this->qty[$key] = $purchase_product->qty;
                $this->weight[$key] = 1;
                if($purchase_product->tax_id){
                    $this->selectedTax[$key] = $purchase_product->tax_id;
                    $tax = Account::find($purchase_product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$key] = $tax->rate;
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
            }else{
                $this->tax_rate[$key] = "";
            }
           
        }
    }



    public function store(){

        if (isset($this->selectedProduct)) {
            
            foreach ($this->selectedProduct as $key => $value) {
    
            if (isset($this->qty[$key])) {
    
                for ($i=0; $i < $this->qty[$key] ; $i++) { 
    
                    $asset = new Asset;
                    $asset->user_id = Auth::user()->id;
                    $asset->vendor_id = $this->vendor_id ? $this->vendor_id : NULL;
                    $asset->currency_id = $this->selectedCurrency ? $this->selectedCurrency : null;
                  
                    if ($this->selectedGoodsReceived) {
                      $asset->goods_received_id = $this->selectedGoodsReceived;
                    }else{
                        $asset->goods_received_id = $this->createGRV();
                    }
                    

                    if (isset($this->selectedProduct[$key])) {
                        $asset->product_id = $this->selectedProduct[$key];
                    }
                    if (isset($this->selectedAccount[$key])) {
                        $asset->account_id = $this->selectedAccount[$key];
                    }
                    if (isset($this->serial_number[$key])) {
                        $asset->serial_number = $this->serial_number[$key];
                    }
                    if (isset($this->amount[$key])) {
                        $asset->amount = $this->amount[$key];
                    }
                    if (isset($this->cost[$key])) {
                        $asset->cost = $this->cost[$key];
                    }
                    if (isset($this->qty[$key])) {
                        $asset->qty = $this->qty[$key];
                    }
                   
                    if (isset($this->amount[$key])) {
                        $asset->subtotal = $this->amount[$key];
                    }
                    if (isset($this->measurement[$key])) {
                        $asset->measurement = $this->measurement[$key];
                    }
                    if (isset($this->weight[$key])) {
                        $asset->weight = $this->weight[$key];
                        $asset->balance = $this->weight[$key];
                    }
                   
                    if (isset($this->tax_rate[$key])) {
                        $asset->tax_rate = $this->tax_rate[$key];
                    }
                    if (isset($this->selectedTax[$key])) {
                        $asset->tax_id = $this->selectedTax[$key];
                    }
                    if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {
                        if (isset($this->amount[$key])) {
                            $asset->tax_amount = ($this->amount[$key] * ($this->tax_rate[$key] / 100 ));
                            $asset->subtotal_incl = ($this->amount[$key] * ($this->tax_rate[$key] / 100 )) + $this->amount[$key];
                        }
                    }else{
                        if(isset($this->amount[$key])){
                            $asset->subtotal_incl = $this->amount[$key];
                        }
                    }
                   
                    $asset->residual_value = $this->residual_value;
                    $asset->store_id = $this->store_id ? $this->store_id : null;
                    $asset->depreciation_type = $this->depreciation_type;
                    $asset->purchase_date = $this->purchase_date;
                    $asset->purchase_type = $this->purchase_type;
                    $asset->purchase_id = $this->selectedPurchase ? $this->selectedPurchase : null;
                    $asset->condition = $this->condition;
                    $asset->asset_number = $this->assetNumber();
                    $asset->warranty_exp_date = $this->warranty_exp_date;
                    $asset->life = $this->life;
                    $asset->description = $this->description;
                    $asset->status = 1;
                    $asset->disposed = 0;
                    $asset->save();
    
                }
    
              }
            }
            Session::flash('success','Asset Added Successfully!!');
            return redirect(route('assets.index'));
           
            
    
            }else {
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Select Product(s) to continue!!"
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
    }

     public function updatedSelectedGoodsReceived($id){
        if(!is_null($id)){
            $goods_received = GoodsReceived::find($id);
            $this->vendor_id = $goods_received->vendor_id ?? null;
        }
    }

    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }
           $this->goods_receiveds = GoodsReceived::where('status',1)->where('department','asset')->where('created_at', '>=', Carbon::now()->subMonth())->orderBy('created_at','desc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->products = Product::with('brand')->orderBy('name','asc')->where('department','asset')->where('status',True)->where('buy',True)->get()->sortBy('brand.name');
        $this->stores = Store::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->purchases = Purchase::where('department','asset')->where('status',1)->where('created_at', '>=', Carbon::now()->subMonth())->where('authorization','approved')->orderBy('created_at','desc')->get();
        return view('livewire.assets.create',[
            'products' => $this->products,
            'stores' => $this->stores,
            'vendors' => $this->vendors,
            'purchases' => $this->purchases,
        ]);
    }
}
