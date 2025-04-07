<?php

namespace App\Http\Livewire\Payrolls;

use App\Models\Payroll;
use Livewire\Component;

class Approved extends Component
{
    public $payrolls;
    
    public function mount(){
        $this->payrolls = Payroll::where('authorization','approved')->latest()->get();
    }
    public function render()
    {
        return view('livewire.payrolls.approved');
    }
}
