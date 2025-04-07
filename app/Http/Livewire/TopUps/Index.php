<?php

namespace App\Http\Livewire\TopUps;

use Carbon\Carbon;
use App\Models\TopUp;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Container;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $top_ups;
    public $order_number;
    public $top_up_id;
    public $currency_id;
    public $currencies;
    public $container_id;
    public $date;
    public $containers;
    public $quantity;
    public $rate;
    public $fuel_type;
    public $amount;
    public $balance;



    public $user_id;

    public function mount(){
        $this->top_ups = TopUp::orderBy('created_at','desc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->containers = Container::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [

        'currency_id' => 'required',
        'container_id' => 'required',
        'date' => 'required',
        'quantity' => 'required',
        'fuel_type' => 'required',
        'rate' => 'required',
    ];
    private function resetInputFields(){


        $this->container_id = "";
        $this->date = "";
        $this->currency_id = "";
        $this->fuel_type = "";
        $this->quantity = "";
        $this->rate = "";
        $this->amount = "";
    }

    public function store(){
        try{
        $top_up = new TopUp;
        $top_up->user_id = Auth::user()->id;
        $top_up->order_number = 'TFT' . Str::random(5);
        $top_up->container_id = $this->container_id;
        $top_up->date = $this->date;
        $top_up->currency_id = $this->currency_id;
        $top_up->fuel_type = $this->fuel_type;
        $top_up->quantity = $this->quantity;
        $top_up->rate = $this->rate;
        $top_up->amount = $this->amount;
        $top_up->save();

        $container = Container::find($this->container_id);
        $container->balance = $this->quantity;
        $container->update();

        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Top Up Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->dispatchBrowserEvent('alert',[

            'type'=>'error',
            'message'=>"Something went wrong while creating broker!!"
        ]);
    }

    }

    public function edit($id){
    $top_up = TopUp::find($id);
    $this->user_id = $top_up->user_id;
    $this->container_id = $top_up->container_id;
    $this->currency_id = $top_up->currency_id;
    $this->fuel_type = $top_up->fuel_type;
    $this->date = $top_up->date;
    $this->quantity = $top_up->quantity;
    $this->rate = $top_up->rate;
    $this->amount = $top_up->amount;
    $this->top_up_id = $top_up->id;
    $this->dispatchBrowserEvent('show-top_upEditModal');

    }


    public function update()
    {
        if ($this->top_up_id) {
            try{
            $top_up = TopUp::find($this->top_up_id);
            $top_up->update([
                'user_id' => Auth::user()->id,
                'container_id' => $this->container_id,
                'currency_id' => $this->currency_id,
                'fuel_type' => $this->fuel_type,
                'date' => $this->date,
                'quantity' => $this->quantity,
                'rate' => $this->rate,
                'amount' => $this->amount,
            ]);
            $this->dispatchBrowserEvent('hide-top_upEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Supplier Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('hide-top_upEditModal');
            $this->dispatchBrowserEvent('alert',[

                'type'=>'error',
                'message'=>"Something went wrong while creating broker!!"
            ]);
        }
        }
    }

    public function render()
    {
        $this->top_ups = TopUp::orderBy('created_at','desc')->get();
        if ($this->quantity != null && $this->rate != null) {
            $this->amount = $this->quantity * $this->rate;
        }
        return view('livewire.top-ups.index',[
            'amount' => $this->amount,
            'top_ups' =>$this->top_ups
        ]);
    }
}
