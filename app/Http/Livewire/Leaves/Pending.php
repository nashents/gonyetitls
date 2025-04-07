<?php

namespace App\Http\Livewire\Leaves;

use App\Models\Leave;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DepartmentHead;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
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


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'decision' => 'required',
        'reason' => 'required|string',
    ];
    public function mount(){
        $this->hod = DepartmentHead::where('employee_id', Auth::user()->employee->id)->first();
        
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

    public function authorize($id, $category){
        $this->category = $category;
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

        if ($this->category == "hr") {
        $leave->management_id = Auth::user()->id;
        $leave->management_decision = $this->decision;
        $leave->management_reply = $this->reason;
        $leave->hod_id = Auth::user()->id;
        $leave->hod_decision = $this->decision;
        $leave->hod_reply = $this->reason;
        $leave->update();
        if ($this->decision == "approved") {
            $employee =  $leave->employee;
            $employee->leave_days =  $employee->leave_days - $leave->days;
            $employee->update();
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
     
        if ((in_array('Admin', $this->role_names) && in_array('Human Resources', $this->department_names)) || (in_array('Management', $this->rank_names) && in_array('Human Resources', $this->department_names)) || in_array('Super Admin', $this->role_names)) {
            return view('livewire.leaves.pending',[
                'leaves' => Leave::where('status','pending')
                ->where('employee_id' ,'!=', Auth::user()->employee->id)
                ->latest()->paginate(10),
            ]);
        }elseif(isset($this->hod)){
            return view('livewire.leaves.pending',[
                'leaves' => Leave::where('status','pending')
                ->where('employee_id' ,'!=', Auth::user()->employee->id)
                ->where('department_id' , $this->hod->department->id)
                ->latest()->paginate(10),
            ]);
        }
       
    }
}
