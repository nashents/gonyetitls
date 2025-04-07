<?php

namespace App\Http\Livewire\Leaves;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Leave;
use Livewire\Component;
use App\Models\Employee;
use App\Models\LeaveType;
use Livewire\WithPagination;
use App\Models\DepartmentHead;
use Illuminate\Support\Facades\Auth;

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
    }

    public function mount(){
      
        $this->employees = Employee::orderBy('name', 'asc')
        ->orderBy('surname', 'asc')->get();
        $this->leave_types = LeaveType::orderBy('name','asc')->get();
        
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
          $this->selected_employee = Employee::find($id);
          $this->available_leave_days =  $this->selected_employee->leave_days;
        }
    }

    public function store(){

        $departments = $this->selected_employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = $this->selected_employee->user->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = $this->selected_employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        $employee_department = $this->selected_employee->departments->first();

        if (isset($employee_department)) {

            $leave = new Leave;
            $leave->user_id = Auth::user()->id;
            $leave->employee_id = $this->selected_employee->id;
            $leave->to = $this->to;
            $leave->from = $this->from;
            $leave->leave_type_id = $this->leave_type_id;
            $leave->department_id = $employee_department->id;
            $leave->days = $this->days;
            $leave->reason = $this->reason;

            $hod = DepartmentHead::where('employee_id', $this->selected_employee->id)->first();
             
            if (in_array('Management', $rank_names) || isset($hod)) {
                $leave->hod_decision = 'approved';
                $leave->management_decision = 'pending';
            }else {
                $department_heads = DepartmentHead::all();
                $department_with_department_head = DepartmentHead::where('department_id',$employee_department->id)->first();
             
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
        $this->to = $leave->to;
        $this->from = $leave->from;
        $this->days = $leave->days;
        $this->reason = $leave->reason;
        $this->dispatchBrowserEvent('show-leaveEditModal');

        }

        public function update()
        {
            if ($this->leave_id) {
                $departments = $this->selected_employee->departments;
                foreach($departments as $department){
                    $department_names[] = $department->name;
                }
                $roles = $this->selected_employee->user->roles;
                foreach($roles as $role){
                    $role_names[] = $role->name;
                }
                $ranks = $this->selected_employee->ranks;
                foreach($ranks as $rank){
                    $rank_names[] = $rank->name;
                }
                $employee_department = $this->selected_employee->departments->first();
        
                if (isset($employee_department)) {
        
                    $leave =  Leave::find($this->leave_id);
                    $leave->user_id = Auth::user()->id;
                    $leave->employee_id = $this->selected_employee->id;
                    $leave->to = $this->to;
                    $leave->from = $this->from;
                    $leave->leave_type_id = $this->leave_type_id;
                    $leave->department_id = $employee_department->id;
                    $leave->days = $this->days;
                    $leave->reason = $this->reason;
        
                    $hod = DepartmentHead::where('employee_id', $this->selected_employee->id)->first();
                     
                    if (in_array('Management', $rank_names) || isset($hod)) {
                        $leave->hod_decision = 'approved';
                        $leave->management_decision = 'pending';
                    }else {
                        $department_heads = DepartmentHead::all();
                        $department_with_department_head = DepartmentHead::where('department_id',$employee_department->id)->first();
                     
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
        
                    $this->dispatchBrowserEvent('hide-leaveEditModal');
                    $this->resetInputFields();
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'success',
                        'message'=>"Leave Application Updated Successfully!!"
                    ]);
                }else {
                    $this->dispatchBrowserEvent('hide-leaveModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Assign employee to a department before leave application!!"
                    ]);
                }
        
            }
        }
    public function render()
    {
        if (isset($this->from) && isset($this->to)) {
            $startDate = Carbon::parse($this->from);
            $endDate = Carbon::parse($this->to);
            $this->days = $startDate->diffInDays($endDate) + 1;
    
        }
     
        $this->leave_types = LeaveType::orderBy('name','asc')->get();
       
        return view('livewire.leaves.manage',[
            'leaves' => Leave::latest()->paginate(10)
        ]);
    }
}
