<?php

namespace App\Http\Livewire\ClearingAgents;

use Livewire\Component;
use App\Models\Border;
use App\Models\ClearingAgent;
use App\Models\ClearingAgentBorder;

class Borders extends Component
{
    public $clearing_agent;
    public $clearing_agent_id;
    public $clearing_agent_borders;
    public $clearing_agent_border_id;
    public $borders;
    public $border;
    public $border_id;
    public $name;
    public $country_a;
    public $country_b;

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
        $this->title = "" ;
        $this->file = "";
    }

    public function mount($id){
        $this->clearing_agent = ClearingAgent::find($id);
        $this->clearing_agent_id = $id;
        $this->borders = Border::latest()->get();
        $this->clearing_agent_borders =  $this->clearing_agent->borders;
    }

    public function removeShow($clearing_agent_id, $border_id){
        $this->clearing_agent = ClearingAgent::find($clearing_agent_id);
        $this->border = Border::find($border_id);
        $this->dispatchBrowserEvent('show-removeBorderModal');
    }

    public function removeborder(){ 
        $this->clearing_agent->borders()->detach($this->border->id);
        $this->dispatchBrowserEvent('hide-removeBorderModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Border(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{

            if (isset($this->border_id)) {
                // $this->clearing_agent->borders()->detach();
                $this->clearing_agent->borders()->attach($this->border_id);
                }
            
            $this->dispatchBrowserEvent('hide-addBorderModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Border(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading border(s)!!"
            ]);
        }
    }

    public function render()
    {
        $this->clearing_agent_borders =  $this->clearing_agent->borders;
        $this->clearing_agent = ClearingAgent::find( $this->clearing_agent_id);
        return view('livewire.clearing-agents.borders',[
            'clearing_agent_borders' => $this->clearing_agent_borders,
            'clearing_agent' => $this->clearing_agent
        ]);
    }
}
