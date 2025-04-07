<?php

namespace App\Http\Livewire\ClearingAgents;

use Livewire\Component;

class Show extends Component
{

    public $clearing_agent;
   
    public function mount($clearing_agent){
        $this->clearing_agent = $clearing_agent;
    }
    
    public function render()
    {
        return view('livewire.clearing-agents.show');
    }
}
