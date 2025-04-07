<?php

namespace App\Http\Livewire\GatePasses;

use Livewire\Component;
use App\Models\GatePass;

class Show extends Component
{
    public $gate_pass;

    public function mount($id){
        $this->gate_pass = GatePass::find($id);
    }
    public function render()
    {
        return view('livewire.gate-passes.show');
    }
}
