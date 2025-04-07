<?php

namespace App\Http\Livewire\Requisitions;

use Livewire\Component;
use App\Models\Requisition;

class Show extends Component
{

    public $requisition;

    public function mount($id){
        $this->requisition = Requisition::find($id);
    }

    public function render()
    {
        return view('livewire.requisitions.show');
    }
}
