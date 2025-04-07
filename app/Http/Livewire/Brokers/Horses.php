<?php

namespace App\Http\Livewire\Brokers;

use App\Models\Horse;
use App\Models\Broker;
use Livewire\Component;

class Horses extends Component
{

    public $broker;
    public $broker_id;
    public $broker_horses;
    public $broker_horse_id;
    public $horses;
    public $horse;
    public $horse_id;
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
        $this->horse_id = "" ;
    }

    public function mount($id){
        $this->broker = Broker::find($id);
        $this->broker_id = $id;
        $this->horses = Horse::latest()->get();
        $this->broker_horses =  $this->broker->horses;
    }

    
    public function removeShow($broker_id, $horse_id){
        $this->broker = Broker::find($broker_id);
        $this->horse = Horse::find($horse_id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removehorse(){ 
        $this->broker->horses()->detach($this->horse->id);
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"horse(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{
            if (isset($this->horse_id)) {
                $this->broker->horses()->attach($this->horse_id);
                }
           
            $this->dispatchBrowserEvent('hide-addHorseModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"horse(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading horse(s)!!"
            ]);
        }
    }


    public function render()
    {
        $this->broker_horses =  $this->broker->horses;
        $this->broker = Broker::find( $this->broker_id);
        
        return view('livewire.brokers.horses',[
            'broker_horses' => $this->broker_horses,
            'broker' => $this->broker
        ]);
    }
}
