<?php

namespace App\Http\Livewire\Brokers;

use App\Models\Broker;
use App\Models\Trailer;
use Livewire\Component;

class Trailers extends Component
{

    public $broker;
    public $broker_id;
    public $broker_trailers;
    public $broker_trailer_id;
    public $trailers;
    public $trailer;
    public $trailer_id;
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
        $this->trailer_id = "" ;
    }

    public function mount($id){
        $this->broker = Broker::find($id);
        $this->broker_id = $id;
        $this->trailers = Trailer::latest()->get();
        $this->broker_trailers =  $this->broker->trailers;
    }

    
    public function removeShow($broker_id, $trailer_id){
        $this->broker = Broker::find($broker_id);
        $this->trailer = Trailer::find($trailer_id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removetrailer(){ 
        $this->broker->trailers()->detach($this->trailer->id);
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"trailer(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{
            if (isset($this->trailer_id)) {
                $this->broker->trailers()->attach($this->trailer_id);
                }
           
            $this->dispatchBrowserEvent('hide-addTrailerModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trailer(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading trailer(s)!!"
            ]);
        }
    }


    public function render()
    {
        $this->broker_trailers =  $this->broker->trailers;
        $this->broker = Broker::find( $this->broker_id);
        
        return view('livewire.brokers.trailers',[
            'broker_trailers' => $this->broker_trailers,
            'broker' => $this->broker
        ]);
    }
}
