<?php

namespace App\Http\Livewire\Shifts;

use Livewire\Component;
use App\Models\Rehandling;

class Rehandlings extends Component
{

    private $rehandlings;
    public $shift_id;
    public $shift;

    public function mount($id){
        $this->shift_id = $id;
    }

    public function render()
    {
        return view('livewire.shifts.rehandlings',[
            $this->rehandlings = Rehandling::where('shift_id',$this->shift_id)->paginate(10)
        ]);
    }
}
