<?php

namespace App\Http\Livewire\Payrolls;

use App\Models\Payroll;
use Livewire\Component;

class Rejected extends Component
{
    public $payrolls;
    
    public function mount(){
        $this->payrolls = Payroll::where('authorization','rejected')->latest()->get();
    }
    public function render()
    {
        return view('livewire.payrolls.rejected');
    }
}
