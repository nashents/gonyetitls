<?php

namespace App\Http\Livewire\Applications;

use App\Exports\ApplicationsExport;
use App\Models\Application;
use App\Models\Check;
use App\Models\Criterion;
use App\Models\JobPosting;
use App\Models\Qualification;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCheck;
use App\Models\RecruitmentDecision;
use App\Models\RecruitmentQualification;
use App\Models\RecruitmentScore;
use App\Models\Stage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

 
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    protected $applications;
    public $application; 
    public $application_filter; 
    public $application_id;
    public $recruitment_candidate_id;
    public $date;
    public $notes;
    public $name;
    public $surname;
    public $email;
    public $phonenumber;
    public $gender;
    public $dob;
    public $idnumber;
    public $license_number;
    public $years_experience;
    public $source;
    public $status;
    public $screening_impression;
    public $next_step;
    public $job_postings;

    public $job_posting_id;

    public $item_id;
    public $score_id;
    public $decision_id;
    public $check_id;
    public $category;
    public $selected_qualification_id;
    public $checks;
    public $criterions;
    public $criterion_id;
    public $stages;
    public $stage_id;
    public $qualifications;


    public $comments = [];
    public $check_attachment = [];
    public $check_name = [];
    public $result = [];
    
    public $existing_qualifications = [];
    public $existing_scores = [];
    public $existing_decisions  = [];
    public $existing_checks = [];
    public $current_comments = [];
    public $current_check_attachment = [];
    public $current_check_name = [];
    public $current_result = [];
    public $old_attachment = [];
 
    public $criterion = [];
    public $score = [];
    public $stage_name = [];
    public $weight = [];
    public $decision = [];

    public $level = [];
    public $expires_at = [];
    public $certificate_path = [];
    public $date_awarded = [];
    public $qualification_id = [];
    public $qualification_status = [];
    public $old_certificate = [];
  
  
    public $current_criterion = [];
    public $current_score = [];
    public $current_stage_name = [];
    public $current_weight = [];
    public $current_decision = [];

    public $current_level = [];
    public $current_expires_at = [];
    public $current_certificate_path = [];
    public $current_date_awarded = [];
    public $current_qualification_id = [];
    public $current_qualification_status = [];
 

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
        $this->job_postings = JobPosting::orderBy('created_at','desc')->get();
        $this->checks = collect();
        $this->criterions = Criterion::orderBy('name','asc')->get();
        $this->stages = collect();
        $this->qualifications = collect();
        $this->application_filter = "created_at";
    }

    public function exportApplicationsCSV(Excel $excel){
        return $excel->download(new ApplicationsExport($this->from, $this->to,  $this->search, $this->application_filter), 'applications' .time().'.csv', Excel::CSV);
    }
    public function exportApplicationsPDF(Excel $excel){
        return $excel->download(new ApplicationsExport($this->from, $this->to,  $this->search, $this->application_filter), 'applications' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportApplicationsExcel(Excel $excel){
        return $excel->download(new ApplicationsExport($this->from, $this->to,  $this->search, $this->application_filter), 'applications' .time().'.xlsx');
    }


     public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->date = "";
        $this->notes = "";
        $this->source = "";
        $this->dob = "";
        $this->gender = "";
        $this->name = "";
        $this->surname = "";
        $this->email = "";
        $this->phonenumber = "";
        $this->license_number = "";
        $this->idnumber = "";
        $this->job_posting_id = "";
        $this->years_experience = "";
        $this->next_step = "";
        $this->screening_impression = "";
        $this->status = "";
        $this->item_id = Null;

        $this->check_name = [];
        $this->result = [];
        $this->comments = [];
        $this->check_attachment = [];
        $this->stage_name = [];
        $this->score = [];
        $this->criterion = [];
        $this->weight = [];
        $this->decision = [];

        $this->level = [];
        $this->date_awarded = [];
        $this->expires_at = [];
        $this->qualification_id = [];
        $this->qualification_status = [];
        $this->certificate_path = [];
      
        $this->current_level = [];
        $this->current_date_awarded = [];
        $this->current_expires_at = [];
        $this->current_qualification_id = [];
        $this->current_qualification_status = [];
        $this->current_certificate_path = [];
        $this->old_certificate = [];
   
        $this->current_check_name = [];
        $this->current_result = [];
        $this->current_comments = [];
        $this->current_check_attachment = [];
        $this->current_stage_name = [];
        $this->current_score = [];
        $this->current_criterion = [];
        $this->current_weight = [];
        $this->current_decision = [];
        
        $this->existing_checks = [];
        $this->existing_decisions = [];
        $this->existing_scores = [];
        $this->existing_qualifications = [];

        $this->inputs = [];

    }

   


    protected $rules = [
        'date' => 'required', 
    ];

       public function applicationNumber(){
       
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

            $application = Application::orderBy('id', 'desc')->first();

        if (!$application) {
            $application_number =  $initials .'AP'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $application->id + 1;
            $application_number =  $initials .'AP'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $application_number;


    }

        public function refresh($category){

        if($category == "checks"){
            $this->checks = Check::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checks Refreshed Successfully!!."
            ]);
        }
        elseif($category == "stages"){
            $this->stages = Stage::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Stages Refreshed Successfully!!."
            ]);
        }
        elseif($category == "qualifications"){
            $this->qualifications = Qualification::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Qualifications Refreshed Successfully!!."
            ]);
        }
        elseif($category == "criterions"){
              $this->criterions = Criterion::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Criterions Refreshed Successfully!!."
            ]);
        }
       
      
    }

     public function removeShow($id, $category){
        $this->category = $category;
        $this->item_id = $id;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeItem(){ 

        if(!isset($this->category)){
            return ;
        }

        if($this->category == "checks"){
            $check = RecruitmentCheck::find($this->item_id);
            $check?->delete();
        }
        elseif($this->category == "scores"){
            $score = RecruitmentScore::find($this->item_id);
            $score?->delete();
        }
        elseif($this->category == "decisions"){
            $decision = RecruitmentDecision::find($this->item_id);
            $decision?->delete();
        }
        elseif($this->category == "qualifications"){
            
            $qualification = RecruitmentQualification::find($this->item_id);
            $qualification?->delete();
        }

     
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Deleted Successfully!!"
        ]);
       
    }

     public function showQualifications($id){

        $this->recruitment_candidate_id = $id;
        
         // Get qualification IDs already linked to this job title
        $existingQualifications = RecruitmentQualification::where('candidate_id', $id)
            ->pluck('qualification_id')
            ->toArray();

        // Fetch only qualifications NOT already assigned
        $this->qualifications = Qualification::whereNotIn('id', $existingQualifications)
            ->orderBy('name', 'asc')
            ->get();

        $this->dispatchBrowserEvent('show-qualificationModal');
    }

    public function addQualifications(){

        $this->validate([
            // This just ensures the candidate exists — NOT unique
        
            'qualification_id' => [
                'required',
                'exists:qualifications,id',

                // 👇 This is the ONLY uniqueness rule: (candidate_id + qualification_id) among active rows
                Rule::unique('recruitment_qualifications')
                    ->where(fn ($query) => $query
                        ->where('candidate_id', $this->recruitment_candidate_id)
                        ->whereNull('deleted_at')
                    ),
            ],
        ]);
       
        if(isset($this->qualification_id)){
            foreach ($this->qualification_id as $key => $id) {
             
                $quali_id = $id;
                $level = $this->level[$key] ?? Null;
                $date_awarded = $this->date_awarded[$key] ?? Null;
                $expires_at = $this->expires_at[$key] ?? Null;
                $certificate_path = $this->certificate_path[$key] ?? Null;
                $status = $this->qualification_status[$key] ?? Null;
               
            
                $recruitment_qualification = new RecruitmentQualification;
                $recruitment_qualification->candidate_id = $this->recruitment_candidate_id;
                $recruitment_qualification->qualification_id = $quali_id;
                $recruitment_qualification->verified_by = Auth::user()->id;
                $recruitment_qualification->verified_at = now();
                $recruitment_qualification->level = $level;
                $recruitment_qualification->date_awarded = $date_awarded;
                $recruitment_qualification->expires_at = $expires_at;
                $recruitment_qualification->status = $status;
                if($certificate_path){

                    $file = $certificate_path;
                    // get file with ext
                    $fileNameWithExt = $file->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore= $filename.'_'.time().'.'.$extention;
                    $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    $recruitment_qualification->certificate_path = $fileNameToStore;
                }

                $recruitment_qualification->save();
                  
                $this->dispatchBrowserEvent('hide-qualificationModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Recruitment Qualifications Added Successfully!!"
                ]);
                
            }

        }
       
    }

      public function showEditQualifications($id){

        $this->recruitment_candidate_id = $id;
        $candidate = RecruitmentCandidate::find($id);
        $this->existing_qualifications = $candidate->qualifications;
        if ($this->existing_qualifications) {
            foreach ($this->existing_qualifications as $qualification) {
                $this->current_qualification_id[] = $qualification->qualification_id;
                $this->current_level[] = $qualification->level;
                $this->current_date_awarded[] = $qualification->date_awarded;
                $this->current_expires_at[] = $qualification->expires_at;
                $this->current_qualification_status[] = $qualification->status;
                $this->current_certificate_path[] = $qualification->certificate_path;
                $this->old_certificate[] = $qualification->certificate_path;
            }
        }
        $this->qualifications = Qualification::orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-qualificationEditModal');
    }
    
    public function updateQualificaitions(){

        if(isset($this->existing_qualifications)){
            foreach ($this->existing_qualifications as $key => $existing_qualification) {

                $qualification_id = $this->current_qualification_id[$key] ?? Null;;
                $status = $this->current_qualification_status[$key] ?? Null;
                $level = $this->current_level[$key] ?? Null;
                $date_awarded = $this->current_date_awarded[$key] ?? Null;
                $expires_at = $this->current_expires_at[$key] ?? Null;
                $certificate_path = $this->current_certificate_path[$key] ?? Null;

                $recruitment_qualification = RecruitmentQualification::find($existing_qualification->id);
                $recruitment_qualification->candidate_id = $this->recruitment_candidate_id;
                $recruitment_qualification->qualification_id = $this->qualification_id;
                $recruitment_qualification->verified_by = Auth::user()->id;
                $recruitment_qualification->verified_at = now();
                $recruitment_qualification->level = $level;
                $recruitment_qualification->date_awarded = $date_awarded;
                $recruitment_qualification->expires_at = $expires_at;
                $recruitment_qualification->status = $status;
                if($certificate_path){

                    $file = $certificate_path;
                    // get file with ext
                    $fileNameWithExt = $file->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore= $filename.'_'.time().'.'.$extention;
                    $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    $recruitment_qualification->certificate_path = $fileNameToStore;
                }

                $recruitment_qualification->update();
                   
            }
        }
      
        if(isset($this->qualification_id)){
            foreach ($this->qualification_id as $key => $id) {

                $qualification_id = $id;
                $level = $this->level[$key] ?? Null;
                $date_awarded = $this->date_awarded[$key] ?? Null;
                $expires_at = $this->expires_at[$key] ?? Null;
                $certificate_path = $this->certificate_path[$key] ?? Null;
                $status = $this->qualification_status[$key] ?? Null;
             

                $recruitment_qualification = new RecruitmentQualification;
                $recruitment_qualification->candidate_id = $this->recruitment_candidate_id;
                $recruitment_qualification->qualification_id = $qualification_id;
                $recruitment_qualification->verified_by = Auth::user()->id;
                $recruitment_qualification->verified_at = now();
                $recruitment_qualification->level = $level;
                $recruitment_qualification->date_awarded = $date_awarded;
                $recruitment_qualification->expires_at = $expires_at;
                $recruitment_qualification->status = $status;
                if($certificate_path){

                    $file = $certificate_path;
                    // get file with ext
                    $fileNameWithExt = $file->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore= $filename.'_'.time().'.'.$extention;
                    $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    $recruitment_qualification->certificate_path = $fileNameToStore;
                }

                $recruitment_qualification->save();
                  
               
                
            }

        }

        $this->dispatchBrowserEvent('hide-qualificationEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Recruitment Qualifications Updated Successfully!!"
                ]);
       
    }
     public function showScores($id){

        $this->recruitment_candidate_id = $id;
        
         // Get qualification IDs already linked to this job title
        $existingStages = RecruitmentScore::where('candidate_id', $id)
            ->pluck('stage')
            ->toArray();

        // Fetch only qualifications NOT already assigned
        $this->stages = Stage::whereNotIn('name', $existingStages)
            ->orderBy('name', 'asc')
            ->get();

        $this->dispatchBrowserEvent('show-stageModal');
    }

    public function addScores(){
        if(isset($this->stage_name)){
            foreach ($this->stage_name as $key => $name) {

                $stage = $name;
                $criterion = $this->criterion[$key] ?? Null;
                $comments = $this->comments[$key] ?? Null;
                $weight = $this->weight[$key] ?? Null;
                $score = $this->score[$key] ?? Null;

                $recruitment_score = new RecruitmentScore;
                $recruitment_score->candidate_id = $this->recruitment_candidate_id;
                $recruitment_score->scored_by = Auth::user()->id;
                $recruitment_score->stage = $stage;
                $recruitment_score->criterion = $criterion;
                $recruitment_score->weight = $weight;
                $recruitment_score->score_percent = $score;
                $recruitment_score->comment = $comments;
                $recruitment_score->save();
                  
                $this->dispatchBrowserEvent('hide-stageModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Recruitment Stages Scored Successfully!!"
                ]);
                
            }

        }
       
    }

      public function showEditScores($id){

        $this->recruitment_candidate_id = $id;
        $candidate = RecruitmentCandidate::find($id);
        $this->existing_scores = $candidate->scores;
        if ($this->existing_scores) {
            foreach ($this->existing_scores as $score) {
                $this->current_stage_name[] = $score->stage;
                $this->current_criterion[] = $score->criterion;
                $this->current_comments[] = $score->comment;
                $this->current_weight[] = $score->weight;
                $this->current_score[] = $score->score_percent;
            }
        }
        $this->stages = Stage::orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-stageEditModal');
    }
    
    public function updateScores(){

        if(isset($this->existing_scores)){
            foreach ($this->existing_scores as $key => $score) {

                $stage = $this->current_stage_name[$key] ?? Null;;
                $criterion = $this->current_criterion[$key] ?? Null;
                $comments = $this->current_comments[$key] ?? Null;
                $weight = $this->current_weight[$key] ?? Null;
                $score = $this->current_score[$key] ?? Null;

                $recruitment_score = RecruitmentScore::find($score->id);
                $recruitment_score->candidate_id = $this->recruitment_candidate_id;
                $recruitment_score->scored_by = Auth::user()->id;
                $recruitment_score->stage = $stage;
                $recruitment_score->criterion = $criterion;
                $recruitment_score->weight = $weight;
                $recruitment_score->score_percent = $score;
                $recruitment_score->comment = $comments;
                $recruitment_score->update();
                   
            }
        }
      
        if(isset($this->stage_name)){
            foreach ($this->stage_name as $key => $name) {

                $stage = $name;
                $criterion = $this->criterion[$key] ?? Null;
                $comments = $this->comments[$key] ?? Null;
                $weight = $this->weight[$key] ?? Null;
                $score = $this->score[$key] ?? Null;

                $recruitment_score = new RecruitmentScore;
                $recruitment_score->candidate_id = $this->recruitment_candidate_id;
                $recruitment_score->scored_by = Auth::user()->id;
                $recruitment_score->stage = $stage;
                $recruitment_score->criterion = $criterion;
                $recruitment_score->weight = $weight;
                $recruitment_score->score_percent = $score;
                $recruitment_score->comment = $comments;
                $recruitment_score->save();
                
            }
        }

        $this->dispatchBrowserEvent('hide-stageEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Recruitment Stages Scores Updated Successfully!!"
        ]);
       
    }

    public function showDecisions($id){

        $this->recruitment_candidate_id = $id;
        
         // Get qualification IDs already linked to this job title
        $existingDecisions = RecruitmentDecision::where('candidate_id', $id)
            ->pluck('stage')
            ->toArray();

        // Fetch only qualifications NOT already assigned
        $this->stages = Stage::whereNotIn('name', $existingDecisions)
            ->orderBy('name', 'asc')
            ->get();

        $this->dispatchBrowserEvent('show-decisionModal');
    }

       public function addDecisions(){


        if(isset($this->stage_name)){
          
            foreach ($this->stage_name as $key => $name) {
            
                $stage = $name;
                $decision = $this->decision[$key] ?? Null;
                $comments = $this->comments[$key] ?? Null;
               
                $recruitment_decision = new RecruitmentDecision;
                $recruitment_decision->candidate_id = $this->recruitment_candidate_id;
                $recruitment_decision->decided_by = Auth::user()->id;
                $recruitment_decision->decided_at = now();
                $recruitment_decision->stage = $stage;
                $recruitment_decision->decision = $decision;
                $recruitment_decision->comment = $comments;
                $recruitment_decision->save();
                  
                $this->dispatchBrowserEvent('hide-decisionModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Recruitment Decision Effected Successfully!!"
                ]);
                
            }

        }
       
    }

      public function showEditDecisions($id){

        $this->recruitment_candidate_id = $id;
        $candidate = RecruitmentCandidate::find($id);
        $this->existing_decisions = $candidate->decisions;
        if ($this->existing_decisions) {
            foreach ($this->existing_decisions as $decision) {
                $this->current_stage_name[] = $decision->stage;
                $this->current_decision[] = $decision->decision;
                $this->current_comments[] = $decision->comment;
            }
        }
        $this->stages = Stage::orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-decisionEditModal');
    }
    
    public function updateDecisions(){

    

        if(isset($this->existing_decisions)){
            foreach ($this->existing_decisions as $key => $existing_decision) {
               
                $stage = $this->current_stage_name[$key] ?? Null;;
                $decision = $this->current_decision[$key] ?? Null;
                $comments = $this->current_comments[$key] ?? Null;

                $recruitment_decision = RecruitmentDecision::find($existing_decision->id);
                $recruitment_decision->candidate_id = $this->recruitment_candidate_id;
                $recruitment_decision->decided_by = Auth::user()->id;
                $recruitment_decision->stage = $stage;
                $recruitment_decision->decision = $decision;
                $recruitment_decision->comment = $comments;
                $recruitment_decision->update();
                   
            }
        }
      
        if(isset($this->stage_name)){
            foreach ($this->stage_name as $key => $name) {

                $stage = $name;
                $decision = $this->decision[$key] ?? Null;
                $comments = $this->comments[$key] ?? Null;

                $recruitment_decision = new RecruitmentDecision;
                $recruitment_decision->candidate_id = $this->recruitment_candidate_id;
                $recruitment_decision->decided_by = Auth::user()->id;
                $recruitment_decision->stage = $stage;
                $recruitment_decision->decision = $decision;
                $recruitment_decision->comment = $comments;
                $recruitment_decision->save();
                
            }

        }

        $this->dispatchBrowserEvent('hide-decisionEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Recruitment Decisions Updated Successfully!!"
        ]);
       
    }
 
    public function showChecks($id){

        $this->recruitment_candidate_id = $id;
        
         // Get qualification IDs already linked to this job title
        $existingChecks = RecruitmentCheck::where('candidate_id', $id)
            ->pluck('type')
            ->toArray();

        // Fetch only qualifications NOT already assigned
        $this->checks = Check::whereNotIn('name', $existingChecks)
            ->orderBy('name', 'asc')
            ->get();

        $this->dispatchBrowserEvent('show-checkModal');
    }

     public function addChecks(){
        if(isset($this->check_name)){
            foreach ($this->check_name as $key => $name) {

                $type = $name;
                $result = $this->result[$key] ?? Null;
                $comments = $this->comments[$key] ?? Null;
                $attachment = $this->check_attachment[$key] ?? Null;

                $recruitment_check = new RecruitmentCheck;
                $recruitment_check->candidate_id = $this->recruitment_candidate_id;
                $recruitment_check->checked_by = Auth::user()->id;
                $recruitment_check->type = $type;
                $recruitment_check->result = $result;
                $recruitment_check->checked_at = now();
                $recruitment_check->comment = $comments;
               
                if($attachment){

                    $file = $attachment;
                    // get file with ext
                    $fileNameWithExt = $file->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore= $filename.'_'.time().'.'.$extention;
                    $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    $recruitment_check->attachment_path = $fileNameToStore;
                }

                $recruitment_check->save();
                  
                $this->dispatchBrowserEvent('hide-checkModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Recruitment Check Uploaded Successfully!!"
                ]);
                
            }

        }
       
    }
    
    public function showEditChecks($id){

        $this->recruitment_candidate_id = $id;
        $candidate = RecruitmentCandidate::find($id);
        $this->existing_checks = $candidate->checks;
        if ($this->existing_checks) {
            foreach ($this->existing_checks as $check) {
                $this->current_check_name[] = $check->type;
                $this->current_result[] = $check->result;
                $this->current_comments[] = $check->comment;
                $this->current_check_attachment[] = $check->attachment_path;
                $this->old_attachment[] = $check->attachment_path;
            }
        }
        $this->checks = Check::orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-checkEditModal');
    }

    public function updateChecks(){

        if(isset($this->check_name)){

            foreach ($this->check_name as $key => $name) {

                $type = $name;
                $result = $this->result[$key] ?? Null;
                $comments = $this->comments[$key] ?? Null;
                $attachment = $this->check_attachment[$key] ?? Null;

                $recruitment_check = new RecruitmentCheck;
                $recruitment_check->candidate_id = $this->recruitment_candidate_id;
                $recruitment_check->checked_by = Auth::user()->id;
                $recruitment_check->type = $type;
                $recruitment_check->result = $result;
                $recruitment_check->checked_at = now();
                $recruitment_check->comment = $comments;
               
                if($attachment){
                    $file = $attachment;
                    // get file with ext
                    $fileNameWithExt = $file->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore= $filename.'_'.time().'.'.$extention;
                    $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    $recruitment_check->attachment_path = $fileNameToStore;
                }

                $recruitment_check->save();
                 
            }

        }
        if(isset($this->existing_checks)){

            foreach ($this->existing_checks as $key => $check) {
               
                $type = $this->current_check_name[$key];
                $result = $this->current_result[$key] ?? Null;
                $comments = $this->current_comments[$key] ?? Null;
                $attachment = $this->current_check_attachment[$key] ?? Null;

                $recruitment_check = RecruitmentCheck::find($check->id);
                $recruitment_check->candidate_id = $this->recruitment_candidate_id;
                $recruitment_check->checked_by = Auth::user()->id;
                $recruitment_check->type = $type;
                $recruitment_check->result = $result;
                $recruitment_check->checked_at = now();
                $recruitment_check->comment = $comments;
               
                if($attachment){
                    $file = $attachment;
                    // get file with ext
                    $fileNameWithExt = $file->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore= $filename.'_'.time().'.'.$extention;
                    $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    $recruitment_check->attachment_path = $fileNameToStore;
                }

                $recruitment_check->update();
                  
               
            }

             $this->dispatchBrowserEvent('hide-checkEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Recruitment Checks Updated Successfully!!"
                ]);

        }
       
    }
   

    public function store(){

        DB::transaction(function () {
        
        $this->validate();

        $application = new Application();
        $application->application_number = $this->applicationNumber();
        $application->user_id = Auth::user()->id;
        $application->date = $this->date;
        $application->notes = $this->notes;
        $application->job_posting_id = $this->job_posting_id;
        $application->save();

        $recruitment_candidate = new RecruitmentCandidate;
        $recruitment_candidate->company_id = Auth::user()->employee->company_id;
        $recruitment_candidate->created_by = Auth::user()->id;
        $recruitment_candidate->application_id = $application->id;
        $recruitment_candidate->applied_at = $this->date;
        $recruitment_candidate->first_name = $this->name;
        $recruitment_candidate->last_name = $this->surname;
        $recruitment_candidate->gender = $this->gender;
        $age = $age = Carbon::parse($this->dob)->age;
        $recruitment_candidate->dob = $this->dob;
        $recruitment_candidate->age = $age;
        $recruitment_candidate->email = $this->email;
        $recruitment_candidate->phone = $this->phonenumber;
        $recruitment_candidate->source = $this->source;
        $recruitment_candidate->national_id = $this->idnumber;
        $recruitment_candidate->drivers_license_number = $this->license_number;
        $recruitment_candidate->years_experience = $this->years_experience;
        $recruitment_candidate->next_step = $this->next_step;
        $recruitment_candidate->status = $this->status;
        $recruitment_candidate->screening_impression = $this->screening_impression;
        $recruitment_candidate->notes = $this->notes;
        $recruitment_candidate->save();
        

        $this->dispatchBrowserEvent('hide-applicationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Application Created Successfully!!"
        ]);
        
        });
    }
    
    public function edit($id){
    
        $application = Application::find($id);
     
        $this->date = $application->date;
        $this->application_id = $application->id;
       

          $this->dispatchBrowserEvent('show-applicationEditModal');
    }

    public function update(){

        DB::transaction(function () {
        
        $this->validate();

        $application = Application::find($this->application_id);
        $application->date = $this->date;
        $application->job_posting_id = $this->selectedjob_posting;
        $application->update();

        

        $this->dispatchBrowserEvent('hide-applicationEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"application Register Update Successfully!!"
        ]);
        
        });
    }


    public function render()
    {
       $search = trim($this->search);

        $applications = Application::query()->with(['job_posting','recruitment_candidate','recruitment_candidate.checks','recruitment_candidate.decisions'])
           
                 // ✅ date filter on date when from/to provided
            ->when($this->from || $this->to, function ($q) {
                $from = $this->from
                    ? Carbon::parse($this->from)->startOfDay()
                    : null;

                $to = $this->to
                    ? Carbon::parse($this->to)->endOfDay()
                    : null;

                if ($from && $to) {
                    $q->whereBetween('date', [$from, $to]);
                } elseif ($from) {
                    $q->where('date', '>=', $from);
                } else { // only $to
                    $q->where('date', '<=', $to);
                }
            })
            ->when($search !== '', function ($q) use ($search) {

                $q->where(function ($qq) use ($search) {

                    // job_posting name
                    $qq->whereHas('job_posting', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%");
                    })

                    // user name / surname / full name
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(name,' ',surname) LIKE ?", ["%{$search}%"]);
                    })

                    // date/time on created_at (works for "2026-02-05", "14:30", "2026-02-05 14")
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%H:%i') LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderBy($this->application_filter, 'desc')
            ->paginate(10);

        return view('livewire.applications.index', [
            'applications' => $applications,
        ]);
    }
}
