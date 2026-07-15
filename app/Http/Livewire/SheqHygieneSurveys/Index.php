<?php

namespace App\Http\Livewire\SheqHygieneSurveys;

use App\Models\Department;
use App\Models\SheqHygieneSurvey;
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
    public $stressor_filter = '';
    public $department_filter = '';

    public $departments;

    public $sheq_hygiene_survey_id;
    public $department_id;
    public $stressor;
    public $area;
    public $survey_date;
    public $surveyor;
    public $result;
    public $limit_standard;
    public $exceeds_limit = 0;
    public $findings;
    public $next_survey_date;
    public $status = 'open';

    protected $rules = [
        'department_id' => 'required',
        'stressor' => 'required',
        'survey_date' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->stressor = "";
        $this->area = "";
        $this->survey_date = "";
        $this->surveyor = "";
        $this->result = "";
        $this->limit_standard = "";
        $this->exceeds_limit = 0;
        $this->findings = "";
        $this->next_survey_date = "";
        $this->status = "open";
    }

    public function surveyNumber(){
        $last_id = SheqHygieneSurvey::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'OHS'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $survey = new SheqHygieneSurvey;
        $survey->user_id = Auth::user()->id;
        $survey->survey_number = $this->surveyNumber();
        $this->fill_fields($survey);
        $survey->save();

        $this->dispatchBrowserEvent('hide-sheq_hygiene_surveyModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Hygiene Survey Created Successfully!!"
        ]);
    }

    private function fill_fields($survey){
        $survey->department_id = $this->department_id;
        $survey->stressor = $this->stressor;
        $survey->area = $this->area;
        $survey->survey_date = $this->survey_date;
        $survey->surveyor = $this->surveyor;
        $survey->result = $this->result;
        $survey->limit_standard = $this->limit_standard;
        $survey->exceeds_limit = $this->exceeds_limit ? 1 : 0;
        $survey->findings = $this->findings;
        $survey->next_survey_date = $this->next_survey_date ?: Null;
        $survey->status = $this->status;
    }

    public function edit($id){
        $survey = SheqHygieneSurvey::find($id);
        $this->sheq_hygiene_survey_id = $survey->id;
        $this->department_id = $survey->department_id;
        $this->stressor = $survey->stressor;
        $this->area = $survey->area;
        $this->survey_date = $survey->survey_date ? Carbon::parse($survey->survey_date)->format('Y-m-d') : Null;
        $this->surveyor = $survey->surveyor;
        $this->result = $survey->result;
        $this->limit_standard = $survey->limit_standard;
        $this->exceeds_limit = $survey->exceeds_limit;
        $this->findings = $survey->findings;
        $this->next_survey_date = $survey->next_survey_date ? Carbon::parse($survey->next_survey_date)->format('Y-m-d') : Null;
        $this->status = $survey->status;
        $this->dispatchBrowserEvent('show-sheq_hygiene_surveyEditModal');
    }

    public function update(){
        $this->validate();

        $survey = SheqHygieneSurvey::find($this->sheq_hygiene_survey_id);
        $this->fill_fields($survey);
        $survey->update();

        $this->dispatchBrowserEvent('hide-sheq_hygiene_surveyEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Hygiene Survey Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_hygiene_survey_id = $id;
        $this->dispatchBrowserEvent('show-sheq_hygiene_surveyDeleteModal');
    }

    public function destroy(){
        $survey = SheqHygieneSurvey::find($this->sheq_hygiene_survey_id);
        if ($survey) {
            $survey->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_hygiene_surveyDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Hygiene Survey Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqHygieneSurvey::query()->with(['department','actions']);

        if ($this->stressor_filter) {
            $query->where('stressor', $this->stressor_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('survey_number','like',$search)
                  ->orWhere('area','like',$search)
                  ->orWhere('findings','like',$search);
            });
        }

        $sheq_hygiene_surveys = $query->orderBy('survey_date','desc')->paginate(10);

        return view('livewire.sheq-hygiene-surveys.index',[
            'sheq_hygiene_surveys' => $sheq_hygiene_surveys
        ]);
    }
}
