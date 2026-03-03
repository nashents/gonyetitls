<?php

namespace App\Http\Livewire\Leaves;

use App\Models\Leave;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DepartmentHead;
use Illuminate\Support\Facades\Auth;

class Approved extends Component
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

        $ranks = Auth::user()->employee->ranks;
        foreach ($ranks as $rank) {
            $rank_names[] = $rank->name;
        }

        if (in_array('Management',$rank_names)) {
        $leave->management_id = Auth::user()->id;
        $leave->management_decision = $this->decision;
        if ($this->decision == "approved") {
            $leave->user->employee->leave_days = $leave->user->employee->leave_days - $leave->days;
        }
        $leave->management_reply = $this->reason;

        }elseif (in_array('HOD',$rank_names)) {
        $leave->hod_id = Auth::user()->id;
        $leave->hod_decision = $this->decision;
        $leave->hod_reply = $this->reason;
        }
        $leave->update();

        if ($this->decision == "approved") {
            $this->dispatchBrowserEvent('hide-decisionModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Application Approved Successfully!!"
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
            $query->where('management_decision', 'approved');
        } elseif (isset($this->hod)) {
            $query->where('hod_decision', 'approved')
                ->where('department_id', $this->hod->department->id);
            // or $this->hod->department_id if you have the FK on the model
        }

        // Single return
        return view('livewire.leaves.approved', [
            'leaves' => $query->latest()->paginate(10),
        ]);
    }
}
