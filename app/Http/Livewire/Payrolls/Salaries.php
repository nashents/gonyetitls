<?php

namespace App\Http\Livewire\Payrolls;

use App\Models\Payroll;
use Livewire\Component;

class Salaries extends Component
{
    public $payroll;
    public $payroll_salaries;

    public function mount($id){
        $this->payroll = Payroll::find($id);
        $this->payroll_salaries = $this->payroll->payroll_salaries;
    }
    public function render()
    {
        return view('livewire.payrolls.salaries');
    }
}
