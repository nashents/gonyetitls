<?php

namespace App\Http\Livewire\Incidents;

use Livewire\Component;
use App\Models\Incident;

class Injuries extends Component
{

    public $incident;
    public $injuries;

    public function mount($id){
        $this->incident = Incident::find($id);
        $this->injuries = $this->incident->incident_injuries;
    }


    public function render()
    {
        return view('livewire.incidents.injuries');
    }
}
