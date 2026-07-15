<?php

namespace App\Http\Livewire\SheqStakeholders;

use App\Models\Department;
use App\Models\SheqStakeholder;
use App\Models\SheqStakeholderEngagement;
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

    public $sheq_stakeholder_id;
    public $department_id;
    public $name;
    public $type = 'external';
    public $category;
    public $needs_expectations;
    public $becomes_obligation = 0;
    public $engagement_method;
    public $engagement_frequency;
    public $status = 'active';

    public $engagement_stakeholder_id;
    public $engagement_date;
    public $method;
    public $summary;

    protected $rules = [
        'name' => 'required',
        'type' => 'required',
        'needs_expectations' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->name = "";
        $this->type = "external";
        $this->category = "";
        $this->needs_expectations = "";
        $this->becomes_obligation = 0;
        $this->engagement_method = "";
        $this->engagement_frequency = "";
        $this->status = "active";
        $this->engagement_date = "";
        $this->method = "";
        $this->summary = "";
    }

    public function store(){
        $this->validate();

        $stakeholder = new SheqStakeholder;
        $stakeholder->user_id = Auth::user()->id;
        $stakeholder->department_id = $this->department_id ?: Null;
        $stakeholder->name = $this->name;
        $stakeholder->type = $this->type;
        $stakeholder->category = $this->category;
        $stakeholder->needs_expectations = $this->needs_expectations;
        $stakeholder->becomes_obligation = $this->becomes_obligation ? 1 : 0;
        $stakeholder->engagement_method = $this->engagement_method;
        $stakeholder->engagement_frequency = $this->engagement_frequency;
        $stakeholder->status = $this->status;
        $stakeholder->save();

        $this->dispatchBrowserEvent('hide-sheq_stakeholderModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Interested Party Created Successfully!!"
        ]);
    }

    public function edit($id){
        $stakeholder = SheqStakeholder::find($id);
        $this->sheq_stakeholder_id = $stakeholder->id;
        $this->department_id = $stakeholder->department_id;
        $this->name = $stakeholder->name;
        $this->type = $stakeholder->type;
        $this->category = $stakeholder->category;
        $this->needs_expectations = $stakeholder->needs_expectations;
        $this->becomes_obligation = $stakeholder->becomes_obligation;
        $this->engagement_method = $stakeholder->engagement_method;
        $this->engagement_frequency = $stakeholder->engagement_frequency;
        $this->status = $stakeholder->status;
        $this->dispatchBrowserEvent('show-sheq_stakeholderEditModal');
    }

    public function update(){
        $this->validate();

        $stakeholder = SheqStakeholder::find($this->sheq_stakeholder_id);
        $stakeholder->department_id = $this->department_id ?: Null;
        $stakeholder->name = $this->name;
        $stakeholder->type = $this->type;
        $stakeholder->category = $this->category;
        $stakeholder->needs_expectations = $this->needs_expectations;
        $stakeholder->becomes_obligation = $this->becomes_obligation ? 1 : 0;
        $stakeholder->engagement_method = $this->engagement_method;
        $stakeholder->engagement_frequency = $this->engagement_frequency;
        $stakeholder->status = $this->status;
        $stakeholder->update();

        $this->dispatchBrowserEvent('hide-sheq_stakeholderEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Interested Party Updated Successfully!!"
        ]);
    }

    public function engage($id){
        $this->engagement_stakeholder_id = $id;
        $this->engagement_date = Carbon::today()->format('Y-m-d');
        $this->dispatchBrowserEvent('show-sheq_stakeholderEngageModal');
    }

    public function storeEngagement(){
        $this->validate([
            'engagement_date' => 'required',
            'summary' => 'required',
        ]);

        $engagement = new SheqStakeholderEngagement;
        $engagement->sheq_stakeholder_id = $this->engagement_stakeholder_id;
        $engagement->user_id = Auth::user()->id;
        $engagement->engagement_date = $this->engagement_date;
        $engagement->method = $this->method;
        $engagement->summary = $this->summary;
        $engagement->save();

        $this->dispatchBrowserEvent('hide-sheq_stakeholderEngageModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Engagement Recorded Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_stakeholder_id = $id;
        $this->dispatchBrowserEvent('show-sheq_stakeholderDeleteModal');
    }

    public function destroy(){
        $stakeholder = SheqStakeholder::find($this->sheq_stakeholder_id);
        if ($stakeholder) {
            $stakeholder->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_stakeholderDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Interested Party Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqStakeholder::query()->with(['department','engagements']);

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('name','like',$search)
                  ->orWhere('needs_expectations','like',$search);
            });
        }

        $sheq_stakeholders = $query->orderBy('name','asc')->paginate(10);

        return view('livewire.sheq-stakeholders.index',[
            'sheq_stakeholders' => $sheq_stakeholders
        ]);
    }
}
