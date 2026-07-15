<?php

namespace App\Http\Livewire\Employees\Leaves;

use App\Exports\EmployeesLeaveExport;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;


class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $employees;
    public $employee;
    public $employee_id;
    public $leave_days;
    public $accrual_rate;
    public $maximum_leave_days;
    public $leave_count;
    public $leave_type_id;
    public $leave_types ;


    public function mount(){
        $this->leave_types = LeaveType::where('active',True)->orderBy('name','asc')->get();
        $this->resetPage();
    }

    public function enforceLeaveTypeDefaults()
    {
        $leaveTypes = LeaveType::where('active', true)->get();

        $enforced = 0;

        DB::transaction(function () use ($leaveTypes, &$enforced) {
            Employee::where('archive', 0)
                ->select('id')
                ->chunkById(200, function ($employees) use ($leaveTypes, &$enforced) {
                    foreach ($employees as $employee) {
                        foreach ($leaveTypes as $leaveType) {
                            EmployeeLeave::updateOrCreate(
                                [
                                    'employee_id' => $employee->id,
                                    'leave_type_id' => $leaveType->id,
                                ],
                                [
                                    'acrual_rate' => $leaveType->monthly_accrual_rate ?? 0,
                                    'available_leave_days' => $leaveType->entitlement ?? 0,
                                    'maximum_leave_days' => $leaveType->max_carry_forward_days ?? 0,
                                ]
                            );

                            $enforced++;

                            // Backward compatibility: keep the Employee table in sync, Annual Leave only
                            if (strtolower($leaveType->name) === 'annual') {
                                Employee::where('id', $employee->id)->update([
                                    'accrual_rate' => $leaveType->monthly_accrual_rate ?? 0,
                                    'leave_days' => $leaveType->entitlement ?? 0,
                                    'maximum_leave_days' => $leaveType->max_carry_forward_days ?? 0,
                                ]);
                            }
                        }
                    }
                });
        });

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Leave Type Defaults Enforced Successfully!! ({$enforced} record(s) updated)"
        ]);
    }

    public function exportEmployeesLeaveCSV(Excel $excel){

        return $excel->download(new EmployeesLeaveExport, 'employees_leave_data.csv', Excel::CSV);
    }
    public function exportEmployeesLeavePDF(Excel $excel){

        return $excel->download(new EmployeesLeaveExport, 'employees_leave_data.pdf', Excel::DOMPDF);
    }
    public function exportEmployeesLeaveExcel(Excel $excel){
        return $excel->download(new EmployeesLeaveExport, 'employees_leave_data.xlsx');
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'leave_days' => 'required',
        'accrual_rate' => 'required',
        'maximum_leave_days' => 'required',
    ];

    private function resetInputFields()
    {
        $this->employee_id = null;
        $this->employee = null;
        $this->leave_type_id = null;
        $this->accrual_rate = null;
        $this->leave_days = null;
        $this->maximum_leave_days = null;
    }

    public function showLeave($id)
    {
        $this->resetInputFields();

        $this->employee_id = $id;
        $this->employee = Employee::find($id);

        $this->dispatchBrowserEvent('show-leaveDaysModal');
    }

    public function updatedLeaveTypeId($id)
    {
        $this->accrual_rate = 0;
        $this->leave_days = 0;
        $this->maximum_leave_days = 0;

        if (is_null($this->employee_id) || is_null($id)) {
            return;
        }

        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return;
        }

        $employeeLeave = EmployeeLeave::where('employee_id', $this->employee_id)
            ->where('leave_type_id', $id)
            ->first();

        if ($employeeLeave) {
            $this->accrual_rate = $employeeLeave->acrual_rate;
            $this->leave_days = $employeeLeave->available_leave_days;
            $this->maximum_leave_days = $employeeLeave->maximum_leave_days;
            return;
        }

        // Backward compatibility for Annual Leave
        if (strtolower($leaveType->name) === 'annual') {
            $employee = Employee::find($this->employee_id);

            $this->accrual_rate = $employee->accrual_rate ?? 0;
            $this->leave_days = $employee->leave_days ?? 0;
            $this->maximum_leave_days = $employee->maximum_leave_days ?? 0;
        }
    }


    public function update()
    {
        $this->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'accrual_rate' => 'required|numeric|min:0',
            'leave_days' => 'required|numeric|min:0',
            'maximum_leave_days' => 'required|numeric|min:0',
        ]);

        $leaveType = LeaveType::find($this->leave_type_id);

        EmployeeLeave::updateOrCreate(
            [
                'employee_id' => $this->employee_id,
                'leave_type_id' => $this->leave_type_id,
            ],
            [
                'acrual_rate' => $this->accrual_rate,
                'available_leave_days' => $this->leave_days,
                'maximum_leave_days' => $this->maximum_leave_days,
            ]
        );

        // Backward compatibility: keep employee table updated for Annual Leave
        if ($leaveType && strtolower($leaveType->name) === 'annual') {
            Employee::where('id', $this->employee_id)->update([
                'accrual_rate' => $this->accrual_rate,
                'leave_days' => $this->leave_days,
                'maximum_leave_days' => $this->maximum_leave_days,
            ]);
        }

        $this->dispatchBrowserEvent('hide-leaveDaysModal');

        $this->resetInputFields();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Leave Details Updated Successfully!!'
        ]);
    }

    public function getLeavesTaken($employeeId)
    {
        return Leave::query()
            ->with('leave_type:id,name')
            ->where('employee_id', $employeeId)
            ->whereYear('from', date('Y'))
            ->where('hod_decision', 'approved')
            ->where('management_decision', 'approved')
            ->selectRaw('leave_type_id, SUM(days) as total_days')
            ->groupBy('leave_type_id')
            ->havingRaw('SUM(days) > 0')
            ->get()
            ->map(function ($leave) {
                return [
                    'leave_type_id' => $leave->leave_type_id,
                    'leave_type'    => $leave->leave_type?->name,
                    'days_taken'    => (float) $leave->total_days,
                ];
            });
    }

    public function getEmployeeLeaves($employeeId)
    {
        return EmployeeLeave::query()
            ->with('leave_type:id,name,active')
            ->where('employee_id', $employeeId)
            ->whereHas('leave_type', function ($query) {
                $query->where('active', true);
            })
            ->get()
            ->map(function ($leave) {
                return [
                    'leave_type_id'         => $leave->leave_type_id,
                    'leave_type'            => $leave->leave_type?->name ?? 'N/A',
                    'acrual_rate'           => (float) ($leave->acrual_rate ?? 0),
                    'available_leave_days'  => (float) ($leave->available_leave_days ?? 0),
                    'maximum_leave_days'    => (float) ($leave->maximum_leave_days ?? 0),
                ];
            });
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (isset($this->search)) {
               
            return view('livewire.employees.leaves.index',[
                'employees' => Employee::query()->where('employee_number','like', '%'.$this->search.'%')
                ->orWhere(DB::raw("concat(name, ' ', surname)"),'like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10),
            ]);
        }{
            return view('livewire.employees.leaves.index',[
                'employees' => Employee::where('archive', 0)->orderBy('name','asc')->paginate(10)
            ]);
        }
     
    }
}
