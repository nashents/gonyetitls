<?php

namespace App\Http\Livewire\SheqDrills;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqDrill;
use App\Models\SheqEmergency;
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
    public $status_filter = '';
    public $department_filter = '';

    public $departments;
    public $employees;
    public $emergencies;

    public $sheq_drill_id;
    public $sheq_emergency_id;
    public $department_id;
    public $coordinator_id;
    public $planned_date;
    public $conducted_date;
    public $participants_count;
    public $response_time;
    public $evaluation;
    public $findings;
    public $findings_communicated = 0;
    public $status = 'planned';

    protected $rules = [
        'sheq_emergency_id' => 'required',
        'department_id' => 'required',
        'planned_date' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->emergencies = SheqEmergency::orderBy('scenario','asc')->get();
    }

    private function resetInputFields(){
        $this->sheq_emergency_id = "";
        $this->department_id = "";
        $this->coordinator_id = "";
        $this->planned_date = "";
        $this->conducted_date = "";
        $this->participants_count = "";
        $this->response_time = "";
        $this->evaluation = "";
        $this->findings = "";
        $this->findings_communicated = 0;
        $this->status = "planned";
    }

    public function drillNumber(){
        $last_id = SheqDrill::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'DRL'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $drill = new SheqDrill;
        $drill->user_id = Auth::user()->id;
        $drill->drill_number = $this->drillNumber();
        $this->fill_fields($drill);
        $drill->save();

        $this->dispatchBrowserEvent('hide-sheq_drillModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Drill Created Successfully!!"
        ]);
    }

    private function fill_fields($drill){
        $drill->sheq_emergency_id = $this->sheq_emergency_id;
        $drill->department_id = $this->department_id;
        $drill->coordinator_id = $this->coordinator_id ?: Null;
        $drill->planned_date = $this->planned_date;
        $drill->conducted_date = $this->conducted_date ?: Null;
        $drill->participants_count = $this->participants_count ?: Null;
        $drill->response_time = $this->response_time;
        $drill->evaluation = $this->evaluation;
        $drill->findings = $this->findings;
        $drill->findings_communicated = $this->findings_communicated ? 1 : 0;
        $drill->status = $this->status;
    }

    public function edit($id){
        $drill = SheqDrill::find($id);
        $this->sheq_drill_id = $drill->id;
        $this->sheq_emergency_id = $drill->sheq_emergency_id;
        $this->department_id = $drill->department_id;
        $this->coordinator_id = $drill->coordinator_id;
        $this->planned_date = $drill->planned_date ? Carbon::parse($drill->planned_date)->format('Y-m-d') : Null;
        $this->conducted_date = $drill->conducted_date ? Carbon::parse($drill->conducted_date)->format('Y-m-d') : Null;
        $this->participants_count = $drill->participants_count;
        $this->response_time = $drill->response_time;
        $this->evaluation = $drill->evaluation;
        $this->findings = $drill->findings;
        $this->findings_communicated = $drill->findings_communicated;
        $this->status = $drill->status;
        $this->dispatchBrowserEvent('show-sheq_drillEditModal');
    }

    public function update(){
        $this->validate();

        $drill = SheqDrill::find($this->sheq_drill_id);
        $this->fill_fields($drill);
        $drill->update();

        $this->dispatchBrowserEvent('hide-sheq_drillEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Drill Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_drill_id = $id;
        $this->dispatchBrowserEvent('show-sheq_drillDeleteModal');
    }

    public function destroy(){
        $drill = SheqDrill::find($this->sheq_drill_id);
        if ($drill) {
            $drill->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_drillDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Drill Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqDrill::query()->with(['department','emergency','coordinator','actions']);

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('drill_number','like',$search)
                  ->orWhere('findings','like',$search);
            });
        }

        $sheq_drills = $query->orderBy('planned_date','desc')->paginate(10);

        return view('livewire.sheq-drills.index',[
            'sheq_drills' => $sheq_drills
        ]);
    }
}
