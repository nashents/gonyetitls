<?php

namespace App\Http\Livewire\Leaves;

use App\Exports\LeavesExport;
use App\Models\DepartmentHead;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Manage extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $range_from;
    public $range_to;
    
    public $employees;
    public $selectedEmployee;
    public $is_backdated = False;
    public $ignore_public_holidays = False;
    public $employee_departments;
    public $department_id;
    public $selected_employee;
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
    public $leave_filter = 'all';


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
       
        $this->selectedEmployee = '';
        $this->available_leave_days = '';
        $this->to = '';
        $this->from = '';
        $this->reason = '';
        $this->leave_type_id = '';
        $this->is_backdated = False;
        $this->ignore_public_holidays = False;
    }

     public function exportLeavesCSV(Excel $excel){
        return $excel->download(new LeavesExport($this->range_from, $this->range_to, $this->leave_filter,  $this->search), 'leaves_' .time().'.csv', Excel::CSV);
    }
    public function exportLeavesPDF(Excel $excel){
        return $excel->download(new LeavesExport($this->range_from, $this->range_to, $this->leave_filter,  $this->search), 'leaves_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportLeavesExcel(Excel $excel){
        return $excel->download(new LeavesExport($this->range_from, $this->range_to, $this->leave_filter,  $this->search), 'leaves_' .time().'.xlsx');
    }

    public function mount(){
        $this->employees = Employee::with('user')
        ->whereHas('user', function ($query) {
            $query->where('category', '!=', 'admin');
        })
        ->orderBy('name', 'asc')
        ->orderBy('surname', 'asc')
        ->get();
        $this->leave_types = LeaveType::orderBy('name','asc')->get();
        $this->employee_departments = collect();

        $user = Auth::user();
        $employee = $user->employee;
        $company = $employee->company;
        $this->days_calculation = $company?->days_calculation;
       
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
          $this->selected_employee = Employee::find($id);
          $this->employee_departments = $this->selected_employee->departments;
          $this->available_leave_days =  $this->selected_employee->leave_days;
        }
    }


    public function store(){

        if (isset($this->department_id)) {

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
            $leave->is_backdated = $this->is_backdated;
            $leave->ignore_public_holidays = $this->ignore_public_holidays;
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
                $employee_department = $this->employee_departments->first();
                $department_with_department_head = DepartmentHead::where('department_id', $employee_department?->id)->first();
             
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
                'message'=>"Application Submitted Successfully!!"
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
        $this->leave_type_id = $leave->leave_type_id;
        $this->ignore_public_holidays = $leave->ignore_public_holidays;
        $this->to = $leave->to;
        $this->from = $leave->from;
        $this->days = $leave->days;
        $this->reason = $leave->reason;
        $this->dispatchBrowserEvent('show-leaveEditModal');

        }

        public function update()
        {
            
        if (isset($this->department_id)) {

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
            $leave->is_backdated = $this->is_backdated;
            $leave->ignore_public_holidays = $this->ignore_public_holidays;
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
                $employee_department = $this->employee_departments->first();
                $department_with_department_head = DepartmentHead::where('department_id', $employee_department?->id)->first();
             
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
                'message'=>"Application Submitted Successfully!!"
            ]);
        }else {
            $this->dispatchBrowserEvent('hide-leaveModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Assign employee to a department before leave application!!"
            ]);
        }
          
        }

    public function updatedLeaveFilter($filter){
       
    }

    public function refresh($category){

        if($category == "leave_types"){
            $this->leave_types = LeaveType::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Leave Types Refreshed Successfully!!."
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
            ])->pluck('date')
            ->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true]);
        }

        $days = 0;
        $weekendIncluded = false;

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $currentDate = $date->toDateString();

            // Only skip public holidays when they must be excluded
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
                } elseif (! $weekendIncluded) {
                    $days++;
                    $weekendIncluded = true;
                }
            } else {
                $days++;
            }
        }

        return $days;
    }

        
    public function render()
    {
            

           $leaves = Leave::with('employee','user','department','leave_type')
            ->filterLeaves($this->leave_filter, $this->range_from, $this->range_to, $this->search)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

            return view('livewire.leaves.manage', [
                'leaves' =>  $leaves
            ]);
    }
}
