<?php

namespace App\Http\Livewire\SheqNonConformities;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqAction;
use App\Models\SheqNonConformity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search','status_filter'];
    public $status_filter = '';
    public $source_filter = '';
    public $department_filter = '';

    public $departments;
    public $employees;

    public $sheq_non_conformity_id;
    public $source;
    public $department_id;
    public $raised_by_id;
    public $date_raised;
    public $description;
    public $classification = 'minor';
    public $immediate_action;
    public $root_cause;
    public $status = 'open';
    public $effectiveness_review;

    public $action_nc_id;
    public $action_title;
    public $action_description;
    public $action_employee_id;
    public $action_due_date;
    public $action_priority = 'medium';

    protected $rules = [
        'source' => 'required',
        'department_id' => 'required',
        'date_raised' => 'required',
        'description' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->source = "";
        $this->department_id = "";
        $this->raised_by_id = "";
        $this->date_raised = "";
        $this->description = "";
        $this->classification = "minor";
        $this->immediate_action = "";
        $this->root_cause = "";
        $this->status = "open";
        $this->effectiveness_review = "";
        $this->action_title = "";
        $this->action_description = "";
        $this->action_employee_id = "";
        $this->action_due_date = "";
        $this->action_priority = "medium";
    }

    public function ncNumber(){
        $last_id = SheqNonConformity::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'NC'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $nc = new SheqNonConformity;
        $nc->user_id = Auth::user()->id;
        $nc->nc_number = $this->ncNumber();
        $nc->source = $this->source;
        $nc->department_id = $this->department_id;
        $nc->raised_by_id = $this->raised_by_id ?: Null;
        $nc->date_raised = $this->date_raised;
        $nc->description = $this->description;
        $nc->classification = $this->classification;
        $nc->immediate_action = $this->immediate_action;
        $nc->root_cause = $this->root_cause;
        $nc->status = $this->status;
        $nc->save();

        $this->dispatchBrowserEvent('hide-sheq_non_conformityModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Non-Conformity Raised Successfully!!"
        ]);
    }

    public function edit($id){
        $nc = SheqNonConformity::find($id);
        $this->sheq_non_conformity_id = $nc->id;
        $this->source = $nc->source;
        $this->department_id = $nc->department_id;
        $this->raised_by_id = $nc->raised_by_id;
        $this->date_raised = $nc->date_raised ? Carbon::parse($nc->date_raised)->format('Y-m-d') : Null;
        $this->description = $nc->description;
        $this->classification = $nc->classification;
        $this->immediate_action = $nc->immediate_action;
        $this->root_cause = $nc->root_cause;
        $this->status = $nc->status;
        $this->effectiveness_review = $nc->effectiveness_review;
        $this->dispatchBrowserEvent('show-sheq_non_conformityEditModal');
    }

    public function update(){
        $this->validate();

        $nc = SheqNonConformity::find($this->sheq_non_conformity_id);
        $nc->source = $this->source;
        $nc->department_id = $this->department_id;
        $nc->raised_by_id = $this->raised_by_id ?: Null;
        $nc->date_raised = $this->date_raised;
        $nc->description = $this->description;
        $nc->classification = $this->classification;
        $nc->immediate_action = $this->immediate_action;
        $nc->root_cause = $this->root_cause;
        $nc->effectiveness_review = $this->effectiveness_review;
        if ($this->status == 'closed' && $nc->status != 'closed') {
            $nc->closed_date = Carbon::today()->format('Y-m-d');
        }
        $nc->status = $this->status;
        $nc->update();

        $this->dispatchBrowserEvent('hide-sheq_non_conformityEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Non-Conformity Updated Successfully!!"
        ]);
    }

    public function raiseAction($id){
        $this->action_nc_id = $id;
        $nc = SheqNonConformity::find($id);
        $this->action_title = 'Corrective action for '.$nc->nc_number;
        $this->action_description = $nc->description;
        $this->dispatchBrowserEvent('show-ncActionModal');
    }

    public function storeAction(){
        $this->validate([
            'action_title' => 'required',
            'action_employee_id' => 'required',
            'action_due_date' => 'required',
        ]);

        $nc = SheqNonConformity::find($this->action_nc_id);

        $last_id = SheqAction::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;

        $sheq_action = new SheqAction;
        $sheq_action->user_id = Auth::user()->id;
        $sheq_action->action_number = 'ACT'. str_pad($next, 5, "0", STR_PAD_LEFT);
        $sheq_action->department_id = $nc->department_id;
        $sheq_action->employee_id = $this->action_employee_id;
        $sheq_action->actionable_type = SheqNonConformity::class;
        $sheq_action->actionable_id = $nc->id;
        $sheq_action->source = 'non_conformity';
        $sheq_action->reference = $nc->nc_number;
        $sheq_action->title = $this->action_title;
        $sheq_action->description = $this->action_description;
        $sheq_action->priority = $this->action_priority;
        $sheq_action->due_date = $this->action_due_date;
        $sheq_action->status = 'open';
        $sheq_action->save();

        if ($nc->status == 'open') {
            $nc->status = 'actions';
            $nc->update();
        }

        $this->dispatchBrowserEvent('hide-ncActionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Corrective Action Raised Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_non_conformity_id = $id;
        $this->dispatchBrowserEvent('show-sheq_non_conformityDeleteModal');
    }

    public function destroy(){
        $nc = SheqNonConformity::find($this->sheq_non_conformity_id);
        if ($nc) {
            $nc->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_non_conformityDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Non-Conformity Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqNonConformity::query()->with(['department','raised_by','actions']);

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->source_filter) {
            $query->where('source', $this->source_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('nc_number','like',$search)
                  ->orWhere('description','like',$search);
            });
        }

        $sheq_non_conformities = $query->orderBy('created_at','desc')->paginate(10);

        return view('livewire.sheq-non-conformities.index',[
            'sheq_non_conformities' => $sheq_non_conformities
        ]);
    }
}
