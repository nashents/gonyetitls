<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Bin;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoodsReceived;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\Rack;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Tyre;
use App\Models\TyreDetail;
use App\Models\Vehicle;
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
  public $selectedPurchaseProduct;
    public $transfer_items;
    public $transfers;
    public $selectedTransfer;
    public $selectedTransferItem;
    public $source = "Purchase";
  public $purchases;
  public $description;
  public $company;

  public $assigned;


  public $tyre;
  public $racks;
  public $bins;
  public $selectedProduct ;
  public $measurement ;
  public $serial_number ;
  public $tax_rate ;
  public $selectedTax ;
  public $qty;
  public $item_description  ;
  public $amount ;
  public $cost ;
  public $tax_amount;
  public $tax_id;
  public $tax;
  public $tax_accounts;
  public $weight;

  public $income_accounts;
  public $expense_accounts;
  public $income_account_id;
  public $expense_account_id;

  public $title;
  public $file;



  public $width;
  public $tyre_number;
  public $thread_depth;
  public $pressure_psi;
  public $life_span;
  public $aspect_ratio;
  public $diameter;
  public $to_bills;


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
  public $selectedAccount;

    public function mount($id){
          $this->company = Auth::user()->employee->company;
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
          return $query->where('name','Expenses');
      })->orderBy('name','asc')->get();
      $this->income_accounts = Account::whereHas('account_type', function($q){
          $q->where('name', 'Income');
      })->orderBy('name','asc')->get();
       $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
        $tyre = Tyre::find($id);
        $this->tyre = $tyre;
        $this->tyre_id = $id;
        $this->width = $tyre->width;
        $this->type = $tyre->type;
        
        $this->diameter = $tyre->diameter;
        $this->thread_depth = $tyre->thread_depth;
        $this->pressure_psi = $tyre->pressure_psi;
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
        $this->to_bills = $tyre->bill ? True : False;
        $this->selectedProduct = $tyre->product_id;
        $this->measurement = $tyre->measurement;
        $this->serial_number = $tyre->serial_number;
        $this->status = $tyre->status;
        $this->stores = Store::latest()->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->store_id =  $tyre->store_id;
      
        $this->selectedAccount = $tyre->account_id;
        $this->selectedPurchaseProduct = $tyre->purchase_product_id;
      
        $this->amount = $tyre->amount;
        $this->qty = $tyre->qty;
        $this->cost = $tyre->cost;
        $this->purchase_date = $tyre->purchase_date ? Carbon::parse($tyre->purchase_date)->format('Y-m-d') : Null;
        $this->tyre_number = $tyre->tyre_number;
        $this->condition = $tyre->condition;
        $this->residual_value = $tyre->residual_value;
        $this->life = $tyre->life;
        $this->warranty_exp_date = $tyre->warranty_exp_date;
        $this->purchase_type = $tyre->purchase_type;
        $this->depreciation_type = $tyre->depreciation_type;
        $this->description = $tyre->description;
       
        $this->selectedGoodsReceived = $tyre->goods_received_id;
        $this->exchange_rate = $tyre->exchange_rate;
        $this->source = $tyre->purchase_id ? "Purchase" : ($tyre->transfer_id ? "Transfer" : "");
        $this->selectedTransfer = $tyre->transfer_id;
        $this->selectedPurchase = $tyre->purchase_id;
        if ($this->selectedPurchase) {
          $this->vendor_id = Purchase::find($this->selectedPurchase)->vendor->id;
          $this->selectedCurrency = Purchase::find($this->selectedPurchase)->currency->id;
          $this->purchase_products = Purchase::find($this->selectedPurchase)->purchase_products;
          $this->selected_currency = Currency::find($this->selectedCurrency);
        }else{
            $this->vendor_id = $tyre->vendor_id;
            $this->selectedCurrency = $tyre->currency_id;
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
                $this->selectedProduct= $transfer_item->product_id;
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
              
               
                $this->qty= $transfer_item->qty;
               

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
            ->where('department', 'tyre')
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

      public function calculateExchangeAmount($total){
        if (($total && is_numeric($total)) && ($this->exchange_rate && is_numeric($this->exchange_rate)) ) {
            $this->exchange_amount = $this->exchange_rate * $total;
        }
    }

    public function update(){

        DB::transaction(function () {

              $subtotal = 0;
              $subtotal_incl = 0;
              $total = 0;
              $tyre = Tyre::find($this->tyre_id);
              $tyre->user_id = Auth::user()->id;
              $tyre->goods_received_id = $this->selectedGoodsReceived ? $this->selectedGoodsReceived : null;
              $tyre->product_id = $this->selectedProduct;
              $tyre->purchase_product_id = $this->selectedPurchaseProduct;
              $tyre->serial_number = $this->serial_number;
              $tyre->type = $this->type;
              $tyre->qty = $this->qty;
              $tyre->weight = $this->qty;
              $tyre->balance = $this->qty;
              $tyre->amount = $this->amount;
              $tyre->cost = $this->cost;
              $tyre->measurement = $this->measurement;
              $tyre->tax_rate = $this->tax_rate;
              $tyre->tax_id = $this->selectedTax;

               

            if(isset($this->qty) && is_numeric($this->qty) && isset($this->amount) && is_numeric($this->amount) ){
            if (isset($this->cost) && is_numeric($this->cost)) {
                $subtotal = ($this->qty * $this->amount) + $this->cost;
                $tyre->subtotal = $subtotal ;
            }else{
                $subtotal = ($this->qty * $this->amount);
                $tyre->subtotal = $subtotal;
            }
            }
        
            if (isset($this->tax_rate) && is_numeric($this->tax_rate) && isset($this->selectedTax)) {
                    $tax_amount = ($subtotal * ($this->tax_rate / 100 ));
                    $tyre->tax_amount = $tax_amount;
                    $total = $tax_amount + $subtotal;
                    $tyre->subtotal_incl =  $total;
                    $tyre->total =  $total;
            }else{
                $total = $subtotal;
                $tyre->subtotal_incl = $total;
                $tyre->total = $total;
            }

        $this->calculateExchangeAmount($total);

              $tyre->exchange_rate = $this->exchange_rate;
              $tyre->exchange_amount = $this->exchange_amount;

              $tyre->width = $this->width;
              $tyre->account_id = $this->selectedAccount;
              $tyre->diameter = $this->diameter;
              $tyre->thread_depth = $this->thread_depth;
              $tyre->pressure_psi = $this->pressure_psi;
              $tyre->life_span = $this->life_span;
              $tyre->aspect_ratio = $this->aspect_ratio;
              $tyre->tyre_number = $this->tyre_number;
              $tyre->currency_id = $this->selectedCurrency;
              $tyre->store_id = $this->store_id;
              $tyre->vendor_id = $this->vendor_id;
              $tyre->condition = $this->condition;
              $tyre->description = $this->description;
              $tyre->depreciation_type = $this->depreciation_type;
              $tyre->purchase_date = $this->purchase_date;
              $tyre->purchase_type = $this->purchase_type;

              $tyre->purchase_id = null;
              $tyre->transfer_id = null;

              if ($this->source === 'Purchase') {
                $tyre->purchase_id = $this->selectedPurchase;
              }

              if ($this->source === 'Transfer') {
                $tyre->transfer_id = $this->selectedTransfer;
              }
              
              $tyre->warranty_exp_date = $this->warranty_exp_date;
              $tyre->life = $this->life;
              $tyre->residual_value = $this->residual_value;
              $tyre->description = $this->description;
              $tyre->status = $this->status;
              $tyre->disposed = 0;

              $tyre->update();

              if ($this->to_bills) {
            
            $bill = $tyre->bill;

            if($bill){     

            $bill->tyre_id = $tyre->id;
            $bill->category = "Inventory Item";
            $bill->bill_date = $tyre->purchase_date;
            $bill->account_id = $tyre->account_id;
            $account = Account::find($tyre->account_id);
            $account_type = $account ?  $account->account_type : "";
            if (isset($account_type)) {
                $bill->account_type_id = $account_type->id;
            }
            $bill->currency_id = $tyre->currency_id;
            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = "pending";
         
            $bill->total = $tyre->subtotal_incl;
            $bill->balance = $tyre->subtotal_incl;
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
                    $bill_expense->product_id = $tyre->product_id;
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $tyre->amount;
                    $bill_expense->subtotal = $tyre->subtotal;
                    $bill_expense->tax_amount = $tyre->tax_amount;
                    $bill_expense->subtotal_incl = $tyre->subtotal_incl;
                    $bill_expense->update();
                }
            }else{
                $bill = new Bill;
                $bill->user_id = Auth::user()->id;
                $bill->bill_number = $this->billNumber();
                $bill->tyre_id = $tyre->id;
                $bill->category = "Tyre";
                $bill->bill_date = $tyre->purchase_date;
                $bill->account_id = $tyre->account_id;
                $account = Account::find($tyre->account_id);
                $account_type = $account ?  $account->account_type : "";
                if (isset($account_type)) {
                    $bill->account_type_id = $account_type->id;
                }
                $bill->currency_id = $tyre->currency_id;
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = "pending";
             
                $bill->total = $tyre->subtotal_incl;
                $bill->balance = $tyre->subtotal_incl;
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
                $bill_expense->product_id = $tyre->product_id;
                $bill_expense->qty = 1;
                $bill_expense->amount = $tyre->amount;
                $bill_expense->subtotal = $tyre->subtotal;
                $bill_expense->tax_amount = $tyre->tax_amount;
                $bill_expense->subtotal_incl = $tyre->subtotal_incl;
                $bill_expense->save();
            }
        }

        Session::flash('success','Tyre(s) added successfully');
        return redirect()->route('tyres.index');

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

     
       $this->goods_receiveds = GoodsReceived::where('status',1)->where('department','tyre')->where('created_at', '>=', Carbon::now()->subMonth())->orderBy('created_at','desc')->get();
        $this->products = Product::with('brand')
        ->where('department', 'tyre')
        ->where('status', true)
        ->where('buy', true)
        ->get()
        ->sortBy([
            ['name', 'asc'],          // first sort by product name
            ['brand.name', 'asc'],    // then sort by brand name
        ]);
         $this->transfers = Transfer::where('department','tyre')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
       $this->purchases = Purchase::where('department','tyre')->where('status',1)->where('created_at', '>=', Carbon::now()->subMonth())->where('authorization','approved')->orderBy('created_at','desc')->get();
        return view('livewire.tyres.edit',[
          'purchases' => $this->purchases,
          'transfers' => $this->transfers,
          
        ]);
    }
}
