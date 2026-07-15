<?php

namespace App\Http\Livewire\SheqObjectives;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqObjective;
use App\Models\SheqObjectiveUpdate;
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
    public $year_filter = '';
    public $department_filter = '';
    public $status_filter = '';

    public $departments;
    public $employees;

    public $sheq_objective_id;
    public $year;
    public $department_id;
    public $employee_id;
    public $category;
    public $objective;
    public $kpi;
    public $baseline;
    public $target;
    public $programme;
    public $due_date;
    public $status = 'open';

    public $update_objective_id;
    public $update_date;
    public $update_progress;
    public $update_comment;

    protected $rules = [
        'year' => 'required',
        'department_id' => 'required',
        'objective' => 'required',
        'target' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->year = Carbon::now()->format('Y');
    }

    private function resetInputFields(){
        $this->year = Carbon::now()->format('Y');
        $this->department_id = "";
        $this->employee_id = "";
        $this->category = "";
        $this->objective = "";
        $this->kpi = "";
        $this->baseline = "";
        $this->target = "";
        $this->programme = "";
        $this->due_date = "";
        $this->status = "open";
        $this->update_date = "";
        $this->update_progress = "";
        $this->update_comment = "";
    }

    public function store(){
        $this->validate();

        $objective = new SheqObjective;
        $objective->user_id = Auth::user()->id;
        $objective->year = $this->year;
        $objective->department_id = $this->department_id;
        $objective->employee_id = $this->employee_id ?: Null;
        $objective->category = $this->category;
        $objective->objective = $this->objective;
        $objective->kpi = $this->kpi;
        $objective->baseline = $this->baseline;
        $objective->target = $this->target;
        $objective->programme = $this->programme;
        $objective->due_date = $this->due_date ?: Null;
        $objective->progress = 0;
        $objective->status = $this->status;
        $objective->save();

        $this->dispatchBrowserEvent('hide-sheq_objectiveModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Objective Created Successfully!!"
        ]);
    }

    public function edit($id){
        $objective = SheqObjective::find($id);
        $this->sheq_objective_id = $objective->id;
        $this->year = $objective->year;
        $this->department_id = $objective->department_id;
        $this->employee_id = $objective->employee_id;
        $this->category = $objective->category;
        $this->objective = $objective->objective;
        $this->kpi = $objective->kpi;
        $this->baseline = $objective->baseline;
        $this->target = $objective->target;
        $this->programme = $objective->programme;
        $this->due_date = $objective->due_date ? Carbon::parse($objective->due_date)->format('Y-m-d') : Null;
        $this->status = $objective->status;
        $this->dispatchBrowserEvent('show-sheq_objectiveEditModal');
    }

    public function update(){
        $this->validate();

        $objective = SheqObjective::find($this->sheq_objective_id);
        $objective->year = $this->year;
        $objective->department_id = $this->department_id;
        $objective->employee_id = $this->employee_id ?: Null;
        $objective->category = $this->category;
        $objective->objective = $this->objective;
        $objective->kpi = $this->kpi;
        $objective->baseline = $this->baseline;
        $objective->target = $this->target;
        $objective->programme = $this->programme;
        $objective->due_date = $this->due_date ?: Null;
        $objective->status = $this->status;
        $objective->update();

        $this->dispatchBrowserEvent('hide-sheq_objectiveEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Objective Updated Successfully!!"
        ]);
    }

    public function progress($id){
        $objective = SheqObjective::find($id);
        $this->update_objective_id = $id;
        $this->update_date = Carbon::today()->format('Y-m-d');
        $this->update_progress = $objective->progress;
        $this->dispatchBrowserEvent('show-sheq_objectiveProgressModal');
    }

    public function storeProgress(){
        $this->validate([
            'update_date' => 'required',
            'update_progress' => 'required|integer|min:0|max:100',
        ]);

        $update = new SheqObjectiveUpdate;
        $update->sheq_objective_id = $this->update_objective_id;
        $update->user_id = Auth::user()->id;
        $update->update_date = $this->update_date;
        $update->progress = $this->update_progress;
        $update->comment = $this->update_comment;
        $update->save();

        $objective = SheqObjective::find($this->update_objective_id);
        $objective->progress = $this->update_progress;
        if ($this->update_progress == 100) {
            $objective->status = 'achieved';
        }
        $objective->update();

        $this->dispatchBrowserEvent('hide-sheq_objectiveProgressModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Progress Recorded Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_objective_id = $id;
        $this->dispatchBrowserEvent('show-sheq_objectiveDeleteModal');
    }

    public function destroy(){
        $objective = SheqObjective::find($this->sheq_objective_id);
        if ($objective) {
            $objective->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_objectiveDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Objective Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqObjective::query()->with(['department','employee','updates']);

        if ($this->year_filter) {
            $query->where('year', $this->year_filter);
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
                $q->where('objective','like',$search)
                  ->orWhere('kpi','like',$search);
            });
        }

        $sheq_objectives = $query->orderBy('year','desc')->orderBy('created_at','desc')->paginate(10);
        $years = SheqObjective::select('year')->distinct()->orderBy('year','desc')->pluck('year');

        return view('livewire.sheq-objectives.index',[
            'sheq_objectives' => $sheq_objectives,
            'years' => $years,
        ]);
    }
}
