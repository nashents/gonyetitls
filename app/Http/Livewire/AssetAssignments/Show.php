<?php

namespace App\Http\Livewire\AssetAssignments;

use Livewire\Component;
use App\Models\AssetAssignment;

class Show extends Component
{
    public $assignment; 

    public function mount($id){
        $this->assignment = AssetAssignment::find($id);
    }

    public function render()
    {
        return view('livewire.asset-assignments.show');
    }
}
