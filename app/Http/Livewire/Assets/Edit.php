<?php

namespace App\Http\Livewire\Assets;

use App\Models\Bin;
use App\Models\Rack;
use App\Models\Asset;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\VendorType;
use App\Models\Measurement;
use App\Models\CategoryValue;
use App\Models\GoodsReceived;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithFileUploads;


    public $stores;
    public $store_id;
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
  
    public $purchase_date;
    public $total;
    public $residual_value;
    public $weight ;
    public $measurement ;
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
    public $selectedProduct ;
    public $serial_number ;
    public $tax_rate ;
    public $selectedTax ;
    public $selectedAccount ;
    public $qty  ;
    public $item_description  ;
    public $amount ;
    public $tax_amount;
    public $tax_id;
    public $tax;
    public $tax_accounts;
  
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
        $this->products = Product::where('department','asset')->orderBy('name','asc')->get();
        $this->category_values = CategoryValue::orderBy('name','asc')->get();
        $this->vendor_types = VendorType::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::latest()->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->categories = Category::orderBy('name','asc')->get();
        $this->purchases = Purchase::where('department','asset')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->stores = Store::orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
         $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->vendor_id = $asset->vendor_id;
        $this->selectedVendorType = $asset->vendor_type_id;
        $this->selectedCurrency = $asset->currency_id;
        $this->selectedProduct = $asset->product_id;
        $this->selectedAccount = $asset->account_id;
        $this->purchase_date = $asset->purchase_date;
        $this->selectedPurchase = $asset->purchase_id;
        $this->qty = $asset->qty;
        $this->amount = $asset->amount;
        $this->tax_amount = $asset->tax_amount;
        $this->subtotal = $asset->subtotal;
        $this->subtotal_incl = $asset->subtotal_incl;
        $this->selectedTax = $asset->tax_id;
        $this->residual_value = $asset->residual_value;
        $this->serial_number = $asset->serial_number;
        $this->purchase_type = $asset->purchase_type;
        $this->description = $asset->description;
        $this->depreciation_type = $asset->depreciation_type;
        $this->asset_number = $asset->asset_number;
        $this->measurement = $asset->measurement;
        $this->weight = $asset->weight;
        $this->store_id = $asset->store_id;
        $this->balance = $asset->balance;
        $this->condition = $asset->condition;
        $this->warranty_exp_date = $asset->warranty_exp_date;
        $this->life = $asset->life;
        $this->status = $asset->status;
        $this->asset_id = $asset->id;
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

    public function updatedSelectedProduct($id){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount = $product->price;
                    $this->item_description = $product->description;
                }
                $this->qty = 1;
                $this->weight = 1;
               
          
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
    }

    

    public function updatedSelectedCurrency($id){
        if(!is_null($id)){
            $this->selected_currency = Currency::find($id);
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedProduct' => 'required',
        'purchase_date' => 'required',
    ];



    public function update(){

                    $asset =  Asset::find($this->asset_id);
                    $asset->vendor_id = $this->vendor_id ? $this->vendor_id : NULL;
                    $asset->currency_id = $this->selectedCurrency ? $this->selectedCurrency : null;
                    $asset->goods_received_id = $this->selectedGoodsReceived ? $this->selectedGoodsReceived : null;
                    $asset->product_id = $this->selectedProduct;
                    $asset->account_id = $this->selectedAccount;
                    $asset->serial_number = $this->serial_number;
                    $asset->amount = $this->amount;
                    $asset->qty = $this->qty;
                    $asset->subtotal = $this->amount;
                    $asset->measurement = $this->measurement;
                    $asset->weight = $this->weight;
                    $asset->balance = $this->weight;
                    $asset->tax_rate = $this->tax_rate;
                    $asset->tax_id = $this->selectedTax;
                    if (isset($this->tax_rate) && is_numeric($this->tax_rate) && isset($this->selectedTax)) {
                        if (isset($this->amount)) {
                            $asset->tax_amount = ($this->amount * ($this->tax_rate / 100 ));
                            $asset->subtotal_incl = ($this->amount * ($this->tax_rate / 100 )) + $this->amount;
                        }
                    }else{
                        $asset->tax_amount = 0;
                        $asset->subtotal_incl = $this->amount;
                    }
                   
                    $asset->residual_value = $this->residual_value;
                    $asset->store_id = $this->store_id ? $this->store_id : null;
                    $asset->depreciation_type = $this->depreciation_type;
                    $asset->purchase_date = $this->purchase_date;
                    $asset->purchase_type = $this->purchase_type;
                    $asset->purchase_id = $this->selectedPurchase ? $this->selectedPurchase : null;
                    $asset->condition = $this->condition;
                    $asset->warranty_exp_date = $this->warranty_exp_date;
                    $asset->life = $this->life;
                    $asset->description = $this->description;
                    $asset->status = 1;
                    $asset->disposed = 0;
                    $asset->update();

                    return redirect(route('assets.index'));
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'success',
                        'message'=>"Asset Updated Successfully!!"
                    ]);
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
        $this->products = Product::where('department','asset')->orderBy('name','asc')->get();
        $this->vendor_types = VendorType::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->purchases = Purchase::where('department','asset')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        return view('livewire.assets.edit',[
            'products' => $this->products,
            'vendor_types' => $this->vendor_types,
            'vendors' => $this->vendors,
            'purchases' => $this->purchases,
        ]);
    }
}
