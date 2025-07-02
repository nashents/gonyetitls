<?php

namespace App\Http\Livewire\Dispatches;

use Livewire\Component;
use App\Models\Dispatch;

class Show extends Component
{
    public $dispatch;
    public $department;

    public function mount($id){
       
        $this->dispatch = Dispatch::find($id);
        $this->department = $this->dispatch->department;
    }

    public function render()
    {
        return view('livewire.dispatches.show');
    }
}
