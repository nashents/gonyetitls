<?php

namespace App\Http\Livewire\TyreAssignments;

use Livewire\Component;
use App\Models\TyreAssignment;

class Show extends Component
{
    public $tyre_assignment;

    public function mount($id){
        $this->tyre_assignment = TyreAssignment::find($id);
    }
    public function render()
    {
        return view('livewire.tyre-assignments.show');
    }
}
