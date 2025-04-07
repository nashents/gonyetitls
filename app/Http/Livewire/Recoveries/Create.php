<?php

namespace App\Http\Livewire\Recoveries;

use App\Models\Trip;
use App\Models\Driver;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Recovery;
use App\Models\Deduction;
use App\Models\Destination;
use App\Models\Measurement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    public $measurements;
    public $measurement;
    public $destinations;
    public $destination_id;
    public $deductions;
    public $deduction_id;
    public $exchange_rate;
    public $exchange_amount;
    public $selectedDriver;
    public $drivers;
    public $trips;
    public $trip_id;
    public $amount;
    public $date;
    public $rate;
    public $weight;
    public $quantity;
    public $litreage;
    public $type;
    public $currencies;
    public $currency_id;
    public $description;
    public $recovery_number;

    public function mount(){
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->deductions = Deduction::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->trips = collect();
        $this->drivers = Driver::query()->with('employee:id,name,surname')
        ->withAggregate('employee','name')
        ->orderBy('employee_name','asc')->get();

    }
    public function recoveryNumber(){

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
    
        $recovery = Recovery::orderBy('id','desc')->first();
    
        if (!$recovery) {
            $recovery_number =  $initials .'R'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $recovery->id + 1;
            $recovery_number =  $initials .'R'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $recovery_number;
    
    
    }

    public function updatedSelectedDriver($driver){
        if (!is_null($driver)) {
            $this->driver = Driver::find($driver);
            $this->trips = $this->driver->trips->sortByDesc('trip_number');
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
   

    protected $rules = [
        'trip_id' => 'required',
        'deduction_id' => 'required',
        'selectedDriver' => 'required',
        'description' => 'required',
        'currency_id' => 'required',
        'type' => 'required',
        'amount' => 'required',
        'date' => 'required',
        'destination_id' => 'required',

    ];

    public function store(){
        $recovery = new Recovery;
        $recovery->user_id = Auth::user()->id;
        $recovery->recovery_number = $this->recoveryNumber();
        $recovery->driver_id = $this->selectedDriver;
        $recovery->trip_id = $this->trip_id;
        $recovery->deduction_id = $this->deduction_id;
        $recovery->currency_id = $this->currency_id;
        $recovery->destination_id = $this->destination_id;
        $recovery->weight = $this->weight;
        $recovery->rate = $this->rate;
        $recovery->litreage = $this->litreage;
        $recovery->quantity = $this->quantity;
        $recovery->measurement = $this->measurement;
        $recovery->type = $this->type;
        $recovery->amount = $this->amount;
        $recovery->balance = $this->amount;
        $recovery->exchange_rate = $this->exchange_rate;
        if (isset($this->exchange_rate) && isset($this->amount)) {
           $exchange_amount = $this->exchange_rate * $this->amount;
           $recovery->exchange_amount = $exchange_amount;
        }
        $recovery->date = $this->date;
        $recovery->description = $this->description;
        $recovery->status = "Unpaid";
        $recovery->progress = "Open";
        $recovery->authorization = "pending";
        $recovery->save();

        Session::flash('success','Recovery Created Successfully!!');
        return redirect()->route('recoveries.index');

    }

    public function render()
    {
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->deductions = Deduction::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        return view('livewire.recoveries.create',[
            'deductions' => $this->deductions,
            'destinations' => $this->destinations,
            'measurements' => $this->measurements,
        ]);
    }
}
