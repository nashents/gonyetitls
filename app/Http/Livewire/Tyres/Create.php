<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Bin;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoodsReceived;
use App\Models\Horse;
use App\Models\Mileage;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\Rack;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Trailer;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Tyre;
use App\Models\TyreAssignment;
use App\Models\TyreCount;
use App\Models\TyreDetail;
use App\Models\TyreDispatch;
use App\Models\TyreDocument;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
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
    public $selectedPurchaseProduct;
    public $transfer_items;
    public $transfers;
    public $selectedTransfer;
    public $selectedTransferItem;
    public $source = "Purchase";
    public $selectedCurrency;
    public $selected_currency;
    public $date;
    public $weight;
    public $all_products = False;
    
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


    
    public $selectedProduct = [];
    public $measurement = [];
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
  
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;

    public $title;
    public $file;
    public $department;



    public $width;
    public $tyre_number;
    public $thread_depth;
    public $pressure_psi;
    public $life_span;
    public $aspect_ratio;
    public $diameter;
    public $company;


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

    public $documents_inputs = [];
    public $p = 1;
    public $o = 1;

    public $racks;
    public $bins;
    public $tyre_assignments;
    public $tyre_assignment_id;
    public $tyres;
    public $tyre_assignment;
    public $assignment_type = "Horse";
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
    public $selectedAccount;
    public $stores;
    public $store_id;
    public $to_bills = False;


    public function documentsAdd($p)
    {
        $p = $p + 1;
        $this->p = $p;
        array_push($this->documents_inputs ,$p);
    }

    public function documentsRemove($p)
    {
        unset($this->documents_inputs[$p]);
    }

    public function mount(){
        $this->department = "tyre";
        $this->company = Auth::user()->employee->company;
        $this->stores = Store::latest()->get();
        $this->tyre_assignments = TyreAssignment::latest()->get();
        $this->tyres = Tyre::where('status',1)->orderBy('tyre_number','asc')->get();
        $this->vehicles = Vehicle::where('status',1)->orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::where('status', 1)->orderBy('registration_number','asc')->get();
        $this->horses = Horse::where('status',1)->orderBy('registration_number','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->products = Product::query()
            ->where('status', true)
            ->when(!$this->all_products, function ($query) {
                $query->where('buy', true)->where('department', $this->department);
            })
            ->orderBy('name', 'asc')
            ->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();

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


        'assignment_type' => 'required',
        'trailer_id' => 'required',
        'vehicle_id' => 'required',
        'horse_id' => 'required',
        'tyre_id' => 'required',
        'starting_odometer' => 'required',
        'position' => 'required',
        'axle' => 'required',

        'selectedGoodsReceived' => 'required',
        'selectedCurrency' => 'required',

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

    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$key] = $product->price; 
                }
                $this->item_description[$key] = $product->description;
                $this->measurement[$key] = $product->unit_of_measure;
                $this->qty[$key] = 1;
               
          
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

      public function tyreNumber(){

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

            $tyre = Tyre::orderBy('id','desc')->first();

        if (!$tyre) {
            $tyre_number =  $initials .'TN'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $tyre->id + 1;
            $tyre_number =  $initials .'TN'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $tyre_number;


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

    

   public function calculateExchangeAmount($total = null){
        if (($total && is_numeric($total)) && ($this->exchange_rate && is_numeric($this->exchange_rate)) ) {
            $this->exchange_amount = $this->exchange_rate * $total;
        }
    }

      public function store(){

          $this->validate([
              'selectedGoodsReceived' => 'required',
              'selectedCurrency' => 'required',
          ]);

          DB::transaction(function () {

          if (isset($this->selectedProduct)) {
            foreach ($this->selectedProduct as $key => $value) {

                if (!isset($this->qty[$key]) || $this->qty[$key] < 1) {
                    continue;
                }

                for ($i = 0; $i < $this->qty[$key]; $i++) {

                $tyre = new Tyre;
                $tyre->user_id = Auth::user()->id;

                $subtotal = 0;
                $subtotal_incl = 0;
                $total = 0;
                $qty = 1;
              

                $tyre->goods_received_id = $this->selectedGoodsReceived;

                $goodsReceived = GoodsReceived::find($tyre->goods_received_id);
                $tyre->product_id = $this->selectedProduct[$key];
                $tyre->account_id = $this->selectedAccount;
                if (isset($this->selectedPurchaseProduct[$key])) {
                    $tyre->purchase_product_id = $this->selectedPurchaseProduct[$key];
                }
                if (isset($this->serial_number[$key])) {
                    $tyre->serial_number = $this->serial_number[$key];
                }
                if (isset($this->type[$key])) {
                    $tyre->type = $this->type[$key];
                }
                if (isset($this->amount[$key])) {
                    $tyre->amount = $this->amount[$key];
                }
                if (isset($this->measurement[$key])) {
                    $tyre->measurement = $this->measurement[$key];
                }
                if (isset($this->cost[$key])) {
                    $tyre->cost = $this->cost[$key];
                }
                $tyre->qty = $qty;
                $tyre->weight = $qty;
                $tyre->balance = $qty;

                if (isset($this->width[$key])) {
                    $tyre->width = $this->width[$key];
                }
                if (isset($this->thread_depth[$key])) {
                    $tyre->thread_depth = $this->thread_depth[$key];
                }
                if (isset($this->pressure_psi[$key])) {
                    $tyre->pressure_psi = $this->pressure_psi[$key];
                }
                if (isset($this->life_span[$key])) {
                    $tyre->life_span = $this->life_span[$key];
                }
                if (isset($this->diameter[$key])) {
                    $tyre->diameter = $this->diameter[$key];
                }
                if (isset( $this->aspect_ratio[$key])) {
                    $tyre->aspect_ratio = $this->aspect_ratio[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $tyre->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $tyre->tax_id = $this->selectedTax[$key];
                }
               
                if (isset($qty) && isset($this->amount[$key]) && (is_numeric($this->amount[$key]) && is_numeric($qty))) {
                    if (isset($this->cost[$key]) && is_numeric($this->cost[$key])) {
                        $subtotal = ($qty * $this->amount[$key]) + $this->cost[$key];
                        $tyre->subtotal = $subtotal ;
                    }else{
                        $subtotal =($qty * $this->amount[$key]);
                        $tyre->subtotal = $subtotal;
                    }
                
                }
            
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {

                        $tax_amount = ($subtotal * ($this->tax_rate[$key] / 100 ));
                        $tyre->tax_amount = $tax_amount;
                        $total = $tax_amount + $subtotal;
                        $tyre->subtotal_incl = $total;
                        $tyre->total = $total;

                }else{
                        $total = $subtotal;
                        $tyre->subtotal_incl =  $total;
                        $tyre->total =  $total;
                    
                }
                $this->calculateExchangeAmount($total);

                $tyre->exchange_rate = $this->exchange_rate;
                $tyre->exchange_amount = $this->exchange_amount;

                $tyre->tyre_number = $this->tyreNumber();
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
                $tyre->status = ($goodsReceived && $goodsReceived->authorization === 'pending') ? 0 : 1;
                $tyre->disposed = 0;

                $tyre->save();

                    if ($this->to_bills == True) {
                      
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
                    $bill->vendor_id =  $tyre->vendor_id;
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

                if ($this->tyre_assignment == True) {

                $assignment = new TyreAssignment;
                $assignment->user_id = Auth::user()->id;
                $assignment->tyre_id = $tyre->id;
                $assignment->type = $this->assignment_type;

                if ($this->assignment_type == "Horse") {
                    $assignment->horse_id = $this->horse_id;
                    $assignment->vehicle_id = null;
                    $assignment->trailer_id = null;
                }elseif ($this->assignment_type == "Trailer") {
                    $assignment->trailer_id = $this->trailer_id;
                    $assignment->horse_id = null;
                    $assignment->vehicle_id = null;
                }elseif ($this->assignment_type == "Vehicle") {
                    $assignment->vehicle_id = $this->vehicle_id;
                    $assignment->horse_id = null;
                    $assignment->trailer_id = null;
                }
                $assignment->starting_odometer = $this->starting_odometer;
                $assignment->position = $this->position;
                $assignment->axle = $this->axle;
                $assignment->status = 1;
                $assignment->save();

                $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);
                $movement->user_id = $assignment->user_id;
                $movement->tyre_id = $assignment->tyre_id;
                
                if ($assignment->horse_id) {
                    $movement->location = 'Horse';
                    $movement->horse_id = $assignment->horse_id;
                } elseif ($assignment->vehicle_id) {
                    $movement->location = 'Vehicle';
                    $movement->vehicle_id = $assignment->vehicle_id;
                } elseif ($assignment->trailer_id) {
                    $movement->location = 'Trailer';
                    $movement->trailer_id = $assignment->vehicle_id;
                }
                
                $movement->current_mileage = $assignment->current_mileage;
                $movement->mileage_moved = $assignment->starting_odometer;
                $movement->date =   $assignment->date_fitted;
                $movement->save();

                $mileage = new Mileage;
                $mileage->user_id = Auth::user()->id;
                $mileage->tyre_assignment_id = $assignment->id;
                $mileage->horse_id = $this->horse_id ? $this->horse_id : Null;
                $mileage->vehicle_id = $this->vehicle_id ? $this->vehicle_id : Null;
                $mileage->trailer_id = $this->trailer_id ? $this->trailer_id : Null;
                $mileage->mileage = $this->starting_odometer;
                $mileage->date = date('Y-m-d');
                $mileage->category = "Tyre Assignment";
                $mileage->save();
        
                $tyre = Tyre::find($tyre->id);
                $dispatch = new TyreDispatch;
                $dispatch->tyre_assignment_id = $assignment->id;
                $dispatch->tyre_id = $tyre->id;
                $dispatch->tyre_number = $tyre->tyre_number;
                $dispatch->serial_number = $tyre->serial_number;
                $dispatch->width = $tyre->width;
                $dispatch->aspect_ratio = $tyre->aspect_ratio;
                $dispatch->diameter =  $tyre->diameter;
                $dispatch->horse_id = $this->horse_id;
                $dispatch->vehicle_id = $this->vehicle_id;
                $dispatch->trailer_id = $this->trailer_id;
                $dispatch->save();
        
                $tyre = Tyre::find($tyre->id);
                $tyre->status = 0;
                $tyre->update();
                }

                }

              }

          }

          
          if ($this->tyre_assignment == True) {
            Session::flash('success','Tyre(s) Added & Assigned Successfully!!');
            return redirect()->route('tyres.index');
          }else {
            Session::flash('success','Tyre(s) Added Successfully');
            return redirect()->route('tyres.index');
          }

        });
         
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
        }
        elseif($category == "goods_receiveds"){
            $this->goods_receiveds = GoodsReceived::orderBy('id','desc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"GRVs Refreshed Successfully!!."
            ]);
        }
        elseif($category == "tyre_purchases"){
            $this->purchases = Purchase::where('department', 'tyre')
            ->where('status', 1)
            ->where('authorization', 'approved')
            ->where(function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subMonth())
                    ->orWhere('star', true);
            })
            ->orderBy('star', 'desc')          // Starred POs first
            ->orderBy('created_at', 'desc')
            ->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Orders Refreshed Successfully!!."
            ]);
        }
        elseif($category == "products"){
          $this->products = Product::query()
            ->where('status', true)
            ->when(!$this->all_products, function ($query) {
                $query->where('buy', true)->where('department', $this->department);
            })
            ->orderBy('name', 'asc')
            ->get();
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

     public function updatedSelectedGoodsReceived($id){
        if(!is_null($id)){
            $goods_received = GoodsReceived::find($id);
            $this->vendor_id = $goods_received->vendor_id ?? null;
        }
    }

    public function render()
    {

        
        $this->goods_receiveds = GoodsReceived::where('status',1)->where('department','tyre')->where('created_at', '>=', Carbon::now()->subMonth())->orderBy('created_at','desc')->get();
        $this->products = Product::query()
            ->where('status', true)
            ->when(!$this->all_products, function ($query) {
                $query->where('buy', true)->where('department', $this->department);
            })
            ->orderBy('name', 'asc')
            ->get();
          $this->transfers = Transfer::where('department','tyre')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->purchases = Purchase::where('department', 'tyre')
            ->where('status', 1)
            ->where('authorization', 'approved')
            ->where(function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subMonth())
                    ->orWhere('star', true);
            })
            ->orderBy('star', 'desc')          // Starred POs first
            ->orderBy('created_at', 'desc')
            ->get();
        return view('livewire.tyres.create',[
            'amount' =>   $this->amount,
            'purchases' => $this->purchases,
            'products' => $this->products,
            'transfers' => $this->transfers
        ]);
    }
}
