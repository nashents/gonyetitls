<?php

namespace App\Http\Livewire\Brokers;

use App\Models\Broker;
use App\Models\Driver;
use Livewire\Component;

class Drivers extends Component
{

    public $broker;
    public $broker_id;
    public $broker_drivers;
    public $broker_driver_id;
    public $drivers;
    public $driver;
    public $driver_id;
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
        $this->driver_id = "" ;
    }

    public function mount($id){
        $this->broker = Broker::find($id);
        $this->broker_id = $id;
        $this->drivers = Driver::latest()->get();
        $this->broker_drivers =  $this->broker->drivers;
    }

    
    public function removeShow($broker_id, $driver_id){
        $this->broker = Broker::find($broker_id);
        $this->driver = Driver::find($driver_id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removedriver(){ 
        $this->broker->drivers()->detach($this->driver->id);
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"driver(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{
            if (isset($this->driver_id)) {
                $this->broker->drivers()->attach($this->driver_id);
                }
           
            $this->dispatchBrowserEvent('hide-addDriverModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"driver(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading driver(s)!!"
            ]);
        }
    }


    public function render()
    {
        $this->broker_drivers =  $this->broker->drivers;
        $this->broker = Broker::find( $this->broker_id);
        
        return view('livewire.brokers.drivers',[
            'broker_drivers' => $this->broker_drivers,
            'broker' => $this->broker
        ]);
    }
}
