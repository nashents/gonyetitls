<?php

namespace App\Http\Livewire\Payslips;

use Livewire\Component;
use App\Models\PayrollSalary;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $payroll_salaries;

    public function mount(){
        $this->payroll_salaries = PayrollSalary::where('employee_id', Auth::user()->employee->id)->get();
    }

    public function render()
    {
        return view('livewire.payslips.index');
    }
}
