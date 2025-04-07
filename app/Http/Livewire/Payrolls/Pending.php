<?php

namespace App\Http\Livewire\Payrolls;

use App\Models\Payroll;
use Livewire\Component;

class Pending extends Component
{
    public $payrolls;
    
    public function mount(){
        $this->payrolls = Payroll::where('authorization','pending')->latest()->get();
    }

    public function update(){

    }
    public function render()
    {
        return view('livewire.payrolls.pending');
    }
}
