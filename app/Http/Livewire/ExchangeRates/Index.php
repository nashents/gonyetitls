<?php

namespace App\Http\Livewire\ExchangeRates;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $exchange_rates;
    public $exchange_rate_id;
    public $rate;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $frequency;
    public $status;
    public $company;


    public function mount(){
        $this->company = Auth::user()->employee->company;
        $this->exchange_rates = ExchangeRate::with('currency')->get()->sortBy('currency.name');
        $this->currencies = Currency::where('id','!=',$this->company->currency_id)->orderBy('name','asc')->get();
        $this->frequency = $this->company->exchange_rate_frequency;
    }

    private function resetInputFields(){
        $this->rate = "" ;
        $this->frequency = "" ;
        $this->selectedCurrency = "";
        $this->status = "";
    }

    public function updatedSelectedCurrency($id){
        if (!is_null($id)) {
            $this->selected_currency = Currency::find($id);
        }
    }

    public function store(){

            $exchange_rate = ExchangeRate::firstOrNew(['currency_id' => $this->selectedCurrency]);
            $exchange_rate->user_id = Auth::user()->id;
            $exchange_rate->currency_id = $this->selectedCurrency;
            $exchange_rate->frequency = $this->frequency;
            if ($this->frequency == "daily") {
               $exchange_rate->expiry = Carbon::today()->addDay()->format('Y-m-d');;
            }elseif ($this->frequency == "weekly") {
                $exchange_rate->expiry = Carbon::today()->addWeek()->format('Y-m-d');
            }elseif ($this->frequency == "monthly") {
                $exchange_rate->expiry = Carbon::today()->addMonth()->format('Y-m-d');
            }
            $exchange_rate->exchange_rate = $this->rate;
            $exchange_rate->status = 1;
            $exchange_rate->save();

            $this->dispatchBrowserEvent('hide-exchange_rateModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bank Account(s) Uploaded Successfully!!"
            ]);
       
    }

    public function edit($id){

        $exchange_rate = ExchangeRate::find($id);
        $this->rate = $exchange_rate->exchange_rate;
        $this->status = $exchange_rate->status;
        $this->selectedCurrency = $exchange_rate->currency_id;
        $this->selected_currency = Currency::find($exchange_rate->currency_id);
        $this->frequency = $exchange_rate->frequency;
        $this->exchange_rate_id = $exchange_rate->id;
        $this->dispatchBrowserEvent('show-exchange_rateEditModal');

    }
    public function update(){
        $exchange_rate = ExchangeRate::find($this->exchange_rate_id);
        $exchange_rate->exchange_rate = $this->rate;
        $exchange_rate->currency_id = $this->selectedCurrency;
        $exchange_rate->frequency = $this->frequency;
        if ($this->frequency == "daily") {
            $exchange_rate->expiry = Carbon::today()->addDay()->format('Y-m-d');;
        }elseif ($this->frequency == "weekly") {
            $exchange_rate->expiry = Carbon::today()->addWeek()->format('Y-m-d');
        }elseif ($this->frequency == "monthly") {
            $exchange_rate->expiry = Carbon::today()->addMonth()->format('Y-m-d');
        }
        $exchange_rate->status = $this->status;
        $exchange_rate->update();
        $this->dispatchBrowserEvent('hide-exchange_rateEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Exchange Rate Updated Successfully!!"
        ]);
    }
    public function render()
    {
        $this->exchange_rates = ExchangeRate::with('currency')->get()->sortBy('currency.name');
        return view('livewire.exchange-rates.index',[
            'exchange_rates' =>  $this->exchange_rates
        ]);
    }
}
