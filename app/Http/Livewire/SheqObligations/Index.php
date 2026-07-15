<?php

namespace App\Http\Livewire\SheqObligations;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqObligation;
use App\Models\SheqObligationEvaluation;
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
    public $type_filter = '';
    public $department_filter = '';
    public $status_filter = '';

    public $departments;
    public $employees;

    public $sheq_obligation_id;
    public $title;
    public $type;
    public $issuing_authority;
    public $reference_number;
    public $department_id;
    public $employee_id;
    public $issue_date;
    public $expiry_date;
    public $requirements;
    public $status = 'valid';

    public $evaluation_obligation_id;
    public $evaluation_date;
    public $compliance_status;
    public $findings;

    protected $rules = [
        'title' => 'required',
        'type' => 'required',
        'department_id' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->title = "";
        $this->type = "";
        $this->issuing_authority = "";
        $this->reference_number = "";
        $this->department_id = "";
        $this->employee_id = "";
        $this->issue_date = "";
        $this->expiry_date = "";
        $this->requirements = "";
        $this->status = "valid";
        $this->evaluation_date = "";
        $this->compliance_status = "";
        $this->findings = "";
    }

    public function obligationNumber(){
        $last_id = SheqObligation::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'OBL'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $obligation = new SheqObligation;
        $obligation->user_id = Auth::user()->id;
        $obligation->obligation_number = $this->obligationNumber();
        $obligation->title = $this->title;
        $obligation->type = $this->type;
        $obligation->issuing_authority = $this->issuing_authority;
        $obligation->reference_number = $this->reference_number;
        $obligation->department_id = $this->department_id;
        $obligation->employee_id = $this->employee_id ?: Null;
        $obligation->issue_date = $this->issue_date ?: Null;
        $obligation->expiry_date = $this->expiry_date ?: Null;
        $obligation->requirements = $this->requirements;
        $obligation->status = $this->status;
        $obligation->save();

        $this->dispatchBrowserEvent('hide-sheq_obligationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Compliance Obligation Created Successfully!!"
        ]);
    }

    public function edit($id){
        $obligation = SheqObligation::find($id);
        $this->sheq_obligation_id = $obligation->id;
        $this->title = $obligation->title;
        $this->type = $obligation->type;
        $this->issuing_authority = $obligation->issuing_authority;
        $this->reference_number = $obligation->reference_number;
        $this->department_id = $obligation->department_id;
        $this->employee_id = $obligation->employee_id;
        $this->issue_date = $obligation->issue_date ? Carbon::parse($obligation->issue_date)->format('Y-m-d') : Null;
        $this->expiry_date = $obligation->expiry_date ? Carbon::parse($obligation->expiry_date)->format('Y-m-d') : Null;
        $this->requirements = $obligation->requirements;
        $this->status = $obligation->status;
        $this->dispatchBrowserEvent('show-sheq_obligationEditModal');
    }

    public function update(){
        $this->validate();

        $obligation = SheqObligation::find($this->sheq_obligation_id);
        $obligation->title = $this->title;
        $obligation->type = $this->type;
        $obligation->issuing_authority = $this->issuing_authority;
        $obligation->reference_number = $this->reference_number;
        $obligation->department_id = $this->department_id;
        $obligation->employee_id = $this->employee_id ?: Null;
        $obligation->issue_date = $this->issue_date ?: Null;
        $obligation->expiry_date = $this->expiry_date ?: Null;
        $obligation->requirements = $this->requirements;
        $obligation->status = $this->status;
        $obligation->update();

        $this->dispatchBrowserEvent('hide-sheq_obligationEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Compliance Obligation Updated Successfully!!"
        ]);
    }

    public function evaluate($id){
        $this->evaluation_obligation_id = $id;
        $this->evaluation_date = Carbon::today()->format('Y-m-d');
        $this->dispatchBrowserEvent('show-sheq_obligationEvaluateModal');
    }

    public function storeEvaluation(){
        $this->validate([
            'evaluation_date' => 'required',
            'compliance_status' => 'required',
        ]);

        $evaluation = new SheqObligationEvaluation;
        $evaluation->sheq_obligation_id = $this->evaluation_obligation_id;
        $evaluation->user_id = Auth::user()->id;
        $evaluation->evaluation_date = $this->evaluation_date;
        $evaluation->compliance_status = $this->compliance_status;
        $evaluation->findings = $this->findings;
        $evaluation->save();

        $this->dispatchBrowserEvent('hide-sheq_obligationEvaluateModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Compliance Evaluation Recorded Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_obligation_id = $id;
        $this->dispatchBrowserEvent('show-sheq_obligationDeleteModal');
    }

    public function destroy(){
        $obligation = SheqObligation::find($this->sheq_obligation_id);
        if ($obligation) {
            $obligation->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_obligationDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Compliance Obligation Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqObligation::query()->with(['department','employee','evaluations']);

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('title','like',$search)
                  ->orWhere('obligation_number','like',$search)
                  ->orWhere('reference_number','like',$search)
                  ->orWhere('issuing_authority','like',$search);
            });
        }

        $sheq_obligations = $query->orderBy('expiry_date','asc')->paginate(10);

        return view('livewire.sheq-obligations.index',[
            'sheq_obligations' => $sheq_obligations
        ]);
    }
}
