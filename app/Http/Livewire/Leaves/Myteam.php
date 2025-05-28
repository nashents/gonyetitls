<?php

namespace App\Http\Livewire\Leaves;

use Livewire\Component;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class Myteam extends Component
{
    public $employees;
    public $department_heads;
    public $employee_department_ids;

    public function mount(){
        $this->employee = Auth::user()->employee;
        $baseDepartmentIds = $this->employee->departments->pluck('id')->toArray();
        $this->employee_department_ids = $baseDepartmentIds;

        $this->employees = Employee::with(['departments', 'leaves'])->whereHas('departments', function ($query) use ($baseDepartmentIds) {
                                    $query->whereIn('departments.id', $baseDepartmentIds);
                                    })
                                    ->where('id', '!=', $this->employee->id) // optional: exclude the current employee
                                    ->distinct()
                                    ->get();
    }

    public function render()
    {
        return view('livewire.leaves.myteam');
    }
}
