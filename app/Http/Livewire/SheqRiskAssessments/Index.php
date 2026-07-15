<?php

namespace App\Http\Livewire\SheqRiskAssessments;

use App\Models\Department;
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
    public $type_filter = '';
    public $department_filter = '';

    public $departments;

    public $sheq_risk_assessment_id;
    public $department_id;
    public $type = 'baseline';
    public $activity;
    public $area;
    public $team;
    public $assessment_date;
    public $review_date;
    public $status = 'active';

    protected $rules = [
        'department_id' => 'required',
        'activity' => 'required',
        'assessment_date' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->type = "baseline";
        $this->activity = "";
        $this->area = "";
        $this->team = "";
        $this->assessment_date = "";
        $this->review_date = "";
        $this->status = "active";
    }

    public function assessmentNumber(){
        $last_id = SheqRiskAssessment::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'RA'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $assessment = new SheqRiskAssessment;
        $assessment->user_id = Auth::user()->id;
        $assessment->assessment_number = $this->assessmentNumber();
        $assessment->department_id = $this->department_id;
        $assessment->type = $this->type;
        $assessment->activity = $this->activity;
        $assessment->area = $this->area;
        $assessment->team = $this->team;
        $assessment->assessment_date = $this->assessment_date;
        $assessment->review_date = $this->review_date ?: Null;
        $assessment->status = $this->status;
        $assessment->save();

        $this->dispatchBrowserEvent('hide-sheq_risk_assessmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Risk Assessment Created Successfully!!"
        ]);
    }

    public function edit($id){
        $assessment = SheqRiskAssessment::find($id);
        $this->sheq_risk_assessment_id = $assessment->id;
        $this->department_id = $assessment->department_id;
        $this->type = $assessment->type;
        $this->activity = $assessment->activity;
        $this->area = $assessment->area;
        $this->team = $assessment->team;
        $this->assessment_date = $assessment->assessment_date ? Carbon::parse($assessment->assessment_date)->format('Y-m-d') : Null;
        $this->review_date = $assessment->review_date ? Carbon::parse($assessment->review_date)->format('Y-m-d') : Null;
        $this->status = $assessment->status;
        $this->dispatchBrowserEvent('show-sheq_risk_assessmentEditModal');
    }

    public function update(){
        $this->validate();

        $assessment = SheqRiskAssessment::find($this->sheq_risk_assessment_id);
        $assessment->department_id = $this->department_id;
        $assessment->type = $this->type;
        $assessment->activity = $this->activity;
        $assessment->area = $this->area;
        $assessment->team = $this->team;
        $assessment->assessment_date = $this->assessment_date;
        $assessment->review_date = $this->review_date ?: Null;
        $assessment->status = $this->status;
        $assessment->update();

        $this->dispatchBrowserEvent('hide-sheq_risk_assessmentEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Risk Assessment Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_risk_assessment_id = $id;
        $this->dispatchBrowserEvent('show-sheq_risk_assessmentDeleteModal');
    }

    public function destroy(){
        $assessment = SheqRiskAssessment::find($this->sheq_risk_assessment_id);
        if ($assessment) {
            $assessment->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_risk_assessmentDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Risk Assessment Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqRiskAssessment::query()->with(['department','risks']);

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('assessment_number','like',$search)
                  ->orWhere('activity','like',$search)
                  ->orWhere('area','like',$search);
            });
        }

        $sheq_risk_assessments = $query->orderBy('created_at','desc')->paginate(10);

        return view('livewire.sheq-risk-assessments.index',[
            'sheq_risk_assessments' => $sheq_risk_assessments
        ]);
    }
}
