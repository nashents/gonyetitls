<?php

namespace App\Http\Livewire\Grades;

use App\Models\Grade;
use Livewire\Component;
use App\Models\Currency;
use App\Models\JobTitle;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $grades;
    public $currencies;
    public $currency_id;
    public $grade_id;
    public $job_titles;
    public $job_title_id;
    public $grade_code;
    public $grade_name;
    public $grade_level;
    public $min_salary;
    public $max_salary;
    public $job_category;
    public $job_band;
    public $next_grade_id;
    public $promotion_criteria;
    public $max_years_in_grade;
    public $leave_days = 30;
    public $bonus_eligibility = False;
    public $overtime_eligibility = True;
    public $benefits_package;
    public $effective_date;
    public $status = True;
    public $user_id;

    public function mount(){
        $this->job_titles = JobTitle::orderBy('title','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
      
    }

    
    private function resetInputFields(){
        $this->grade_code = '';
        $this->grade_name = '';
        $this->grade_level = '';
        $this->currency_id = '';
        $this->job_title_id = [];
        $this->min_salary = '';
        $this->max_salary = '';
        $this->job_category = '';
        $this->job_band = '';
        $this->effective_date = '';
        $this->status = '';
        $this->benefits_package = '';
        $this->overtime_eligibility = '';
        $this->leave_days = '';
        $this->bonus_eligibility = '';
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'grade_name' => 'required|unique:grades,grade_name,NULL,id,deleted_at,NULL',
        'grade_code' => 'required|unique:grades,grade_code,NULL,id,deleted_at,NULL',
    ];

    public function store(){
        $grade = new Grade;
        $grade->grade_name = $this->grade_name;
        $grade->grade_code = $this->grade_code;
        $grade->grade_level = $this->grade_level ?? 1;
        $grade->min_salary = $this->min_salary;
        $grade->currency_id = $this->currency_id;
        $grade->max_salary = $this->max_salary;
        $grade->job_category = $this->job_category;
        $grade->job_band = $this->job_band;
        $grade->next_grade_id = $this->next_grade_id;
        $grade->promotion_criteria = $this->promotion_criteria;
        $grade->max_years_in_grade = $this->max_years_in_grade;
        $grade->leave_days = $this->leave_days;
        $grade->overtime_eligibility = $this->overtime_eligibility;
        $grade->bonus_eligibility = $this->bonus_eligibility;
        $grade->benefits_package = $this->benefits_package;
        $grade->effective_date = $this->effective_date;
        $grade->status = $this->status; 
        $grade->save();
        $grade->job_titles()->sync($this->job_title_id);

        $this->dispatchBrowserEvent('hide-gradeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Grade Added Successfully!!"
        ]);
    }

    public function edit($id){
        $grade = Grade::find($id);
        $this->user_id = $grade->user_id;
        $this->grade_name = $grade->grade_name;
        $this->grade_code = $grade->grade_code;
        $this->grade_level = $grade->grade_level;
        $this->min_salary = $grade->min_salary;
        $this->max_salary = $grade->max_salary;
        $this->currency_id = $grade->currency_id;
        $this->bonus_eligibility = $grade->bonus_eligibility;
        $this->overtime_eligibility = $grade->overtime_eligibility;
        $job_titles = $grade->job_titles;
        if($job_titles){
            foreach($job_titles as $job_title){
                $this->job_title_id[] = $job_title->id;
            }
        }
        $this->effective_date = $grade->effective_date;
        $this->status = $grade->status;
        $this->benefits_package = $grade->benefits_package;
        $this->leave_days = $grade->leave_days;
        $this->max_years_in_grade = $grade->max_years_in_grade;
        $this->promotion_criteria = $grade->promotion_criteria;
        $this->job_category = $grade->job_category;
        $this->job_band = $grade->job_band;
        $this->grade_id = $grade->id;
        $this->dispatchBrowserEvent('show-gradeEditModal');
    }

    public function update(){
        $grade = Grade::find($this->grade_id);
        $grade->grade_name = $this->grade_name;
        $grade->grade_code = $this->grade_code;
        $grade->grade_level = $this->grade_level;
        $grade->min_salary = $this->min_salary;
        $grade->currency_id = $this->currency_id;
        $grade->max_salary = $this->max_salary;
        $grade->job_category = $this->job_category;
        $grade->job_band = $this->job_band;
        $grade->next_grade_id = $this->next_grade_id;
        $grade->promotion_criteria = $this->promotion_criteria;
        $grade->max_years_in_grade = $this->max_years_in_grade;
        $grade->leave_days = $this->leave_days;
        $grade->overtime_eligibility = $this->overtime_eligibility;
        $grade->bonus_eligibility = $this->bonus_eligibility;
        $grade->benefits_package = $this->benefits_package;
        $grade->effective_date = $this->effective_date;
        $grade->status = $this->status; 
        $grade->update();
        $grade->job_titles()->detach();
        $grade->job_titles()->sync($this->job_title_id);

        $this->dispatchBrowserEvent('hide-gradeEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Grade Update Successfully!!"
        ]);
    }

    public function render()
    {
        if(filled($this->search)){
            return view('livewire.grades.index',[
            'grades' => Grade::where('grade_name','like','%'.$this->search.'%')
                            ->orWhere('grade_code','like','%'.$this->search.'%')
                            ->orWhere('grade_level','like','%'.$this->search.'%')
                            ->orWhere('job_category','like','%'.$this->search.'%')
                            ->orWhere('job_band','like','%'.$this->search.'%')
                            ->orderBy('grade_code','asc')
                            ->paginate(10)
            ]);
          
        }else{
             return view('livewire.grades.index',[
            'grades' => Grade::orderBy('grade_code','asc')->paginate(10)
            ]);
        }
       
    }
}
