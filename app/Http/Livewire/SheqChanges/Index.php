<?php

namespace App\Http\Livewire\SheqChanges;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqChange;
use App\Models\SheqRiskAssessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $authorization_filter = '';
    public $department_filter = '';

    public $departments;
    public $employees;
    public $assessments;

    public $sheq_change_id;
    public $department_id;
    public $requested_by_id;
    public $request_date;
    public $type = 'permanent';
    public $description;
    public $reason;
    public $sheq_risk_assessment_id;
    public $implementation_date;
    public $closeout_date;
    public $status = 'open';
    public $reason_rejected;

    protected $rules = [
        'department_id' => 'required',
        'requested_by_id' => 'required',
        'request_date' => 'required',
        'description' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->assessments = SheqRiskAssessment::orderBy('created_at','desc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->requested_by_id = "";
        $this->request_date = "";
        $this->type = "permanent";
        $this->description = "";
        $this->reason = "";
        $this->sheq_risk_assessment_id = "";
        $this->implementation_date = "";
        $this->closeout_date = "";
        $this->status = "open";
        $this->reason_rejected = "";
    }

    public function changeNumber(){
        $last_id = SheqChange::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'MOC'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $change = new SheqChange;
        $change->user_id = Auth::user()->id;
        $change->change_number = $this->changeNumber();
        $this->fill_fields($change);
        $change->authorization = 'pending';
        $change->save();

        $this->dispatchBrowserEvent('hide-sheq_changeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Change Request Created Successfully!!"
        ]);
    }

    private function fill_fields($change){
        $change->department_id = $this->department_id;
        $change->requested_by_id = $this->requested_by_id;
        $change->request_date = $this->request_date;
        $change->type = $this->type;
        $change->description = $this->description;
        $change->reason = $this->reason;
        $change->sheq_risk_assessment_id = $this->sheq_risk_assessment_id ?: Null;
        $change->implementation_date = $this->implementation_date ?: Null;
        $change->closeout_date = $this->closeout_date ?: Null;
        $change->status = $this->status;
    }

    public function edit($id){
        $change = SheqChange::find($id);
        $this->sheq_change_id = $change->id;
        $this->department_id = $change->department_id;
        $this->requested_by_id = $change->requested_by_id;
        $this->request_date = $change->request_date ? Carbon::parse($change->request_date)->format('Y-m-d') : Null;
        $this->type = $change->type;
        $this->description = $change->description;
        $this->reason = $change->reason;
        $this->sheq_risk_assessment_id = $change->sheq_risk_assessment_id;
        $this->implementation_date = $change->implementation_date ? Carbon::parse($change->implementation_date)->format('Y-m-d') : Null;
        $this->closeout_date = $change->closeout_date ? Carbon::parse($change->closeout_date)->format('Y-m-d') : Null;
        $this->status = $change->status;
        $this->dispatchBrowserEvent('show-sheq_changeEditModal');
    }

    public function update(){
        $this->validate();

        $change = SheqChange::find($this->sheq_change_id);
        $this->fill_fields($change);
        $change->update();

        $this->dispatchBrowserEvent('hide-sheq_changeEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Change Request Updated Successfully!!"
        ]);
    }

    public function approve($id){
        $change = SheqChange::find($id);
        $change->authorization = 'approved';
        $change->authorized_by_id = Auth::user()->id;
        $change->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Change Request Approved Successfully!!"
        ]);
    }

    public function reject($id){
        $this->sheq_change_id = $id;
        $this->dispatchBrowserEvent('show-sheq_changeRejectModal');
    }

    public function saveReject(){
        $change = SheqChange::find($this->sheq_change_id);
        $change->authorization = 'rejected';
        $change->authorized_by_id = Auth::user()->id;
        $change->reason_rejected = $this->reason_rejected;
        $change->update();

        $this->dispatchBrowserEvent('hide-sheq_changeRejectModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Change Request Rejected!!"
        ]);
    }

    public function delete($id){
        $this->sheq_change_id = $id;
        $this->dispatchBrowserEvent('show-sheq_changeDeleteModal');
    }

    public function destroy(){
        $change = SheqChange::find($this->sheq_change_id);
        if ($change) {
            $change->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_changeDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Change Request Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqChange::query()->with(['department','requested_by','risk_assessment','actions']);

        if ($this->authorization_filter) {
            $query->where('authorization', $this->authorization_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('change_number','like',$search)
                  ->orWhere('description','like',$search);
            });
        }

        $sheq_changes = $query->orderBy('created_at','desc')->paginate(10);

        return view('livewire.sheq-changes.index',[
            'sheq_changes' => $sheq_changes
        ]);
    }
}
