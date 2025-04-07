<?php

namespace App\Http\Livewire\Agents;

use Livewire\Component;

class Show extends Component
{

    public $agent;
    public $trips;

    public function mount($agent){
        $this->agent = $agent;
        $this->trips = $this->agent->trips;
    }
    
    public function render()
    {
        return view('livewire.agents.show');
    }
}
