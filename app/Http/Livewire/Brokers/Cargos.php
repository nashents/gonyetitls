<?php

namespace App\Http\Livewire\Brokers;

use App\Models\Cargo;
use App\Models\Broker;
use Livewire\Component;

class Cargos extends Component
{
    public $broker;
    public $broker_id;
    public $broker_cargos;
    public $broker_cargo_id;
    public $cargos;
    public $cargo;
    public $cargo_id;
    public $name;
    public $from;
    public $to;

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

    private function resetInputFields(){
        $this->cargo_id = "" ;
    }

    public function mount($id){
        $this->broker = Broker::find($id);
        $this->broker_id = $id;
        $this->cargos = Cargo::latest()->get();
        $this->broker_cargos =  $this->broker->cargos;
    }


    public function removeShow($broker_id, $cargo_id){
        $this->broker = Broker::find($broker_id);
        $this->cargo = Cargo::find($cargo_id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeCargo(){ 
        $this->broker->cargos()->detach($this->cargo->id);
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Cargo(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{
            if (isset($this->cargo_id)) {
                $this->broker->cargos()->attach($this->cargo_id);
                }
           
            $this->dispatchBrowserEvent('hide-addCargoModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargo(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading cargo(s)!!"
            ]);
        }
    }

    public function render()
    {
        $this->broker_cargos =  $this->broker->cargos;
        $this->broker = Broker::find( $this->broker_id);
        
        return view('livewire.brokers.cargos',[
            'broker_cargos' => $this->broker_cargos,
            'broker' => $this->broker
        ]);
    }
}
