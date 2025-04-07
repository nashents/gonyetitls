<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Tyre;
use App\Models\Horse;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Mileage;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\TyreCount;
use App\Models\TyreDetail;
use App\Models\TyreDispatch;
use App\Models\TyreDocument;
use Livewire\WithFileUploads;
use App\Models\TyreAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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


    
    public $selectedProduct = [];
    public $serial_number = [];
    public $tax_rate = [];
    public $selectedTax = [];
    public $qty = [] ;
    public $item_description = [] ;
    public $amount = [];
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

        $this->stores = Store::latest()->get();
        $this->tyre_assignments = TyreAssignment::latest()->get();
        $this->tyres = Tyre::where('status',1)->orderBy('tyre_number','asc')->get();
        $this->vehicles = Vehicle::where('status',1)->orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::where('status', 1)->orderBy('registration_number','asc')->get();
        $this->horses = Horse::where('status',1)->orderBy('registration_number','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->products = Product::orderBy('name','asc')->where('department','tyre')->where('status',True)->where('buy',True)->get();
        $this->purchases = Purchase::where('department','tyre')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();

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

    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$key] = $product->price;
                    $this->item_description[$key] = $product->description;
                }
                $this->qty[$key] = 1;
               
          
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

      public function store(){

          if (isset($this->selectedProduct)) {
            foreach ($this->selectedProduct as $key => $value) {
                $tyre = new Tyre;
                $tyre->user_id = Auth::user()->id;
               
                $tyre->product_id = $this->selectedProduct[$key];
            
                if (isset($this->serial_number[$key])) {
                    $tyre->serial_number = $this->serial_number[$key];
                }
                if (isset($this->type[$key])) {
                    $tyre->type = $this->type[$key];
                }
                if (isset($this->amount[$key])) {
                    $tyre->amount = $this->amount[$key];
                }
                if (isset($this->qty[$key])) {
                    $tyre->qty = $this->qty[$key];
                }
               
                if (isset($this->amount[$key])) {
                    $tyre->subtotal = $this->amount[$key];
                }
                if (isset($this->width[$key])) {
                    $tyre->width = $this->width[$key];
                }
                if (isset($this->thread_depth[$key])) {
                    $tyre->thread_depth = $this->thread_depth[$key];
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
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {
                    if (isset($this->amount[$key])) {
                        $tyre->tax_amount = ($this->amount[$key] * ($this->tax_rate[$key] / 100 ));
                        $tyre->subtotal_incl = ($this->amount[$key] * ($this->tax_rate[$key] / 100 )) + $this->amount[$key];
                    }
                }else{
                    if(isset($this->amount[$key])){
                        $tyre->subtotal_incl = $this->amount[$key];
                    }
                    
                }

                $tyre->tyre_number = $this->tyreNumber();
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
                $tyre->status = 1;
                $tyre->disposed = 0;

                $tyre->save();

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

          
          if ($this->tyre_assignment == True) {
            Session::flash('success','Tyre(s) Added & Assigned Successfully!!');
            return redirect()->route('tyres.index');
          }else {
            Session::flash('success','Tyre(s) Added Successfully');
            return redirect()->route('tyres.index');
          }

         
      }

    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }
        $this->products = Product::with('brand')->orderBy('name','asc')->where('department','tyre')->where('status',True)->where('buy',True)->get()->sortBy('brand.name');
        $this->purchases = Purchase::where('department','tyre')->where('status',1)->where('authorization','approved')->orderBy('created_at','desc')->get();
        return view('livewire.tyres.create',[
            'amount' =>   $this->amount,
            'purchases' => $this->purchases,
            'products' => $this->products
        ]);
    }
}
