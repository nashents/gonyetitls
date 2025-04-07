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
use Illuminate\Support\Facades\Session;

class Edit extends Component
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
    public $rate;
    public $weight;
    public $quantity;
    public $balance;
    public $litreage;
    public $type;
    public $amount;
    public $currencies;
    public $currency_id;
    public $description;
    public $recovery_number;
    public $recovery;
    public $date;
    public $recovery_id;

    public function updated($value){
        $this->validateOnly($value);
    }
   
    protected $rules = [
        'trip_id' => 'required',
        'deduction_id' => 'required',
        'selectedDriver' => 'required',
        'recovery_number' => 'required',
        'description' => 'required',
        'currency_id' => 'required',
        'amount' => 'required',
        'type' => 'required',
        'date' => 'required',
        'destination_id' => 'required',

    ];

    public function updatedSelectedDriver($driver){
        if (!is_null($driver)) {
            $this->driver = Driver::find($driver);
            $this->trips = $this->driver->trips;
        }
    }


    public function mount($id){
        $recovery = Recovery::find($id);
        $this->recovery_id = $recovery->id;
        $this->selectedDriver = $recovery->driver_id;
        $this->driver = Driver::find( $this->selectedDriver);
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->deductions = Deduction::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->destinations = Destination::latest()->get();
        $this->trips = $this->driver->trips;
        $this->drivers = Driver::where('status', 1)->orderBy('driver_number','asc')->get();
        $this->date = $recovery->date;
        $this->trip_id = $recovery->trip_id;
        $this->type = $recovery->type;
        $this->rate = $recovery->rate;
        $this->weight = $recovery->weight;
        $this->measurement = $recovery->measurement;
        $this->quantity = $recovery->quantity;
        $this->litreage = $recovery->litreage;
        $this->deduction_id = $recovery->deduction_id;
        $this->destination_id = $recovery->destination_id;
        $this->currency_id = $recovery->currency_id;
        $this->amount = $recovery->amount;
        $this->balance = $recovery->balance;
        $this->description = $recovery->description;
        $this->deduction = $recovery->deduction;
    }

    public function update(){
        $recovery = Recovery::find($this->recovery_id);
        $recovery->driver_id = $this->selectedDriver;
        $recovery->trip_id = $this->trip_id;
        $recovery->deduction_id = $this->deduction_id;
        $recovery->currency_id = $this->currency_id;
        $recovery->type = $this->type;
        $recovery->weight = $this->weight;
        $recovery->measurement = $this->measurement;
        $recovery->quantity = $this->quantity;
        $recovery->rate = $this->rate;
        $recovery->litreage = $this->litreage;
        $recovery->destination_id = $this->destination_id;
        $recovery->amount = $this->amount;
        if ($recovery->status == "Unpaid") {
            $recovery->balance = $this->amount;
        }else {
            $recovery->balance = $this->balance;
        }
      
        $recovery->exchange_rate = $this->exchange_rate;
        if (isset($this->exchange_rate) && isset($this->amount)) {
           $exchange_amount = $this->exchange_rate * $this->amount;
           $recovery->exchange_amount = $exchange_amount;
        }
        $recovery->description = $this->description;
        $recovery->update();

        Session::flash('success','Recovery Updated Successfully!!');
        return redirect()->route('recoveries.index');

    }
    
    public function render()
    {
        $this->deductions = Deduction::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        return view('livewire.recoveries.edit',[
            'deductions' => $this->deductions,
            'measurements' => $this->measurements,
        ]);
    }
}
