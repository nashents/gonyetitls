<?php

namespace App\Http\Livewire\Incidents;

use Livewire\Component;
use App\Models\Incident;

class Show extends Component
{
    public $incident;
    public $incidents;
    public $incident_id;

    public function mount($id){
        $this->incident = Incident::find($id);
    }
    public function render()
    {
        return view('livewire.incidents.show');
    }
}
