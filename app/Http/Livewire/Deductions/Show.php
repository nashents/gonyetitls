<?php

namespace App\Http\Livewire\Deductions;

use Livewire\Component;
use App\Models\Deduction;

class Show extends Component
{

    public $deductions;
    public $deduction;
    public $deduction_id;

    public function mount($id){
        $this->deduction = Deduction::find($id);
        
    }
    public function render()
    {
       
        return view('livewire.deductions.show');
    }
}
