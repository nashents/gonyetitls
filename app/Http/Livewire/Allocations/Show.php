<?php

namespace App\Http\Livewire\Allocations;

use Livewire\Component;
use App\Models\Allocation;

class Show extends Component
{
    public $allocations;
    public $allocation_id;
    public $allocation;


    public function mount($id){
        $this->allocation = Allocation::find($id);
    }

    public function render()
    {
        return view('livewire.allocations.show');
    }
}
