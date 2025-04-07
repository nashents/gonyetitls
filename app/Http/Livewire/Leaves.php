<?php

namespace App\Http\Livewire;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Leave;
use Livewire\Component;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\DepartmentHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Leaves extends Component
{
    public $leaves;
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


    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'name' => 'required',
        'surname' => 'required',
        'email' => 'required',
        'available_leave_days' => 'required',
        'to' => 'required',
        'from' => 'required',
        'reason' => 'required',
        'leave_type_id' => 'required',
    ];

    private function resetInputFields(){
        $this->name = '';
        $this->surname = '';
        $this->email = '';
        $this->available_leave_days = '';
        $this->to = '';
        $this->from = '';
        $this->reason = '';
        $this->leave_type_id = '';
    }

    public function mount(){
        $this->leaves = Leave::where('user_id', Auth::user()->id)->latest()->get();
        $this->leave_types = LeaveType::latest()->get();
        $this->user_id = Auth::user()->id;
        $this->name = Auth::user()->name;
        $this->surname = Auth::user()->surname;
        $this->email = Auth::user()->email;
        $this->available_leave_days = Auth::user()->employee->leave_days;
    }

    public function store(){
        // try {
        $datetime1 = new DateTime($this->to);
        $datetime2 = new DateTime($this->from);
        $interval = $datetime1->diff($datetime2);
        $days = $interval->format('%a');

        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }

        if (Auth::user()->employee->departments->first()) {
            $leave = new Leave;
            $leave->user_id = $this->user_id;
            $leave->to = $this->to;
            $leave->from = $this->from;

            $hod = DepartmentHead::where('employee_id',Auth::user()->employee->id)->first();
            
            if (in_array('Management', $rank_names) || isset($hod)) {
                $leave->hod_decision = 'approved';
                $leave->management_decision = 'pending';
            }else {
                $department_heads = DepartmentHead::latest()->get();
                $department = Auth::user()->employee->departments->first();
                if ($department) {
                    $department_with_department_head = DepartmentHead::where('department_id',$department->id)->first();
                }
             
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

            $leave->leave_type_id = $this->leave_type_id;
            $leave->department_id = Auth::user()->employee->departments->first()->id;
            $leave->days = $days;
            $leave->reason = $this->reason;
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
                'message'=>"No department assigned. Failed to create Application!!"
            ]);
        }
//         }
//         catch(\Exception $e){
//         // Set Flash Message
//         $this->dispatchBrowserEvent('alert',[
//             'type'=>'error',
//             'message'=>"Something went wrong while submitting leave application!!"
//         ]);
// }

    }

    public function edit($id){
        $leave = Leave::find($id);
        $user = User::find($leave->user_id);

        $this->leave_id = $leave->id;
        $this->user_id = $leave->user_id;
        $this->name = $user->name;
        $this->surname = $user->name;
        $this->hod_decision = $leave->hod_decision;
        $this->management_decision = $leave->management_decision;
        $this->email = $user->email;
        $this->available_leave_days = Auth::user()->employee->leave_days;
        $this->leave_type_id = $leave->leave_type_id;
        $this->to = $leave->to;
        $this->from = $leave->from;
        $this->reason = $leave->reason;
        $this->dispatchBrowserEvent('show-leaveEditModal');

        }

        public function update()
        {
            if ($this->leave_id) {
                $leave = Leave::find($this->leave_id);
                $datetime1 = new DateTime($this->to);
                $datetime2 = new DateTime($this->from);
                $interval = $datetime1->diff($datetime2);
                $days = $interval->format('%a');
                $leave->update([
                    'user_id' => $this->user_id,
                    'to' => $this->to,
                    'from' => $this->from,
                    'reason' => $this->reason,
                    'days' => $days,
                    'leave_type_id' => $this->leave_type_id,
                ]);

                $this->dispatchBrowserEvent('hide-leaveEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Application Updated Successfully!!"
                ]);
            }
        }
    public function render()
    {
        $this->leaves = Leave::where('user_id', Auth::user()->id)->latest()->get();
        return view('livewire.leaves',[
            'leaves' => $this->leaves
        ]);
    }
}
