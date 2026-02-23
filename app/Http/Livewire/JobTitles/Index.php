<?php

namespace App\Http\Livewire\JobTitles;

use App\Models\Department;
use App\Models\Grade;
use App\Models\JobTitle;
use App\Models\JobTitleQualification;
use App\Models\Qualification;
use App\Models\Rank;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;


class Index extends Component
{

    public $ranks;
    public $rank_id;
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
    protected $job_titles;
    public $job_title_id;
    public $user_id;
    public $qualification;
    public $job_title;
    public $job_title_qualifications;
    public $job_title_qualification;
    public $job_title_qualification_id;

    public $duties;
    public $instructions;
    public $requirements;

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
        $this->ranks = Rank::orderBy('name','asc')->get();
        $this->job_title = Null;
        $this->qualification = Null;
        $this->qualifications = collect();
        $this->job_title_qualifications = collect();
        $this->grades = Grade::orderBy('grade_name','asc')->orderBy('grade_code','asc')->get();
         
    }
    private function resetInputFields(){
        $this->title = '';
        $this->description = '';
        $this->duties = '';
        $this->instructions = '';
        $this->requirements = '';
        $this->department_id = Null;
        $this->grade_id = Null;
        $this->rank_id = Null;
       
    }
   
    private function resetQualificationInputFields(){
        $this->mandatory = [];
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[
      
    ];
    protected $rules = [
       
        'title' => 'required|unique:job_titles,title,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        // try{
        $job_title = new JobTitle;
        $job_title->user_id = Auth::user()->id;
        $job_title->title = $this->title;
        $job_title->department_id = $this->department_id;
        $job_title->description = $this->description;
        $job_title->rank_id = $this->rank_id;
        $job_title->duties = $this->duties;
        $job_title->instructions = $this->instructions;
        $job_title->requirements = $this->requirements;
        $job_title->save();
        $job_title->grades()->attach($this->grade_id);

        $this->dispatchBrowserEvent('hide-job_titleModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Title Created Successfully!!"
        ]);
   
    }

    public function edit($id){
    $job_title = JobTitle::find($id);
    $this->user_id = $job_title->user_id;
    $this->title = $job_title->title;
    $this->department_id = $job_title->department_id;
    $this->rank_id = $job_title->rank_id;
    $this->requirements = $job_title->requirements;
    $this->duties = $job_title->duties;
    $this->instructions = $job_title->instructions;
    $this->description = $job_title->description;
    $this->job_title_id = $job_title->id;
    $grades = $job_title->grades;
    if ($grades) {
        foreach ($grades as $grade) {
            $this->grade_id[] = $grade->id;
        }
    }
    $this->dispatchBrowserEvent('show-job_titleEditModal');

    }

    


    public function update()
    {
        if ($this->job_title_id) {
          
            $job_title = JobTitle::find($this->job_title_id);
            $job_title->title = $this->title;
            $job_title->department_id = $this->department_id;
            $job_title->description = $this->description;
            $job_title->rank_id = $this->rank_id;
            $job_title->duties = $this->duties;
            $job_title->instructions = $this->instructions;
            $job_title->requirements = $this->requirements;
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

     public function removeShow($id){
        $this->job_title_qualification_id = $id;
        $job_title_qualification = JobTitleQualification::find($id);
        $this->qualification = $job_title_qualification->qualification;
        $this->job_title = $job_title_qualification->qualification;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeQualification(){

        $job_title_qualification = JobTitleQualification::find($this->job_title_qualification_id);
        $job_title_qualification->delete();

         $this->job_title_qualifications = $this->job_title->job_title_qualifications;

        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Qualification Removed Successfully!!"
        ]);

    }

    public function showQualification($id){
        $this->job_title_id = $id;
        
         // Get qualification IDs already linked to this job title
        $existingQualificationIds = JobTitleQualification::where('job_title_id', $id)
            ->pluck('qualification_id')
            ->toArray();

        // Fetch only qualifications NOT already assigned
        $this->qualifications = Qualification::whereNotIn('id', $existingQualificationIds)
            ->orderBy('name', 'asc')
            ->get();
            $this->dispatchBrowserEvent('show-qualificationModal');
    }

    public function showEditQualification($id){

        $this->job_title_id = $id;

        $this->qualifications = Qualification::orderBy('name','asc')->get();

        $job_title = JobTitle::with('job_title_qualifications')->find($id);

        $this->job_title_qualifications = $job_title->job_title_qualifications;

        if($this->job_title_qualifications->count() > 0) {

           
            foreach ($this->job_title_qualifications as $key => $job_title_qualification) {

                $this->qualification_id[] = $job_title_qualification->qualification_id;
                $this->mandatory[] = $job_title_qualification->mandatory;
                $this->min_level[] = $job_title_qualification->min_level;
                $this->weight[] = $job_title_qualification->weight;
                $this->min_score[] = $job_title_qualification->min_score;

            }

        }

        $this->dispatchBrowserEvent('show-qualificationEditModal');
    }

    public function addQualification(){

        if (!isset($this->qualification_id)) {
            return;
        }
        foreach ($this->qualification_id as $key => $id) {

            $job_title_qualification = JobTitleQualification::withTrashed()->firstOrNew([
            'job_title_id'     => $this->job_title_id,
            'qualification_id' =>  $this->qualification_id[$key] ?? null,
            ]);

            if (method_exists($job_title_qualification, 'trashed') && $job_title_qualification->trashed()) {
                $job_title_qualification->restore();
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

    public function updateQualification()
    {
        if (empty($this->qualification_id)) {
            return;
        }

        foreach ($this->qualification_id as $key => $qualificationId) {
            // Use firstOrNew to find existing qualification or create a new one
            $job_title_qualification = JobTitleQualification::withTrashed()->firstOrNew([
                'job_title_id'      => $this->job_title_id,
                'qualification_id'  => $qualificationId,
            ]);

             if (method_exists($job_title_qualification, 'trashed') && $job_title_qualification->trashed()) {
                $job_title_qualification->restore();
            }

            // Update fields dynamically if present
            $job_title_qualification->mandatory   = $this->mandatory[$key] ?? null;
            $job_title_qualification->min_level   = $this->min_level[$key] ?? null;
            $job_title_qualification->weight      = $this->weight[$key] ?? null;
            $job_title_qualification->min_score   = $this->min_score[$key] ?? null;

            // Save (either update existing or insert new)
            $job_title_qualification->save();
        }

        // Reset input fields and close modal
        $this->resetQualificationInputFields();
        $this->dispatchBrowserEvent('hide-qualificationModal');
    }


       public function refresh($category){

        if($category == "qualifications"){
            $this->qualifications = Qualification::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Qualifications Refreshed Successfully!!."
            ]);
        }elseif($category == "grades"){
              $this->grades = Grade::orderBy('grade_code','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Grades Refreshed Successfully!!."
            ]);
        }
       
      
    }


    public function render()
    {
        $term = trim((string) $this->search);

        $query = JobTitle::query()
            ->with(['department','grades'])
            ->when(filled($term), function ($q) use ($term) {
                $q->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('duties', 'like', "%{$term}%")
                    ->orWhere('requirements', 'like', "%{$term}%")
                    ->orWhere('instructions', 'like', "%{$term}%")
                    ->orWhereHas('grades', function ($q) use ($term) {
                        $q->where('grade_code', 'like', "%{$term}%")
                            ->orWhere('grade_name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('job_title_qualifications', function ($q) use ($term) {
                        $q->orWhereHas('qualification', function ($q) use ($term) {
                            $q->where('name', 'like', "%{$term}%");
                        });
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
