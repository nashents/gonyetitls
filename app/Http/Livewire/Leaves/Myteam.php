<?php

namespace App\Http\Livewire\Leaves;

use Livewire\Component;
use App\Models\DepartmentHead;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class Myteam extends Component
{
    public $employee;
    public $employees;
    public $department_heads;
    public $employee_department_ids;

    public function mount(){
        $this->employee = Auth::user()->employee;
        // Everything on this page is scoped to MY DEFAULT department only.
        $defaultDepartmentId = $this->employee?->defaultDepartment()?->id;
        $this->employee_department_ids = $defaultDepartmentId ? [$defaultDepartmentId] : [];

        if (!$defaultDepartmentId) {
            $this->employees = collect();
            $this->department_heads = collect();
            return;
        }

        // Reporting To: the department's HOD(s) plus Management / Director
        // ranks whose default department is mine (excluding myself).
        $headEmployeeIds = DepartmentHead::where('department_id', $defaultDepartmentId)
            ->pluck('employee_id');

        $this->department_heads = Employee::with(['departments', 'leaves', 'user', 'ranks'])
            ->where('id', '!=', $this->employee->id)
            ->where(function ($q) use ($headEmployeeIds, $defaultDepartmentId) {
                $q->whereIn('id', $headEmployeeIds)
                  ->orWhere(function ($q) use ($defaultDepartmentId) {
                      $q->inDefaultDepartment($defaultDepartmentId)
                        ->whereHas('ranks', fn ($r) => $r->whereIn('name', self::SENIOR_RANKS));
                  });
            })
            ->orderBy('name')
            ->get();

        // My Team: colleagues whose default department is mine, excluding
        // myself and anyone already listed under Reporting To.
        $this->employees = Employee::with(['default_departments', 'leaves', 'user'])
            ->visibleTo()
            ->inDefaultDepartment($defaultDepartmentId)
            ->where('id', '!=', $this->employee->id)
            ->whereNotIn('id', $this->department_heads->pluck('id'))
            ->orderBy('name')
            ->get();
    }

    private const SENIOR_RANKS = ['Management', 'Director'];

    public function render()
    {
        return view('livewire.leaves.myteam');
    }
}
