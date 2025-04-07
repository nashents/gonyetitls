<?php

namespace App\Http\Livewire\Currencies;

use Livewire\Component;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $currencies;
    public $name;
    public $fullname;
    public $symbol;

    public $currency_id;
    public $user_id;

    public function mount(){
        $this->currencies = Currency::latest()->get();
    }
    private function resetInputFields(){
        $this->fullname = "";
        $this->name = "";
        $this->symbol = "";
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:currencies,name,NULL,id,deleted_at,NULL|string|min:2',
        'fullname' => 'required',
        'symbol' => 'required',
    ];

    public function store(){
        try{
        $currency = new Currency;
        $currency->user_id = Auth::user()->id;
        $currency->name = $this->name;
        $currency->fullname = $this->fullname;
        $currency->symbol = $this->symbol;
        $currency->save();
        $this->dispatchBrowserEvent('hide-currencyModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Currency Created Successfully!!"
        ]);

        }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating currency!!"
            ]);
         }

    }

    public function edit($id){
    $currency = Currency::find($id);
    $this->user_id = $currency->user_id;
    $this->name = $currency->name;
    $this->fullname = $currency->fullname;
    $this->symbol = $currency->symbol;
    $this->currency_id = $currency->id;
    $this->dispatchBrowserEvent('show-currencyEditModal');

    }



    public function update()
    {
        if ($this->currency_id) {
            try {
            $currency = Currency::find($this->currency_id);
            $currency->update([
                'user_id' => Auth::user()->id,
                'fullname' => $this->fullname,
                'name' => $this->name,
                'symbol' => $this->symbol,
            ]);

            $this->dispatchBrowserEvent('hide-currencyEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Currency Updated Successfully!!"
            ]);
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating currency!!"
                ]);
             }

        }
    }

    public function render()
    {
        $this->currencies = Currency::latest()->get();
        return view('livewire.currencies.index',[
            'currencies' => $this->currencies
        ]);
    }
}
