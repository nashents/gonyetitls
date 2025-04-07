<?php

namespace App\Http\Livewire\Quotations;

use App\Models\Cargo;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\BankAccount;
use App\Models\Destination;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Models\QuotationProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    public $quotations;
    public $quotation_number;
    public $quotation_id;
    public $customers;
    public $selectedCustomer;
    public $company_id;
    public $loading_points;
    public $loading_point_id;
    public $offloading_points;
    public $offloading_point_id;
    public $bank_accounts;
    public $bank_account_id;
    public $cargos;
    public $selectedCargo;
    public $initials;
    public $currencies;
    public $destinations;
    public $currency_id;
    public $vat;
    public $date;
    public $expiry;
    public $memo;
    public $qty;
    public $footer;
    public $subtotal = 0;
    public $total = 0;
  
    public $weight;
    public $rate;
    public $freight;
    public $exchange_rate;
    public $exchange_amount;


    public $user_id;

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


public function updatedSelectedCustomer($id){
    $this->selectedCustomer = $id;
    $this->customer = Customer::find($id);
    $this->initials = $this->customer->initials;
    $this->quotation_number = $this->quotationNumber();
    $this->currency_id = $this->customer->currency ? $this->customer->currency->id  : NULL;
}

public function updatedSelectedCargo($id){
    if (!is_null($id)) {
        $this->cargo_type = Cargo::find($id)->type;
    }
}

public function quotationNumber(){
    
    if (Auth::user()->employee->company->quotation_serialize_by_customer == TRUE) {

        if (isset($this->initials) &&  $this->initials != NULL) {
        }else {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $this->initials = $words[0][0].$words[1][0];
            }else {
                $this->initials = $words[0][0];
            }
        }

        $quotation = Quotation::where('customer_id', $this->selectedCustomer)->orderBy('id', 'desc')->get()->first();

        if (!$quotation) {
            $this->number = 1;
            $quotation_number =  $this->initials .'Q'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            if ($quotation->number) {
                $this->number = $quotation->number + 1;
            }else{
                $this->number = $quotation->id + 1;
            }
           
            $quotation_number =  $this->initials .'Q'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $quotation_number;
    }else {

        $str = Auth::user()->employee->company->name;
        $words = explode(' ', $str);
        if (isset($words[1][0])) {
            $this->initials = $words[0][0].$words[1][0];
        }else {
            $this->initials = $words[0][0];
        }

        $quotation = Quotation::orderBy('id','desc')->first();
        if (!$quotation) {
            $this->number = 1;
            $quotation_number =  $this->initials .'Q'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $this->number = $quotation->id + 1;
            $quotation_number =  $this->initials .'Q'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $quotation_number;
    }

   


}
    public function mount(){
       
        $this->quotation_number = $this->quotationNumber();
        $this->bank_accounts = BankAccount::orderBy('name','asc')->get();
        $this->quotations = Quotation::latest()->get();
        $this->currencies = Currency::latest()->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        
        if (Auth::user()->employee->company) {
            $this->memo = Auth::user()->employee->company->quotation_memo;
            $this->footer = Auth::user()->employee->company->quotation_footer;
            $this->vat = Auth::user()->employee->company->vat;
            $this->company_id = Auth::user()->employee->company->id;
        }elseif (Auth::user()->company) {
            $this->memo = Auth::user()->company->quotation_memo;
            $this->footer = Auth::user()->company->quotation_footer;
            $this->vat = Auth::user()->company->vat;
            $this->company_id = Auth::user()->company->id;
        }

    }

    public function quotationDate(){
        if ($this->expiry == "") {
            $this->expiry  = $this->date;
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

        'selectedCargo.*.required' => 'Cargo field is required',
        'selectedCargo.0.required' => 'Cargo field is required',
        'to.*.required' => 'To field is required',
        'to.0.required' => 'To field is required',
        'from.*.required' => 'From field is required',
        'from.0.required' => 'From field is required',
        'weight.*.required' => 'Weight field is required',
        'weight.0.required' => 'Weight field is required',
        'rate.*.required' => 'Rate field is required',
        'rate.0.required' => 'Rate field is required',
        'freight.*.required' => 'Freight field is required',
        'freight.0.required' => 'Freight field is required',
        
    ];

    protected $rules = [
        'selectedCustomer' => 'required',
        'quotation_number' => 'required',
        'date' => 'required',
        'currency_id' => 'required',
        'selectedCargo.0' => 'required',
        'from.0' => 'required',
        'to.0' => 'required',
        'freight.0' => 'required',
        'weight.0' => 'required',

        'offloading_point_id.*' => 'required',
        'loading_point_id.*' => 'required',
        'selectedCargo.*' => 'required',
        'from.*' => 'required',
        'to.*' => 'required',
        'freight.*' => 'required',
        'rate.*' => 'required',
        'weight.*' => 'required',

    ];

    public function store(){

        $quotation = new Quotation;
        $quotation->user_id = Auth::user()->id;
        $quotation->company_id = $this->company_id;
        $quotation->customer_id = $this->selectedCustomer;
        $quotation->currency_id = $this->currency_id;
        $quotation->quotation_number = $this->quotation_number;
        $quotation->number = $this->number;
        $quotation->date = $this->date;
        $quotation->expiry = $this->expiry;
        $quotation->memo = $this->memo;
        $quotation->footer = $this->footer;
        $quotation->save();
        $quotation->bank_accounts()->sync($this->bank_account_id);

        $this->quotation_id = $quotation->id;

        if (isset($this->selectedCargo )) {
        foreach ($this->selectedCargo as $key => $value) {
            $quotation_product = new QuotationProduct;
            $quotation_product->quotation_id =  $this->quotation_id;
            if (isset($this->selectedCargo[$key])) {
                $cargo = Cargo::find($this->selectedCargo[$key]);
                $cargo_name = $cargo->name ? $cargo->name: "";
              }
              if (isset($this->to[$key])) {
                $to = Destination::find($this->to[$key]);
                $destination_country = $to->country ?  $to->country->name : "";
                $destination = $destination_country .' '. $to->city;
              }else {
                $destination = "";
              }
              if (isset($this->from[$key])) {
                $from = Destination::find($this->from[$key]);
                $origin_country = $from->country ?  $from->country->name : "";
                $origin = $origin_country .' '. $from->city;

              }else {
                $origin = "";
              }
              
              if (isset($this->loading_point_id[$key])) {
                $loading_point = LoadingPoint::find($this->loading_point_id[$key]);
                $loading_point_name = $loading_point ? $loading_point->name : "";
              }else {
                $loading_point_name = "";
              }
              if (isset($this->offloading_point_id[$key])) {
                $offloading_point = OffloadingPoint::find($this->offloading_point_id[$key]);
                $offloading_point_name = $offloading_point ? $offloading_point->name : "";
              }else {
                $offloading_point_name = "";
              }
              if (isset($this->weight[$key])) {
                $weight = $this->weight[$key]."Tons";
            }else {
                $weight = "";
            }

              $description = $cargo_name. " " .$weight.",". " from " .$origin." ".$loading_point_name. " to " .$destination." ".$offloading_point_name.".";
              $quotation_product->description = $description;


            if (isset($this->qty[$key])) {
                $quotation_product->qty = $this->qty[$key];
            }
            if (isset($this->freight[$key])) {
                $quotation_product->freight = $this->freight[$key];
            }
           
            if (isset($this->freight[$key]) && isset($this->qty[$key]) ) {
                $quotation_product->subtotal = $this->freight[$key]*$this->qty[$key];
                
            }
          
            $quotation_product->save();

            if (isset($this->freight[$key]) && isset($this->qty[$key]) ) {
                $this->subtotal = $this->subtotal + ($this->freight[$key]*$this->qty[$key]);
                
            }
         
           

        }

    }
        if (($this->vat != "" && $this->vat != Null) && ($this->subtotal != "" && $this->subtotal != Null) ) {
            $this->total = $this->subtotal + ($this->subtotal * ($this->vat/100));
            $quotation =  Quotation::find($quotation->id);
            $quotation->total = $this->total;
            $quotation->exchange_rate = $this->exchange_rate;
            if (($this->exchange_rate != "" && $this->exchange_rate != Null) && ($this->total != "" && $this->total != Null)) {
               $exchange_amount = $this->exchange_rate * $this->total;
               $quotation->exchange_amount = $exchange_amount;
            }
            $quotation->subtotal = $this->subtotal;
            $quotation->vat = $this->vat;
            $quotation->update();
        }else{
            $this->total = $this->subtotal;
            $quotation =  Quotation::find($quotation->id);
            $quotation->exchange_rate = $this->exchange_rate;
            if (($this->exchange_rate != "" && $this->exchange_rate != Null) && ($this->total != "" && $this->total != Null)) {
               $exchange_amount = $this->exchange_rate * $this->total;
               $quotation->exchange_amount = $exchange_amount;
            }
            $quotation->total = $this->total;
            $quotation->subtotal = $this->subtotal;
            $quotation->vat = $this->vat;
            $quotation->update();
        }
       

        Session::flash('success','Quotation Created Successfully!!');

        $this->dispatchBrowserEvent('hide-quotationModal');

        return redirect()->route('quotations.index');
    }





    public function render()
    {
        $this->currencies = Currency::latest()->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->bank_accounts = BankAccount::orderBy('name','asc')->get();
            return view('livewire.quotations.create',[
                'customers' => $this->customers,
                'cargos' => $this->cargos,
                'destinations' => $this->destinations,
                'loading_points' => $this->loading_points,
                'offloading_points' => $this->offloading_points,
                'currencies' => $this->currencies,
                'bank_accounts' => $this->bank_accounts,
            ]);
    }
}
