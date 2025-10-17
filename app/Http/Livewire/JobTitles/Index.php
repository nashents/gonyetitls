<?php

namespace App\Http\Livewire\JobTitles;

use App\Models\Grade;
use Livewire\Component;
use App\Models\JobTitle;
use App\Models\Department;
use Livewire\WithPagination;
use App\Models\Qualification;
use Illuminate\Support\Facades\Auth;
use App\Models\JobTitleQualification;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    public $departments;
    public $department_id;
    public $title;
    public $description;
    public $qualifications;
    public $qualification_id = [];
    public $grades;
    public $grade_id; 
    public $mandatory = [];
    public $min_level = [];
    public $weight = [];
    public $min_score = []; 

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $job_titles;
    public $job_title_id;
    public $user_id;

    public $inputs = [];
    public $i = 1;
    public $n = 1;
    
    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    
    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->qualifications = Qualification::orderBy('name','asc')->get();
        $this->grades = Grade::orderBy('grade_name','asc')->orderBy('grade_code','asc')->get();
         
    }
    private function resetInputFields(){
        $this->title = '';
    }
   
    private function resetQualificationInputFields(){
        $this->mandatory = [];
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[
        'department_id.required' => "Department field is required",
        'grade_id.required' => "Department field is required"
    ];
    protected $rules = [
        'department_id' => 'nullable',
        'grade_id' => 'nullable',
        'title' => 'required|unique:job_titles,title,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        // try{
        $job_title = new JobTitle;
        $job_title->user_id = Auth::user()->id;
        $job_title->title = $this->title;
        $job_title->department_id = $this->department_id;
        $job_title->description = $this->description;
        $job_title->save();
        $job_title->grades()->attach($this->grade_id);

        $this->dispatchBrowserEvent('hide-job_titleModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Title Created Successfully!!"
        ]);
        return redirect(request()->header('Referer'));

   
    }

    public function edit($id){
    $job_title = JobTitle::find($id);
    $this->user_id = $job_title->user_id;
    $this->title = $job_title->title;
    $this->department_id = $job_title->department_id;
    $this->job_title_id = $job_title->id;
    $this->dispatchBrowserEvent('show-job_titleEditModal');

    }


    public function update()
    {
        if ($this->job_title_id) {
          
            $job_title = JobTitle::find($this->job_title_id);
            $job_title->title = $this->title;
            $job_title->department_id = $this->department_id;
            $job_title->description = $this->description;
            $job_title->update();
            $job_title->grades()->detach();
            $job_title->grades()->attach($this->grade_id);

            $this->dispatchBrowserEvent('hide-job_titleEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Job Title Updated Successfully!!"
            ]);
      
        }
    }

    public function showQualification($id){
        $this->job_title_id = $id;
        $this->dispatchBrowserEvent('show-qualificationModal');
    }

    public function addQualification(){

        if (!isset($this->qualification_id)) {
            return;
        }
        foreach ($this->qualification_id as $key => $id) {

            $job_title_qualification = new JobTitleQualification();
            $job_title_qualification->job_title_id = $this->job_title_id;
            if (isset($this->qualification_id[$key])) {
                    $job_title_qualification->qualification_id = $this->qualification_id[$key];
            }
            if (isset($this->mandatory[$key])) {
                    $job_title_qualification->mandatory = $this->mandatory[$key];
            }
            if (isset($this->min_level[$key])) {
                    $job_title_qualification->min_level = $this->min_level[$key];
            }
            if (isset($this->weight[$key])) {
                    $job_title_qualification->weight = $this->weight[$key];
            }
            if (isset($this->min_score[$key])) {
                    $job_title_qualification->min_score = $this->min_score[$key] ;
            }
            
            $job_title_qualification->save();
        }
       

        $this->resetQualificationInputFields();
        $this->dispatchBrowserEvent('hide-qualificationModal');
    }



    public function render()
    {
        $term = trim((string) $this->search);

        $query = JobTitle::query()
            ->with(['department','grades'])
            ->when(filled($term), function ($q) use ($term) {
                $q->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                    ->orWhereHas('grades', function ($q) use ($term) {
                        $q->where('grade_code', 'like', "%{$term}%")
                            ->orWhere('grade_name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('department', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%");
                    });
                });
            })
            ->select('job_titles.*')   // avoid ambiguous selects
            ->distinct()
            ->orderBy('title', 'asc');

        return view('livewire.job-titles.index', [
            'job_titles' => $query->paginate(10),
        ]);
    }
}
