<?php

namespace App\Http\Livewire\SheqEngagements;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqEngagement;
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

    public $departments;
    public $employees;

    public $sheq_engagement_id;
    public $type;
    public $leader_id;
    public $department_id;
    public $engagement_date;
    public $area;
    public $observations;
    public $positives;
    public $concerns;
    public $status = 'open';

    protected $rules = [
        'type' => 'required',
        'leader_id' => 'required',
        'department_id' => 'required',
        'engagement_date' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->type = "";
        $this->leader_id = "";
        $this->department_id = "";
        $this->engagement_date = "";
        $this->area = "";
        $this->observations = "";
        $this->positives = "";
        $this->concerns = "";
        $this->status = "open";
    }

    public function engagementNumber(){
        $last_id = SheqEngagement::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'ENG'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $engagement = new SheqEngagement;
        $engagement->user_id = Auth::user()->id;
        $engagement->engagement_number = $this->engagementNumber();
        $engagement->type = $this->type;
        $engagement->leader_id = $this->leader_id;
        $engagement->department_id = $this->department_id;
        $engagement->engagement_date = $this->engagement_date;
        $engagement->area = $this->area;
        $engagement->observations = $this->observations;
        $engagement->positives = $this->positives;
        $engagement->concerns = $this->concerns;
        $engagement->status = $this->status;
        $engagement->save();

        $this->dispatchBrowserEvent('hide-sheq_engagementModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Leadership Engagement Recorded Successfully!!"
        ]);
    }

    public function edit($id){
        $engagement = SheqEngagement::find($id);
        $this->sheq_engagement_id = $engagement->id;
        $this->type = $engagement->type;
        $this->leader_id = $engagement->leader_id;
        $this->department_id = $engagement->department_id;
        $this->engagement_date = $engagement->engagement_date ? Carbon::parse($engagement->engagement_date)->format('Y-m-d') : Null;
        $this->area = $engagement->area;
        $this->observations = $engagement->observations;
        $this->positives = $engagement->positives;
        $this->concerns = $engagement->concerns;
        $this->status = $engagement->status;
        $this->dispatchBrowserEvent('show-sheq_engagementEditModal');
    }

    public function update(){
        $this->validate();

        $engagement = SheqEngagement::find($this->sheq_engagement_id);
        $engagement->type = $this->type;
        $engagement->leader_id = $this->leader_id;
        $engagement->department_id = $this->department_id;
        $engagement->engagement_date = $this->engagement_date;
        $engagement->area = $this->area;
        $engagement->observations = $this->observations;
        $engagement->positives = $this->positives;
        $engagement->concerns = $this->concerns;
        $engagement->status = $this->status;
        $engagement->update();

        $this->dispatchBrowserEvent('hide-sheq_engagementEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Leadership Engagement Updated Successfully!!"
        ]);
    }

    public function close($id){
        $engagement = SheqEngagement::find($id);
        $engagement->status = 'closed';
        $engagement->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Leadership Engagement Closed Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_engagement_id = $id;
        $this->dispatchBrowserEvent('show-sheq_engagementDeleteModal');
    }

    public function destroy(){
        $engagement = SheqEngagement::find($this->sheq_engagement_id);
        if ($engagement) {
            $engagement->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_engagementDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Leadership Engagement Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqEngagement::query()->with(['department','leader']);

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('engagement_number','like',$search)
                  ->orWhere('area','like',$search)
                  ->orWhere('observations','like',$search);
            });
        }

        $sheq_engagements = $query->orderBy('engagement_date','desc')->paginate(10);

        return view('livewire.sheq-engagements.index',[
            'sheq_engagements' => $sheq_engagements
        ]);
    }
}
