<?php

namespace App\Http\Livewire\Dispatches;

use App\Models\Department;
use App\Models\Dispatch;
use Livewire\Component;

class Show extends Component
{
    public $dispatch;
    public $department;

    public function mount($id){
       
        $this->dispatch = Dispatch::find($id);
        $this->department = Department::find($this->dispatch->department_id);
    }

    public function render()
    {
        return view('livewire.dispatches.show');
    }
}
