<?php

namespace App\Http\Livewire\JobPostings;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Grade;
use App\Models\JobPosting;
use App\Models\JobTitle;
use App\Models\Rank;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{


    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    protected $job_postings;
    public $job_posting_id;
    public $job_titles;
    public $job_title_id;
    public $branches;
    public $branch_id;
    public $departments;
    public $department_id;
    public $grade_id;
    public $ranks;
    public $rank_id;
    public $description;
    public $number_of_candidates;
    public $duties;
    public $instructions;
    public $requirements;
    public $start_date;
    public $due_date;
    public $contract_type = "Full Time";
    public $duration;
    public $title;
    public $grades;


    public function mount(){
        $this->resetPage();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->ranks = Rank::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->grades  = Grade::orderBy('grade_code','asc')->get();
        $this->job_titles = JobTitle::orderBy('title','asc')->get();
    }

     public function updatingSearch()
    {
        $this->resetPage();
    }

   


    public function updatedJobTitleId($id){
        if (is_null($id)) {
            return;
        }
        $job_title = JobTitle::find($id);
        $this->department_id = $job_title->department_id;
        $this->grade_id = $job_title->grades->first()?->id;
        $this->rank_id = $job_title->rank_id;
        $this->duties = $job_title->duties;
        $this->description = $job_title->description;
        $this->requirements = $job_title->requirements;
        $this->instructions = $job_title->instructions;
    }

    public function job_postingNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $job_posting = JobPosting::orderBy('id', 'desc')->first();

        if (!$job_posting) {
            $job_posting_number =  $initials .'J'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $job_posting->id + 1;
            $job_posting_number =  $initials .'J'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $job_posting_number;


    }

     public function refresh($category){

        if($category == "job_titles"){
            $this->job_titles = JobTitle::orderBy('title','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Job Titles Refreshed Successfully!!."
            ]);
        }
        if($category == "grades"){
            $this->grades  = Grade::orderBy('grade_code','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Grades Refreshed Successfully!!."
            ]);
        }
       
      
    }


   
     private function resetInputFields(){
        $this->due_date = '';
        $this->start_date = '';
        $this->contract_type = '';
        $this->duration = '';
        $this->description = '';
        $this->duties = '';
        $this->instructions = '';
        $this->requirements = '';
        $this->job_title_id = Null;
        $this->department_id = Null;
        $this->grade_id = Null;
        $this->rank_id = Null;
       
    }

    public function store(){

        $job_posting = new JobPosting;
        $job_posting->user_id = Auth::user()->id;
        $job_posting->job_posting_number = $this->job_postingNumber();
        $job_posting->requirements = $this->requirements;
        $job_posting->duties = $this->duties;
        $job_posting->instructions = $this->instructions;
        $job_posting->contract_type = $this->contract_type;
        if ($this->contract_type == "Full Time") {
            $job_posting->duration = Null;
        }elseif($this->contract_type == "Fixed Term"){
            $job_posting->duration = $this->duration;
        }
        $job_posting->number_of_candidates = $this->number_of_candidates;
        $job_posting->description = $this->description;
        $job_posting->start_date = $this->start_date;
        $job_posting->job_title_id = $this->job_title_id;
        $job_posting->department_id = $this->department_id;
        $job_posting->grade_id = $this->grade_id;
        $job_posting->rank_id = $this->rank_id;
        $job_posting->due_date = $this->due_date;
        $job_posting->save();
       

      
        $this->dispatchBrowserEvent('hide-job_postingModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Posting Created Successfully!!"
        ]);
       
        
    }

    public function edit($id){
        $job_posting = JobPosting::find($id);
        $this->job_title_id = $job_posting->id;
        $this->description = $job_posting->description;
        $this->department_id = $job_posting->department_id;
        $this->rank_id = $job_posting->rank_id;
        $this->grade_id = $job_posting->grade_id;
        $this->instructions = $job_posting->instructions;
        $this->duties = $job_posting->duties;
        $this->requirements = $job_posting->requirements;
        $this->due_date = $job_posting->due_date;
        $this->start_date = $job_posting->start_date;
        $this->contract_type = $job_posting->contract_type;
        $this->duration = $job_posting->duration;
        $this->number_of_candidates = $job_posting->number_of_candidates;
        $this->job_posting_id = $job_posting->id;
         $this->dispatchBrowserEvent('show-job_postingEditModal');

    }
   
    public function update(){

        $job_posting = JobPosting::find($this->job_posting_id);
        $job_posting->requirements = $this->requirements;
        $job_posting->duties = $this->duties;
        $job_posting->instructions = $this->instructions;
        $job_posting->contract_type = $this->contract_type;
        if ($this->contract_type == "Full Time") {
            $job_posting->duration = Null;
        }elseif($this->contract_type == "Fixed Term"){
            $job_posting->duration = $this->duration;
        }
        $job_posting->number_of_candidates = $this->number_of_candidates;
        $job_posting->description = $this->description;
        $job_posting->start_date = $this->start_date;
        $job_posting->job_title_id = $this->job_title_id;
        $job_posting->department_id = $this->department_id;
        $job_posting->grade_id = $this->grade_id;
        $job_posting->rank_id = $this->rank_id;
        $job_posting->due_date = $this->due_date;
        $job_posting->update();

         $this->dispatchBrowserEvent('hide-job_postingEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Posting Updated Successfully!!"
        ]);
       

      
       
        
    }

    public function delete($id){
        $this->job_posting_id = $id;
        $this->dispatchBrowserEvent('show-job_postingDeleteModal');
    }

    public function destroy(){
        $job_posting = JobPosting::find($this->job_posting_id);
        $applications = $job_posting->applications;
        if ($applications) {
            foreach ($applications as $application) {
                $recruitment_candidate = $application->recruitment_candidate;
                $recruitment_candidate->checks()->delete();
                $recruitment_candidate->decisions()->delete();
                $recruitment_candidate->qualifications()->delete();
                $recruitment_candidate->scores()->delete();
                $recruitment_candidate?->delete();
                $application?->delete();
            }
        }
        $job_posting->delete();
        $this->dispatchBrowserEvent('hide-job_postingDeleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Posting Deleted Successfully!!"
        ]);
    }
    
    public function render()
    {
        $baseQuery = JobPosting::query()
    ->with([
        'department:id,name',
        'job_title:id,title',
        'rank:id,name',
        'grade:id,grade_code,grade_name',
    ]);

if ($this->search) {
    $search = trim($this->search);

    $baseQuery->where(function ($q) use ($search) {
        // Direct columns on job_postings
        $q->where('job_posting_number', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('due_date', 'like', "%{$search}%")
            ->orWhere('start_date', 'like', "%{$search}%")
            ->orWhere('contract_type', 'like', "%{$search}%")
            ->orWhere('duration', 'like', "%{$search}%")
            ->orWhere('instructions', 'like', "%{$search}%")
            ->orWhere('requirements', 'like', "%{$search}%")
            ->orWhere('duties', 'like', "%{$search}%")

            // Relations: department
            ->orWhereHas('department', function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%");
            })

            // Relations: job_title
            ->orWhereHas('job_title', function ($qq) use ($search) {
                $qq->where('title', 'like', "%{$search}%");
            })

            // Relations: rank
            ->orWhereHas('rank', function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%");
            })

            // Relations: grade
            ->orWhereHas('grade', function ($qq) use ($search) {
                $qq->where('grade_code', 'like', "%{$search}%")
                   ->orWhere('grade_name', 'like', "%{$search}%");
            });
            });
        }

        $this->job_postings = $baseQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.job-postings.index', [
            'job_postings' => $this->job_postings,
        ]);
    }
}
