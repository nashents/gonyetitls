<?php

namespace App\Http\Livewire\Incidents;

use Livewire\Component;
use App\Models\Incident;

class Damages extends Component
{

    public $incident;
    public $damages;

    public function mount($id){
        $this->incident = Incident::find($id);
        $this->damages = $this->incident->incident_damages;
    }

    public function render()
    {
        return view('livewire.incidents.damages');
    }
}
