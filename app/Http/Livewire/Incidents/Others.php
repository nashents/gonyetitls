<?php

namespace App\Http\Livewire\Incidents;

use Livewire\Component;
use App\Models\Incident;

class Others extends Component
{

    public $incident;
    public $others;

    public function mount($id){
        $this->incident = Incident::find($id);
        $this->others = $this->incident->incident_others;
    }


    public function render()
    {
        return view('livewire.incidents.others');
    }
}
