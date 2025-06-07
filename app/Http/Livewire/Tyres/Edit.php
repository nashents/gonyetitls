<?php

namespace App\Http\Livewire\Tyres;

use Carbon\Carbon;
use App\Models\Bin;
use App\Models\Rack;
use App\Models\Tyre;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Product;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\TyreDetail;
use App\Models\GoodsReceived;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
  use WithFileUploads;

  public $products;
  public $condition;
  public $currencies;
  public $vendors;
  public $exchange_rate;
  public $exchange_amount;
  public $vendor_id;
  public $goods_receiveds;
  public $selectedGoodsReceived;
  public $selectedCurrency;
  public $selected_currency;
  public $date;
  public $total;
  public $purchase_date;
  public $type;
  public $quantity;
  public $residual_value;
  public $life;
  public $purchase_type;
  public $depreciation_type;
  public $warranty_exp_date;
  public $purchase_products;
  public $selectedPurchase;
  public $purchases;
  public $description;

  public $assigned;


  
  public $selectedProduct ;
  public $serial_number ;
  public $tax_rate ;
  public $selectedTax ;
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

  public $title;
  public $file;



  public $width;
  public $tyre_number;
  public $thread_depth;
  public $life_span;
  public $aspect_ratio;
  public $diameter;


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

  public $documents_inputs ;
  public $p = 1;
  public $o = 1;

  public $tyre_assignments;
  public $tyre_assignment_id;
  public $tyres;
  public $tyre_assignment;
  public $assignment_type = NULL;
  public $tyre_id;
  public $horses;
  public $horse_id;
  public $vehicles;
  public $vehicle_id;
  public $trailers;
  public $trailer_id;
  public $position;
  public $axle;
  public $starting_odometer;
  public $ending_odometer;
  public $status;
  public $stores;
  public $store_id;

    public function mount($id){

        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
          return $query->where('name','Expenses');
      })->orderBy('name','asc')->get();
      $this->income_accounts = Account::whereHas('account_type', function($q){
          $q->where('name', 'Income');
      })->orderBy('name','asc')->get();
      $this->tax_accounts = Account::whereHas('account_type', function ($query) {
          return $query->where('name','Sales Taxes');
      })->orderBy('name','asc')->get();
        $tyre = Tyre::find($id);
        $this->tyre = $tyre;
        $this->tyre_id = $id;
        $this->width = $tyre->width;
        $this->type = $tyre->type;
        $this->qty = $tyre->qty;
        $this->diameter = $tyre->diameter;
        $this->thread_depth = $tyre->thread_depth;
        $this->life_span = $tyre->life_span;
        $this->aspect_ratio = $tyre->aspect_ratio;
        $this->selectedTax = $tyre->tax_id;
        $tax = Account::find($this->selectedTax);
        if (isset($tax)) {
            $this->tax_rate = $tax->rate;
        }else{
            $this->tax_rate = "";
        }
        $this->tax_rate = $tyre->tax_rate;
        $this->selectedProduct = $tyre->product_id;
        $this->serial_number = $tyre->serial_number;
        $this->status = $tyre->status;
        $this->stores = Store::latest()->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->products = Product::orderBy('name','asc')->where('department','tyre')->where('status',True)->where('buy',True)->get();
        $this->purchases = Purchase::where('department','tyre')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->store_id =  $tyre->store_id;
        $this->vendor_id = $tyre->vendor_id;
        $this->selectedCurrency = $tyre->currency_id;
        $this->amount = $tyre->amount;
        $this->total = $tyre->total;
        $this->purchase_date = $tyre->purchase_date;
        $this->tyre_number = $tyre->tyre_number;
        $this->condition = $tyre->condition;
        $this->residual_value = $tyre->residual_value;
        $this->life = $tyre->life;
        $this->warranty_exp_date = $tyre->warranty_exp_date;
        $this->purchase_type = $tyre->purchase_type;
        $this->depreciation_type = $tyre->depreciation_type;
        $this->description = $tyre->description;
        $this->selectedPurchase = $tyre->purchase_id;
        if ($this->selectedPurchase) {
          $this->vendor_id = Purchase::find($this->selectedPurchase)->vendor->id;
          $this->selectedCurrency = Purchase::find($this->selectedPurchase)->currency->id;
          $this->purchase_products = Purchase::find($this->selectedPurchase)->purchase_products;
        }
       

      }
      public function updated($value){
          $this->validateOnly($value);
      }
      protected $messages =[

        'selectedProduct.*.required' => 'Product field is required',
        'aspect_ratio.*.required' => 'Aspect Ratio field is required',
        'serial_number.*.required' => 'Serial Number field is required',
        'selectedProduct.0.required' => 'Product field is required',
        'aspect_ratio.0.required' => 'Aspect Ratio field is required',
        'serial_number.0.required' => 'Serial Number field is required',
        'vendor_id.required' => 'Select Vendor',

    ];
      protected $rules = [
        'aspect_ratio.*' => 'required',
        'width.*' => 'required',
        'diameter.*' => 'required',
        'serial_number.*' => 'required',
        'type.*' => 'required',
        'selectedProduct.*' => 'required',
        'aspect_ratio.0' => 'required',
        'type.0' => 'required',
        'width.0' => 'required',
        'diameter.0' => 'required',
        'serial_number.0' => 'required',
        'selectedProduct.0' => 'required',
        'vendor_id' => 'required',
      ];

      public function updatedSelectedPurchase($id)
    {
        if (!is_null($id) ) {
        $this->selectedCurrency = Purchase::find($id)->currency_id;
        $this->vendor_id = Purchase::find($id)->vendor->id;
        $this->selectedAccount = Purchase::find($id)->account_id;
        $this->purchase_products = Purchase::find($id)->purchase_products;
        }
    }

    public function updatedSelectedCurrency($id){
      if(!is_null($id)){
          $this->selected_currency = Currency::find($id);
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
            $this->qty= 1;
           
      
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


      public function update(){

              $tyre = Tyre::find($this->tyre_id);
              $tyre->user_id = Auth::user()->id;
              $tyre->goods_received_id = $this->selectedGoodsReceived ? $this->selectedGoodsReceived : null;
              $tyre->product_id = $this->selectedProduct;
              
              $tyre->serial_number = $this->serial_number;
              $tyre->type = $this->type;
              $tyre->qty = $this->qty;
              $tyre->amount = $this->amount;
              $tyre->subtotal = $this->qty * $this->amount;
  
              $tyre->tax_rate = $this->tax_rate;
              $tyre->tax_id = $this->selectedTax;

              $tax = Account::find($this->selectedTax);
              if (isset($tax)) {
                  $this->tax_rate = $tax->rate;
              }else{
                  $this->tax_rate = "";
              }
             
              if (isset($this->tax_rate) && is_numeric($this->tax_rate) && isset($this->selectedTax)) {
                if (isset($this->amount)) {
                    $tyre->tax_amount = ($this->amount * ($this->tax_rate / 100 ));
                    $tyre->subtotal_incl = ($this->amount * ($this->tax_rate / 100 )) + $this->amount;
                }
            }else{
                $tyre->tax_amount = 0;
                $tyre->subtotal_incl = $this->amount;
            }

              $tyre->width = $this->width;
              $tyre->diameter = $this->diameter;
              $tyre->thread_depth = $this->thread_depth;
              $tyre->life_span = $this->life_span;
              $tyre->aspect_ratio = $this->aspect_ratio;
              $tyre->tyre_number = $this->tyre_number;
              $tyre->currency_id = $this->selectedCurrency;
              $tyre->store_id = $this->store_id;
              $tyre->purchase_id = $this->selectedPurchase;
              $tyre->vendor_id = $this->vendor_id;
              $tyre->condition = $this->condition;
              $tyre->description = $this->description;
              $tyre->depreciation_type = $this->depreciation_type;
              $tyre->purchase_date = $this->purchase_date;
              $tyre->purchase_type = $this->purchase_type;
              $tyre->purchase_id = $this->selectedPurchase ? $this->selectedPurchase : null;
              $tyre->warranty_exp_date = $this->warranty_exp_date;
              $tyre->life = $this->life;
              $tyre->residual_value = $this->residual_value;
              $tyre->description = $this->description;
              $tyre->status = $this->status;
              $tyre->disposed = 0;

              $tyre->update();

        Session::flash('success','Tyre(s) added successfully');
        return redirect()->route('tyres.index');
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
       $this->goods_receiveds = GoodsReceived::where('status',1)->where('department','tyre')->where('created_at', '>=', Carbon::now()->subMonth())->orderBy('created_at','desc')->get();
    $this->products = Product::orderBy('name','asc')->where('department','tyre')->where('status',True)->where('buy',True)->get();
      $this->purchases = Purchase::where('department','tyre')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        return view('livewire.tyres.edit',[
          'purchases' => $this->purchases,
          
        ]);
    }
}
