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
    }

    public function mount(){
        $this->selected_employee = Auth::user()->employee;
        $this->departments = $this->selected_employee->departments;
        $this->available_leave_days =  $this->selected_employee->leave_days;
        $this->leave_types = LeaveType::orderBy('name','asc')->get();
        
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
               
        
                if ($this->department_id) {
        
                    $leave =  Leave::find($this->leave_id);
                    $leave->user_id = Auth::user()->id;
                    $leave->employee_id = $this->selected_employee->id;
                    $leave->to = $this->to;
                    $leave->from = $this->from;
                    $leave->leave_type_id = $this->leave_type_id;
                    $leave->department_id = $this->department_id;
                    $leave->days = $this->days;
                    $leave->reason = $this->reason;
        
                    $hod = DepartmentHead::where('employee_id', $this->selected_employee->id)->first();
                     
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
       
        return view('livewire.leaves.index',[
            'leaves' => Leave::where('employee_id',Auth::user()->employee->id)->latest()->paginate(10)
        ]);
    }
}
