<?php

namespace App\Http\Livewire\Leaves;

use App\Mail\LeaveApplicationMail;
use App\Models\DepartmentHead;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class Rejected extends Component
{
     use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $range_from;
    public $range_to;
    
    private $leaves;
    public $leave_id;
    public $decision;
    public $reason;
    public $rank_names;
    public $role_names;
    public $department_names;
    public $category;
    public $hod;
    public $company;

    public function mount(){
        
        $this->hod = DepartmentHead::where('employee_id', Auth::user()->employee->id)->first();
        $this->company = Auth::user()->employee->company;

        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
        $this->rank_names[] = $rank->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
        $this->role_names[] = $role->name;
        }
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
        $this->department_names[] = $department->name;
        }

    }

    public function authorize($id){
        $leave = Leave::find($id);
        $this->leave_id = $id;
        $this->dispatchBrowserEvent('show-decisionModal');
    }

    public function decision(){

     
        $leave = Leave::find($this->leave_id);
        if ($this->category == "hr") {

        $leave->management_id = Auth::user()->id;
        $leave->management_decision = $this->decision;
        $leave->management_reply = $this->reason;
        if ($leave->hod_id == Null) {
            $leave->hod_id = Auth::user()->id;
            $leave->hod_decision = $this->decision;
            $leave->hod_reply = $this->reason;
        }
        $leave->status = $this->decision;
        $leave->update();

       if ($this->decision == "approved") {

                $employee = $leave->employee;
                $leaveType = $leave->leave_type;

                if ($employee && $leaveType) {

                    $employeeLeave = EmployeeLeave::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->first();

                        if ($employeeLeave && is_numeric($leave->days)) {

                            $employeeLeave->available_leave_days = max(
                                0,
                                ($employeeLeave->available_leave_days ?? 0) - $leave->days
                            );

                            $employeeLeave->save();
                        }

                        // Backward compatibility
                        if (strtolower(trim($leaveType->name)) === 'annual') {

                            $employee->leave_days = max(
                                0,
                                ($employee->leave_days ?? 0) - $leave->days
                            );

                            $employee->save();
                        }

                        if ($employee->personal_email) {
                            Mail::to($employee->personal_email)
                                ->send(new LeaveApplicationMail($this->company, $leave));
                        }
                }
            }

        }elseif ($this->category == "hod") {
            $leave->hod_id = Auth::user()->id;
            $leave->hod_decision = $this->decision;
            $leave->hod_reply = $this->reason;
            $leave->update();
        }
      

        if ($this->decision == "approved") {
            $this->dispatchBrowserEvent('hide-decisionModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Leave Application Approved Successfully!!"
            ]);
            return redirect()->route('leaves.approved');
        }else {
            $this->dispatchBrowserEvent('hide-decisionModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Application Rejected Successfully!!"
            ]);
            return redirect()->route('leaves.rejected');

        }



    }

    public function render()
    {
            
        // Define readable flags first
        $inHrDept      = in_array('Human Resources', $this->department_names ?? []);
        $isAdmin       = in_array('Admin', $this->role_names ?? []);
        $isSuperAdmin  = in_array('Super Admin', $this->role_names ?? []);
        $isManagement  = in_array('Management', $this->rank_names ?? []);

        // Who can see ALL pending leaves (management level)
        $canSeeAllPending =
            $isSuperAdmin ||
            ($isAdmin && $inHrDept) ||
            ($isManagement && $inHrDept);

        // Base query
        $query = Leave::query();

        if ($canSeeAllPending) {
            $query->where('management_decision', 'rejected');
        } elseif (isset($this->hod)) {
            $query->where('hod_decision', 'rejected')
                ->where('department_id', $this->hod->department->id);
            // or $this->hod->department_id if you have the FK on the model
        }

        // Single return
        return view('livewire.leaves.rejected', [
            'leaves' => $query->latest()->paginate(10),
        ]);
    }
}
