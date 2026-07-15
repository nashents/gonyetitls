<?php

namespace App\Http\Livewire\SheqActions;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqAction;
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

    public $sheq_action_id;
    public $department_id;
    public $employee_id;
    public $source;
    public $reference;
    public $title;
    public $description;
    public $priority = 'medium';
    public $due_date;
    public $status;

    public $completed_date;
    public $completion_notes;
    public $effectiveness;
    public $effectiveness_notes;

    protected $rules = [
        'title' => 'required',
        'employee_id' => 'required',
        'due_date' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->employee_id = "";
        $this->source = "";
        $this->reference = "";
        $this->title = "";
        $this->description = "";
        $this->priority = "medium";
        $this->due_date = "";
        $this->completed_date = "";
        $this->completion_notes = "";
        $this->effectiveness = "";
        $this->effectiveness_notes = "";
    }

    public function actionNumber(){
        $last_id = SheqAction::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'ACT'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $sheq_action = new SheqAction;
        $sheq_action->user_id = Auth::user()->id;
        $sheq_action->action_number = $this->actionNumber();
        $sheq_action->department_id = $this->department_id ?: Null;
        $sheq_action->employee_id = $this->employee_id ?: Null;
        $sheq_action->source = $this->source ?: 'other';
        $sheq_action->reference = $this->reference;
        $sheq_action->title = $this->title;
        $sheq_action->description = $this->description;
        $sheq_action->priority = $this->priority;
        $sheq_action->due_date = $this->due_date;
        $sheq_action->status = 'open';
        $sheq_action->save();

        $this->dispatchBrowserEvent('hide-sheq_actionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Action Created Successfully!!"
        ]);
    }

    public function edit($id){
        $sheq_action = SheqAction::find($id);
        $this->sheq_action_id = $sheq_action->id;
        $this->department_id = $sheq_action->department_id;
        $this->employee_id = $sheq_action->employee_id;
        $this->source = $sheq_action->source;
        $this->reference = $sheq_action->reference;
        $this->title = $sheq_action->title;
        $this->description = $sheq_action->description;
        $this->priority = $sheq_action->priority;
        $this->due_date = $sheq_action->due_date ? Carbon::parse($sheq_action->due_date)->format('Y-m-d') : Null;
        $this->dispatchBrowserEvent('show-sheq_actionEditModal');
    }

    public function update(){
        $this->validate();

        $sheq_action = SheqAction::find($this->sheq_action_id);
        $sheq_action->department_id = $this->department_id ?: Null;
        $sheq_action->employee_id = $this->employee_id ?: Null;
        $sheq_action->source = $this->source ?: 'other';
        $sheq_action->reference = $this->reference;
        $sheq_action->title = $this->title;
        $sheq_action->description = $this->description;
        $sheq_action->priority = $this->priority;
        $sheq_action->due_date = $this->due_date;
        $sheq_action->update();

        $this->dispatchBrowserEvent('hide-sheq_actionEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Action Updated Successfully!!"
        ]);
    }

    public function start($id){
        $sheq_action = SheqAction::find($id);
        $sheq_action->status = 'in_progress';
        $sheq_action->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Action Marked In Progress!!"
        ]);
    }

    public function complete($id){
        $this->sheq_action_id = $id;
        $this->completed_date = Carbon::today()->format('Y-m-d');
        $this->dispatchBrowserEvent('show-sheq_actionCompleteModal');
    }

    public function saveComplete(){
        $sheq_action = SheqAction::find($this->sheq_action_id);
        $sheq_action->completed_date = $this->completed_date ?: Carbon::today()->format('Y-m-d');
        $sheq_action->completion_notes = $this->completion_notes;
        $sheq_action->status = 'completed';
        $sheq_action->update();

        $this->dispatchBrowserEvent('hide-sheq_actionCompleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Action Completed Successfully!!"
        ]);
    }

    public function verify($id){
        $this->sheq_action_id = $id;
        $this->dispatchBrowserEvent('show-sheq_actionVerifyModal');
    }

    public function saveVerify(){
        $sheq_action = SheqAction::find($this->sheq_action_id);
        $sheq_action->effectiveness = $this->effectiveness;
        $sheq_action->effectiveness_notes = $this->effectiveness_notes;
        $sheq_action->verified_by_id = Auth::user()->id;
        $sheq_action->verified_date = Carbon::today()->format('Y-m-d');
        $sheq_action->status = $this->effectiveness == 'not_effective' ? 'open' : 'verified';
        $sheq_action->update();

        $this->dispatchBrowserEvent('hide-sheq_actionVerifyModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Action Effectiveness Recorded Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_action_id = $id;
        $this->dispatchBrowserEvent('show-sheq_actionDeleteModal');
    }

    public function destroy(){
        $sheq_action = SheqAction::find($this->sheq_action_id);
        if ($sheq_action) {
            $sheq_action->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_actionDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Action Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqAction::query()->with(['department','employee','user']);

        if ($this->status_filter == 'overdue') {
            $query->whereNotIn('status', ['completed','verified'])
                  ->whereDate('due_date','<', Carbon::today());
        } elseif ($this->status_filter) {
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
                $q->where('title','like',$search)
                  ->orWhere('action_number','like',$search)
                  ->orWhere('reference','like',$search)
                  ->orWhere('description','like',$search);
            });
        }

        $sheq_actions = $query->orderBy('created_at','desc')->paginate(10);

        return view('livewire.sheq-actions.index',[
            'sheq_actions' => $sheq_actions
        ]);
    }
}
