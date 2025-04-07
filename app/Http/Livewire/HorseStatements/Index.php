<?php

namespace App\Http\Livewire\HorseStatements;

use App\Models\Bill;
use App\Models\Trip;
use App\Models\Horse;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $horses;
    public $selectedHorse;
    public $bills;
    public $from;
    public $to;
    public $default_currency;
    public $total_revenue;

    public function mount(){
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->default_currency = Auth::user()->employee->company->currency;
    }

    public function updatedSelectedHorse($id){
        if (!is_null($id)) {
            $this->selected_horse = Horse::find($id);
        }
    }

    public function render()
    {
        if (isset($this->selectedHorse) && isset($this->from) && isset($this->to)) {

            $this->bills = Bill::where('horse_id',$this->selectedHorse)->whereBetween('bill_date',[$this->from, $this->to])
            ->where('authorization','approved')->where('total','!=',Null)->where('total','!=','')->get();

            $default_trips_sum = Trip::where('horse_id',$this->selectedHorse)->where('currency_id', $this->default_currency->id)->whereBetween('start_date',[$this->from,$this->to])
            ->where('authorization','approved')->where('freight','!=',Null)->where('freight','!=','')->where('freight','>',0)->where('trip_status','!=','cancelled')->get()->sum('freight');
            $exchange_trips_sum = Trip::where('horse_id',$this->selectedHorse)->where('currency_id','!=', $this->default_currency->id)->whereBetween('start_date',[$this->from,$this->to])
            ->where('authorization','approved')->where('exchange_customer_freight','!=',Null)->where('exchange_customer_freight','!=','')->where('exchange_customer_freight','>',0)->where('trip_status','!=','cancelled')->get()->sum('exchange_customer_freight');
            if (isset($exchange_trips_sum)) {
               $this->total_revenue = $default_trips_sum + $exchange_trips_sum;
            }else{
                $this->total_revenue =  $default_trips_sum;
            }
            $default_currency_bills = Bill::where('horse_id',$this->selectedHorse)->where('currency_id',$this->default_currency->id)->whereBetween('bill_date',[$this->from,$this->to])
            ->where('authorization','approved')->where('total','!=',Null)->where('total','!=','')->where('total','>',0)->get();
            $exchange_currency_bills = Bill::where('horse_id',$this->selectedHorse)->where('currency_id',$this->default_currency->id)->whereBetween('bill_date',[$this->from,$this->to])
            ->where('authorization','approved')->where('exchange_amount','!=',Null)->where('exchange_amount','!=','')->where('exchange_amount','>',0)->get();
        }
        return view('livewire.horse-statements.index');
    }
}
