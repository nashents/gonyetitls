<?php

namespace App\Http\Livewire\Dispatches;

use App\Models\Dispatch;
use Livewire\Component;

class Movements extends Component
{
    public $movements;


    public function mount($id){
        $dispatch = Dispatch::find($id);
        $this->movements = $dispatch->movements;

    }
    public function render()
    {
        return view('livewire.dispatches.movements');
    }
}
