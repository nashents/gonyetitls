<?php

namespace App\Http\Livewire\Leaves;

use App\Models\DepartmentHead;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $range_from;
    public $range_to;

    public $departments;
    public $department_id;
    public $ignore_public_holidays;
    public $selectedEmployee;
    public $selected_employee;
    public $is_backdated = False;
    private $leaves;
    public $leave_id;
    public $user_id;
    public $leave_types;
    public $leave_type_id;
    public $name;
    public $hod_decision;
    public $management_decision;
    public $surname;
    public $email;
    public $to;
    public $from;
    public $reason;
    public $available_leave_days;
    public $days;
    public $days_calculation;
    public $employee_departments;


    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
       
        'available_leave_days' => 'required',
        'to' => 'required',
        'from' => 'required',
        'reason' => 'required',
        'leave_type_id' => 'required',
    ];

    private function resetInputFields(){
       
        $this->selectedEmployee = Null;
        $this->available_leave_days = 0;
        $this->to = Null;
        $this->from = Null;
        $this->reason = Null;
        $this->leave_type_id = Null;
        $this->ignore_public_holidays = False;
        $this->is_backdated = False;
    }

    public function mount(){
        $this->selected_employee = Auth::user()->employee;
        $this->departments = $this->selected_employee->departments;
        $this->available_leave_days =  0;
        $this->leave_types = LeaveType::where('active',True)->orderBy('name','asc')->get();
        $user = Auth::user();
        $employee = $user->employee;
        $company = $employee->company;
        $this->days_calculation = $company?->days_calculation;
        
    }

    
    public function refresh($category){

        if($category == "leave_types"){
            $this->leave_types = LeaveType::where('active',True)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Leave Types Refreshed Successfully!!."
            ]);
        }
    }

     public function updatedLeaveTypeId($id)
    {
        $this->available_leave_days = 0;

        if (is_null($id) || is_null($this->selected_employee?->id)) {
            return;
        }

        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return;
        }

        // Backward compatibility: Annual leave still comes from employees.leave_days
        if (strtolower($leaveType->name) === 'annual') {
            $this->available_leave_days = (float) ($this->selected_employee->leave_days ?? 0);
            return;
        }

        // Other leave types come from employee_leaves.available_leave_days
        $employeeLeave = EmployeeLeave::where('employee_id', $this->selected_employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();

        $this->available_leave_days = (float) ($employeeLeave->available_leave_days ?? 0);
    }

    public function updatedSelectedEmployee($id)
    {
        $this->available_leave_days = 0;

        if (!is_null($id)) {
            $this->selected_employee = Employee::with('departments')->find($id);

            if (!$this->selected_employee) {
                return;
            }

            $this->employee_departments = $this->selected_employee->departments;

            // Refresh leave balance based on currently selected leave type
            if (!is_null($this->leave_type_id)) {
                $this->updatedLeaveTypeId($this->leave_type_id);
            }
        }
    }

   

    public function store(){
       
        if ($this->department_id) {

            $now = Carbon::now();
            $start = Carbon::parse($this->from);
            $end = Carbon::parse($this->to);

            $isBackdated = $start->lt($now->startOfDay()); // Before today
            $isEmergency = !$isBackdated && $start->lt($now->addHours(24)); // Less than 24hrs ahead

            $leave = new Leave;
            $leave->user_id = Auth::user()->id;
            $leave->employee_id = $this->selected_employee->id;
            $leave->to = $this->to;
            $leave->from = $this->from;
            $leave->ignore_public_holidays = $this->ignore_public_holidays ?? False;
            $leave->is_backdated = $this->is_backdated;
            $leave->is_emergency = $isEmergency;
            $leave->leave_type_id = $this->leave_type_id;
            $leave->department_id = $this->department_id;
            $leave->days = $this->days;
            $leave->reason = $this->reason;


            //checking if employee is a department head or a manager
            $hod = DepartmentHead::where('employee_id', $this->selected_employee->id)->first();
            $ranks = $this->selected_employee->ranks;
            foreach($ranks as $rank){
                $rank_names[] = $rank->name;
            }
             
            if (in_array('Management', $rank_names) || isset($hod)) {
                $leave->hod_decision = 'approved';
                $leave->management_decision = 'pending';
            }else {
                $department_heads = DepartmentHead::all();
                $department_with_department_head = DepartmentHead::where('department_id',$this->department_id)->first();
             
                if ($department_heads->count()>0) {
                    
                    if (isset($department_with_department_head)) {
                        $leave->hod_decision = 'pending';
                        $leave->management_decision = 'pending';
                    }else {
                        $leave->hod_decision = 'approved';
                        $leave->management_decision = 'pending';
                    }
                }else {
                  
                    $leave->hod_decision = 'approved';
                    $leave->management_decision = 'pending';
                }


            }

            $leave->save();

            $this->dispatchBrowserEvent('hide-leaveModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Leave Application Submitted Successfully!!"
            ]);
        }else {
            $this->dispatchBrowserEvent('hide-leaveModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Assign employee to a department before leave application!!"
            ]);
        }


    }

    public function edit($id){
        $leave = Leave::find($id);
       
        $this->leave_id = $leave->id;
        $this->user_id = $leave->user_id;
      
        $this->hod_decision = $leave->hod_decision;
        $this->management_decision = $leave->management_decision;
        $this->selectedEmployee = $leave->employee_id;
        $this->selected_employee = Employee::find($leave->employee_id);
        $this->available_leave_days =  $this->selected_employee->leave_days;
        $this->department_id = $leave->department_id;
        $this->leave_type_id = $leave->leave_type_id;
        $this->ignore_public_holidays = $leave->ignore_public_holidays ?? False;
        $this->to = $leave->to;
        $this->from = $leave->from;
        $this->days = $leave->days;
        $this->reason = $leave->reason;
        $this->dispatchBrowserEvent('show-leaveEditModal');

    }

        public function update()
        {
              if ($this->department_id) {

                    $now = Carbon::now();
                    $start = Carbon::parse($this->from);
                    $end = Carbon::parse($this->to);

                    $isBackdated = $start->lt($now->startOfDay()); // Before today
                    $isEmergency = !$isBackdated && $start->lt($now->addHours(24)); // Less than 24hrs ahead

                    $leave =  Leave::find($this->leave_id);
                    $leave->user_id = Auth::user()->id;
                    $leave->employee_id = $this->selected_employee->id;
                    $leave->to = $this->to;
                    $leave->from = $this->from;
                    $leave->ignore_public_holidays = $this->ignore_public_holidays ?? False;
                    $leave->is_backdated = $this->is_backdated;
                    $leave->is_emergency = $isEmergency;
                    $leave->leave_type_id = $this->leave_type_id;
                    $leave->department_id = $this->department_id;
                    $leave->days = $this->days;
                    $leave->reason = $this->reason;


                    //checking if employee is a department head or a manager
                    $hod = DepartmentHead::where('employee_id', $this->selected_employee->id)->first();
                    $ranks = $this->selected_employee->ranks;
                    foreach($ranks as $rank){
                        $rank_names[] = $rank->name;
                    }
                    
                    if (in_array('Management', $rank_names) || isset($hod)) {
                        $leave->hod_decision = 'approved';
                        $leave->management_decision = 'pending';
                    }else {
                        $department_heads = DepartmentHead::all();
                        $department_with_department_head = DepartmentHead::where('department_id',$this->department_id)->first();
                    
                        if ($department_heads->count()>0) {
                            
                            if (isset($department_with_department_head)) {
                                $leave->hod_decision = 'pending';
                                $leave->management_decision = 'pending';
                            }else {
                                $leave->hod_decision = 'approved';
                                $leave->management_decision = 'pending';
                            }
                        }else {
                        
                            $leave->hod_decision = 'approved';
                            $leave->management_decision = 'pending';
                        }


                    }

                    $leave->update();

                    $this->dispatchBrowserEvent('hide-leaveModal');
                    $this->resetInputFields();
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'success',
                        'message'=>"Leave Application Submitted Successfully!!"
                    ]);
                }else {
                    $this->dispatchBrowserEvent('hide-leaveModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Assign employee to a department before leave application!!"
                    ]);
                }
        }

     public function updatedFrom()
    {
        $this->recalculateDays();
    }

    public function updatedTo()
    {
        $this->recalculateDays();
    }

    public function updatedDaysCalculation()
    {
        $this->recalculateDays();
    }

    public function updatedIgnorePublicHolidays()
    {
        $this->recalculateDays();
    }
    

    public function recalculateDays()
    {
        if (blank($this->from) || blank($this->to)) {
            $this->days = 0;
            return;
        }

        $this->days = $this->calculateLeaveDays(
            $this->from,
            $this->to,
            $this->days_calculation ?? 'include_weekends',
            $this->ignore_public_holidays
        );

    }

    public function calculateLeaveDays($from, $to, $daysCalculation = 'include_weekends', $ignore_public_holidays = false)
    {
        $startDate = Carbon::parse($from)->startOfDay();
        $endDate   = Carbon::parse($to)->startOfDay();

        if ($startDate->gt($endDate)) {
            return 0;
        }

        $ignore_public_holidays = filter_var($ignore_public_holidays, FILTER_VALIDATE_BOOLEAN);

        $publicHolidays = collect();

        if (! $ignore_public_holidays) {
            $publicHolidays = PublicHoliday::whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ])
            ->pluck('date')
            ->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true]);
        }

        $days = 0;
        $weekendCountByWeek = [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $currentDate = $date->toDateString();

            if (! $ignore_public_holidays && isset($publicHolidays[$currentDate])) {
                continue;
            }

            if ($daysCalculation === 'exclude_weekends') {
                if (! $date->isWeekend()) {
                    $days++;
                }
            } elseif ($daysCalculation === 'one_weekend_day') {
                if (! $date->isWeekend()) {
                    $days++;
                } else {
                    $weekKey = $date->format('o-W');

                    if (($weekendCountByWeek[$weekKey] ?? 0) < 1) {
                        $days++;
                        $weekendCountByWeek[$weekKey] = 1;
                    }
                }
            } else {
                $days++;
            }
        }

        return $days;
    }

    public function render()
    {
        
        if (filled($this->search)) {
             return view('livewire.leaves.index',[
                'leaves' => Leave::where('days', 'like', '%' . $this->search . '%')
                    ->orWhere('reason','like', '%' . $this->search . '%')
                    ->orWhereHas('employee', function ($q) {
                        $q->where(DB::raw("concat(name, ' ', surname)"), 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('user', function ($q) {
                        $q->where(DB::raw("concat(name, ' ', surname)"), 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('department', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('leave_type', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })->paginate(10)
            ]);
          
        }else{
            return view('livewire.leaves.index',[
                'leaves' => Leave::where('employee_id',Auth::user()->employee->id)->latest()->paginate(10)
            ]);
        }
       
    }
}
