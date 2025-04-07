<?php

namespace App\Http\Livewire\TopUps;

use App\Models\TopUp;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Container;
use Illuminate\Support\Facades\Auth;

class Manage extends Component
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

    public function mount($container){
        $this->container = $container;
        $this->container_id = $container->id;
        $this->top_ups = TopUp::where('container_id',$container->id)->latest()->get();

        $this->currencies = Currency::all();
        $this->containers = Container::all();
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
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->top_ups = TopUp::where('container_id', $this->container_id)->latest()->get();
        } else {
            $this->top_ups = TopUp::where('user_id',Auth::user()->id)
            ->where('container_id', $this->container_id)->latest()->get();
        }
        if ($this->quantity != null && $this->rate != null) {
            $this->amount = $this->quantity * $this->rate;
        }
        return view('livewire.top-ups.manage',[
            'amount' => $this->amount,
            'top_ups' =>$this->top_ups
        ]);
    }
}
