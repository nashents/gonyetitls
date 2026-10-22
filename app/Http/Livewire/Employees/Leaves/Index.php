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
    use \App\Http\Livewire\Concerns\FiltersStaff;
    protected $paginationTheme = 'bootstrap';
    public $search;
    // 'all' (default), 'drivers' or 'admin' — applies to the list and every export.
    public $staff_type = 'all';
    protected $queryString = ['search', 'staff_type' => ['except' => 'all']];

    private $employees;
    public $employee;
    public $employee_id;
    public $leave_days;
    public $accrual_rate;
    public $maximum_leave_days;
    public $leave_count;
    public $leave_type_id;
    public $leave_types ;

    public $enforce_accrual_rate = true;
    public $enforce_leave_days = true;
    public $enforce_maximum_leave_days = true;
    // Enforce scope: 'all', 'admin' or 'drivers' (Employee::scopeOfStaffType), and one leave type or all.
    public $enforce_staff_type = 'all';
    // Optional branch, combinable with the staff type (e.g. admin employees of one branch).
    public $enforce_branch_id;
    public $enforce_leave_type_id;
    // Optional overrides (one leave type only); blank = use the leave type's default.
    public $enforce_accrual_rate_value;
    public $enforce_leave_days_value;
    public $enforce_maximum_leave_days_value;

    public function mount(){
        $this->leave_types = LeaveType::where('active',True)->orderBy('name','asc')->get();
        $this->resetPage();
    }

    public function enforceLeaveTypeDefaults()
    {
        if (!$this->enforce_accrual_rate && !$this->enforce_leave_days && !$this->enforce_maximum_leave_days) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'danger',
                'message' => 'Select at least one field to enforce.'
            ]);
            return;
        }

        if (!in_array($this->enforce_staff_type, ['all', 'admin', 'drivers'], true)) {
            $this->enforce_staff_type = 'all';
        }

        // Overrides only make sense for a single leave type.
        $overrides = [];
        if (filled($this->enforce_leave_type_id)) {
            $this->validate([
                'enforce_accrual_rate_value' => 'nullable|numeric|min:0',
                'enforce_leave_days_value' => 'nullable|numeric|min:0',
                'enforce_maximum_leave_days_value' => 'nullable|numeric|min:0',
            ]);
            $overrides = array_filter([
                'acrual_rate' => $this->enforce_accrual_rate_value,
                'available_leave_days' => $this->enforce_leave_days_value,
                'maximum_leave_days' => $this->enforce_maximum_leave_days_value,
            ], 'filled');
        }

        $enforceAccrualRate = $this->enforce_accrual_rate;
        $enforceLeaveDays = $this->enforce_leave_days;
        $enforceMaximumLeaveDays = $this->enforce_maximum_leave_days;
        $staffType = $this->enforce_staff_type;
        $branch = filled($this->enforce_branch_id) ? \App\Models\Branch::find($this->enforce_branch_id) : null;
        $branchFilter = ['branch_id' => $branch?->id];

        $leaveTypes = LeaveType::where('active', true)
            ->when(filled($this->enforce_leave_type_id), fn ($q) => $q->where('id', $this->enforce_leave_type_id))
            ->get();

        $enforced = 0;

        DB::transaction(function () use ($leaveTypes, &$enforced, $enforceAccrualRate, $enforceLeaveDays, $enforceMaximumLeaveDays, $staffType, $branchFilter, $overrides) {
            Employee::where('archive', 0)
                ->ofStaffType($staffType)
                ->filterBy($branchFilter)
                ->select('id')
                ->chunkById(200, function ($employees) use ($leaveTypes, &$enforced, $enforceAccrualRate, $enforceLeaveDays, $enforceMaximumLeaveDays, $overrides) {
                    foreach ($employees as $employee) {
                        foreach ($leaveTypes as $leaveType) {
                            $defaults = array_merge([
                                'acrual_rate' => $leaveType->monthly_accrual_rate ?? 0,
                                'available_leave_days' => $leaveType->entitlement ?? 0,
                                'maximum_leave_days' => $leaveType->maximum_leave_days ?? 0,
                            ], $overrides);

                            $employeeLeave = EmployeeLeave::where('employee_id', $employee->id)
                                ->where('leave_type_id', $leaveType->id)
                                ->first();

                            if ($employeeLeave) {
                                $values = array_intersect_key($defaults, array_filter([
                                    'acrual_rate' => $enforceAccrualRate,
                                    'available_leave_days' => $enforceLeaveDays,
                                    'maximum_leave_days' => $enforceMaximumLeaveDays,
                                ]));

                                $employeeLeave->update($values);
                            } else {
                                EmployeeLeave::create(array_merge($defaults, [
                                    'employee_id' => $employee->id,
                                    'leave_type_id' => $leaveType->id,
                                ]));
                            }

                            $enforced++;

                            // Backward compatibility: keep the Employee table in sync, Annual Leave only
                            if (strtolower($leaveType->name) === 'annual') {
                                $employeeDefaults = [
                                    'accrual_rate' => $defaults['acrual_rate'],
                                    'leave_days' => $defaults['available_leave_days'],
                                    'maximum_leave_days' => $defaults['maximum_leave_days'],
                                ];

                                // New records are fully seeded from the leave type regardless of the checkboxes
                                $employeeValues = $employeeLeave
                                    ? array_intersect_key($employeeDefaults, array_filter([
                                        'accrual_rate' => $enforceAccrualRate,
                                        'leave_days' => $enforceLeaveDays,
                                        'maximum_leave_days' => $enforceMaximumLeaveDays,
                                    ]))
                                    : $employeeDefaults;

                                if (!empty($employeeValues)) {
                                    Employee::where('id', $employee->id)->update($employeeValues);
                                }
                            }
                        }
                    }
                });
        });

        $this->dispatchBrowserEvent('hide-enforceLeaveTypeDefaultsModal');

        $scopeLabel = ['all' => 'all employees', 'admin' => 'admin employees', 'drivers' => 'drivers'][$staffType]
            .($branch ? " in {$branch->name}" : '');

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Leave Type Defaults Enforced for {$scopeLabel}!! ({$enforced} record(s) updated)"
        ]);
    }

    public function exportEmployeesLeaveCSV(Excel $excel){

        return $excel->download(new EmployeesLeaveExport($this->staff_type, $this->staffFilters()), $this->exportFileName('csv'), Excel::CSV);
    }
    public function exportEmployeesLeavePDF(Excel $excel){

        return $excel->download(new EmployeesLeaveExport($this->staff_type, $this->staffFilters()), $this->exportFileName('pdf'), Excel::DOMPDF);
    }
    public function exportEmployeesLeaveExcel(Excel $excel){
        return $excel->download(new EmployeesLeaveExport($this->staff_type, $this->staffFilters()), $this->exportFileName('xlsx'));
    }

    private function exportFileName(string $extension): string
    {
        $suffix = (['drivers' => '_drivers', 'admin' => '_admin'][$this->staff_type] ?? '').$this->staffFilterFileSuffix();
        return "employees_leave_data{$suffix}.{$extension}";
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

    public function updatingStaffType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Employee::query()->ofStaffType($this->staff_type)->filterBy($this->staffFilters());

        if (filled($this->search)) {
            // Grouped so the staff-type filter still applies to both search terms.
            $query->where(function ($q) {
                $q->where('employee_number','like', '%'.$this->search.'%')
                  ->orWhere(DB::raw("concat(name, ' ', surname)"),'like', '%'.$this->search.'%');
            });
        } else {
            $query->where('archive', 0);
        }

        return view('livewire.employees.leaves.index',[
            'employees' => $query->orderBy('name','asc')->paginate(10),
        ]);
     
    }
}
