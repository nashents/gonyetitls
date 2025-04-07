<?php

namespace App\Http\Livewire\Allocations;

use Livewire\Component;
use App\Models\Allocation;

class Deleted extends Component
{
    public $allocations;
    public $allocation_id;

    public function mount(){
        $this->allocations = Allocation::onlyTrashed()->get();
    }
    public function render()
    {
        return view('livewire.allocations.deleted');
    }
}
