<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Leave;
use App\Models\Branch;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;

class Company extends Component
{

    public $branch_count;
    public $employee_count;
    public $department_count;
    public $leave_count;

    public function mount(){
        $this->branch_count = Branch::all()->count();
        $this->employee_count = Employee::doesntHave('driver')->get()->count();
        $this->department_count = Department::all()->count();
        $this->leave_count = Leave::all()->count();
    }

    public function render()
    {
        return view('livewire.dashboard.company');
    }
}
