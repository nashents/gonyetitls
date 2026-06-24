<?php

namespace App\Http\Livewire\Applications;

use App\Exports\ApplicationsExport;
use App\Mail\AccountCreationMail;
use App\Models\Application;
use App\Models\Check;
use App\Models\Company;
use App\Models\Criterion;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\JobPosting;
use App\Models\JobTitle;
use App\Models\Qualification;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCheck;
use App\Models\RecruitmentDecision;
use App\Models\RecruitmentQualification;
use App\Models\RecruitmentScore;
use App\Models\Role;
use App\Models\Stage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
    public $years_experience = 0;
    public $source;
    public $status = "Applied";
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
        array_push($this->inputs, $i);
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
        return $this->companyInitials() . 'AP' . str_pad(
            optional(Application::orderBy('id', 'desc')->first())->id + 1 ?: 1,
            5, "0", STR_PAD_LEFT
        );
    }

    /* ===========================================================
     |  Lock-gating: qualifications -> checks -> scores -> decisions
     * =========================================================== */

    private function gateBlocked($candidate, $relation, $message){
        if (!$candidate || $candidate->$relation()->count() == 0) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $message]);
            return true;
        }
        return false;
    }

    public function refresh($category){

        if($category == "checks"){
            $this->checks = Check::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Checks Refreshed Successfully!!."]);
        }
        elseif($category == "stages"){
            $this->stages = Stage::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Stages Refreshed Successfully!!."]);
        }
        elseif($category == "qualifications"){
            $this->qualifications = Qualification::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Qualifications Refreshed Successfully!!."]);
        }
        elseif($category == "criterions"){
            $this->criterions = Criterion::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Criterions Refreshed Successfully!!."]);
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
            RecruitmentCheck::find($this->item_id)?->delete();
        }
        elseif($this->category == "scores"){
            RecruitmentScore::find($this->item_id)?->delete();
        }
        elseif($this->category == "decisions"){
            RecruitmentDecision::find($this->item_id)?->delete();
        }
        elseif($this->category == "qualifications"){
            RecruitmentQualification::find($this->item_id)?->delete();
        }

        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Item Deleted Successfully!!"]);
    }

    /* ----------------------- QUALIFICATIONS (step 1, always open) -------- */

    public function showQualifications($id){

        $this->recruitment_candidate_id = $id;

        $existingQualifications = RecruitmentQualification::where('candidate_id', $id)
            ->pluck('qualification_id')->toArray();

        $this->qualifications = Qualification::whereNotIn('id', $existingQualifications)
            ->orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-qualificationModal');
    }

    public function addQualifications(){

        $this->validate([
            'qualification_id' => [
                'required',
                'exists:qualifications,id',
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
                    $recruitment_qualification->certificate_path = $this->storeUpload($certificate_path);
                }

                $recruitment_qualification->save();

                $this->dispatchBrowserEvent('hide-qualificationModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Qualifications Added Successfully!!"]);
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
                $this->old_certificate[] = $qualification->certificate_path;
            }
        }
        $this->qualifications = Qualification::orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-qualificationEditModal');
    }

    public function updateQualifications(){

        if(isset($this->existing_qualifications)){

            foreach ($this->existing_qualifications as $key => $existing_qualification) {

                $qualification_id = $this->current_qualification_id[$key] ?? Null;
                $status = $this->current_qualification_status[$key] ?? Null;
                $level = $this->current_level[$key] ?? Null;
                $date_awarded = $this->current_date_awarded[$key] ?? Null;
                $expires_at = $this->current_expires_at[$key] ?? Null;
                $certificate_path = $this->current_certificate_path[$key] ?? Null;

                $recruitment_qualification = RecruitmentQualification::find($existing_qualification->id);
                $recruitment_qualification->candidate_id = $this->recruitment_candidate_id;
                $recruitment_qualification->qualification_id = $qualification_id;
                $recruitment_qualification->verified_by = Auth::user()->id;
                $recruitment_qualification->verified_at = now();
                $recruitment_qualification->level = $level;
                $recruitment_qualification->date_awarded = $date_awarded;
                $recruitment_qualification->expires_at = $expires_at;
                $recruitment_qualification->status = $status;
                if($certificate_path){
                    $recruitment_qualification->certificate_path = $this->storeUpload($certificate_path);
                }

                $recruitment_qualification->update();
            }
        }

        $this->dispatchBrowserEvent('hide-qualificationEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Qualifications Updated Successfully!!"]);
    }

    /* ----------------------- CHECKS (step 2, needs qualifications) ------- */

    public function showChecks($id){

        $candidate = RecruitmentCandidate::find($id);
        if ($this->gateBlocked($candidate, 'qualifications', 'Add at least one qualification before recording checks.')) {
            return;
        }

        $this->recruitment_candidate_id = $id;

        $existingChecks = RecruitmentCheck::where('candidate_id', $id)->pluck('type')->toArray();

        $this->checks = Check::whereNotIn('name', $existingChecks)
            ->orderBy('name', 'asc')->get();

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
                    $recruitment_check->attachment_path = $this->storeUpload($attachment);
                }

                $recruitment_check->save();

                $this->dispatchBrowserEvent('hide-checkModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Check Uploaded Successfully!!"]);
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
                $this->old_attachment[] = $check->attachment_path;
            }
        }
        $this->checks = Check::orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-checkEditModal');
    }

    public function updateChecks(){

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
                    $recruitment_check->attachment_path = $this->storeUpload($attachment);
                }

                $recruitment_check->update();
            }

            $this->dispatchBrowserEvent('hide-checkEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Checks Updated Successfully!!"]);
        }
    }

    /* ----------------------- SCORES (step 3, needs checks) --------------- */

    public function showScores($id){

        $candidate = RecruitmentCandidate::find($id);
        if ($this->gateBlocked($candidate, 'checks', 'Complete checks before scoring this candidate.')) {
            return;
        }

        $this->recruitment_candidate_id = $id;

        $existingStages = RecruitmentScore::where('candidate_id', $id)->pluck('stage')->toArray();

        $this->stages = Stage::whereNotIn('name', $existingStages)
            ->orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-stageModal');
    }

    public function addScores(){

        if(isset($this->stage_name)){


            foreach ($this->stage_name as $key => $name) {

                $recruitment_score = new RecruitmentScore;
                $recruitment_score->candidate_id = $this->recruitment_candidate_id;
                $recruitment_score->scored_by = Auth::user()->id;
                $recruitment_score->stage = $name;
                $recruitment_score->criterion = $this->criterion[$key] ?? 0;
                $recruitment_score->weight = $this->weight[$key] ?? 0;
                $recruitment_score->score_percent = $this->score[$key] ?? Null;
                $recruitment_score->comment = $this->comments[$key] ?? Null;
                $recruitment_score->save();

               
            }
                $this->dispatchBrowserEvent('hide-stageModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Interview Scored Successfully!!"]);      
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

                $stage = $this->current_stage_name[$key] ?? Null;
                $criterion = $this->current_criterion[$key] ?? Null;
                $comments = $this->current_comments[$key] ?? Null;
                $weight = $this->current_weight[$key] ?? Null;
                $score_value = $this->current_score[$key] ?? Null;

                $recruitment_score = RecruitmentScore::find($score?->id);
                if($recruitment_score){
                    $recruitment_score->candidate_id = $this->recruitment_candidate_id;
                    $recruitment_score->scored_by = Auth::user()->id;
                    $recruitment_score->stage = $stage;
                    $recruitment_score->criterion = $criterion;
                    $recruitment_score->weight = $weight;
                    $recruitment_score->score_percent = $score_value;
                    $recruitment_score->comment = $comments;
                    $recruitment_score->update();
                }
            }
        }

        if(isset($this->stage_name)){
            foreach ($this->stage_name as $key => $name) {

                $recruitment_score = new RecruitmentScore;
                $recruitment_score->candidate_id = $this->recruitment_candidate_id;
                $recruitment_score->scored_by = Auth::user()->id;
                $recruitment_score->stage = $name;
                $recruitment_score->criterion = $this->criterion[$key] ?? Null;
                $recruitment_score->weight = $this->weight[$key] ?? Null;
                $recruitment_score->score_percent = $this->score[$key] ?? Null;
                $recruitment_score->comment = $this->comments[$key] ?? Null;
                $recruitment_score->save();
            }
        }

        $this->dispatchBrowserEvent('hide-stageEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Stages Scores Updated Successfully!!"]);
    }

    /* ----------------------- DECISIONS (step 4, needs scores) ------------ */

    public function showDecisions($id){

        $candidate = RecruitmentCandidate::find($id);
        if ($this->gateBlocked($candidate, 'scores', 'Score the candidate before making a decision.')) {
            return;
        }

        $this->recruitment_candidate_id = $id;

        $existingDecisions = RecruitmentDecision::where('candidate_id', $id)->pluck('stage')->toArray();

        $this->stages = Stage::whereNotIn('name', $existingDecisions)
            ->orderBy('name', 'asc')->get();

        $this->dispatchBrowserEvent('show-decisionModal');
    }

    public function addDecisions(){

        if(isset($this->stage_name)){
            foreach ($this->stage_name as $key => $name) {

                $recruitment_decision = new RecruitmentDecision;
                $recruitment_decision->candidate_id = $this->recruitment_candidate_id;
                $recruitment_decision->decided_by = Auth::user()->id;
                $recruitment_decision->decided_at = now();
                $recruitment_decision->stage = $name;
                $recruitment_decision->decision = $this->decision[$key] ?? Null;
                $recruitment_decision->comment = $this->comments[$key] ?? Null;
                $recruitment_decision->save();
            }
        }

        $this->applyDecisionOutcome();

        $this->dispatchBrowserEvent('hide-decisionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Decision Effected Successfully!!"]);
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

                $recruitment_decision = RecruitmentDecision::find($existing_decision->id);
                $recruitment_decision->candidate_id = $this->recruitment_candidate_id;
                $recruitment_decision->decided_by = Auth::user()->id;
                $recruitment_decision->stage = $this->current_stage_name[$key] ?? Null;
                $recruitment_decision->decision = $this->current_decision[$key] ?? Null;
                $recruitment_decision->comment = $this->current_comments[$key] ?? Null;
                $recruitment_decision->update();
            }
        }

        if(isset($this->stage_name)){
            foreach ($this->stage_name as $key => $name) {

                $recruitment_decision = new RecruitmentDecision;
                $recruitment_decision->candidate_id = $this->recruitment_candidate_id;
                $recruitment_decision->decided_by = Auth::user()->id;
                $recruitment_decision->stage = $name;
                $recruitment_decision->decision = $this->decision[$key] ?? Null;
                $recruitment_decision->comment = $this->comments[$key] ?? Null;
                $recruitment_decision->save();
            }
        }

        $this->applyDecisionOutcome();

        $this->dispatchBrowserEvent('hide-decisionEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Recruitment Decisions Updated Successfully!!"]);
    }

    /* ===========================================================
     |  Auto-provisioning of Employee / Driver on Contracted|Hired
     * =========================================================== */

    // Promote candidate status from a Hired/Contracted decision, then provision.
    private function applyDecisionOutcome(){
        $candidate = RecruitmentCandidate::find($this->recruitment_candidate_id);
        if(!$candidate) return;

        $decisions = $candidate->decisions()->pluck('decision')
            ->map(fn($d) => strtolower((string) $d));

        if($decisions->contains('hired'))            $candidate->status = 'Hired';
        elseif($decisions->contains('contracted'))   $candidate->status = 'Contracted';
        else return;

        $candidate->save();
        $this->provisionStaff($candidate);
    }

    private function companyInitials(){
        $company = Auth::user()->company ?? optional(Auth::user()->employee)->company;
        $words = explode(' ', trim($company?->name ?? ''));
        return isset($words[1][0]) ? $words[0][0].$words[1][0] : ($words[0][0] ?? 'X');
    }

    private function generatePIN(){
        return (string) random_int(100000, 999999);
    }

    private function employeeNumber(){
        $n = (optional(Employee::orderBy('id','desc')->first())->id ?? 0) + 1;
        return $this->companyInitials().'EMP'.str_pad($n, 5, "0", STR_PAD_LEFT);
    }

    private function driverNumber(){
        $n = (optional(Driver::orderBy('id','desc')->first())->id ?? 0) + 1;
        return $this->companyInitials().'DRV'.str_pad($n, 5, "0", STR_PAD_LEFT);
    }

    // Driver if the posting's job title contains "driver", else fall back to a present licence.
    private function isDriverCandidate($candidate){
        $application = Application::with('job_posting.job_title')->find($candidate->application_id);
        $title = strtolower(optional(optional(optional($application)->job_posting)->job_title)->title ?? '');
        if($title !== '') return str_contains($title, 'driver');
        return !empty($candidate->drivers_license_number);
    }

    private function provisionStaff($candidate){

        if(!$candidate || !in_array($candidate->status, ['Contracted','Hired'])) return;
        if($candidate->employee_id) return; // idempotent: already provisioned

        $isDriver = $this->isDriverCandidate($candidate);
        $hasEmail = $candidate->email && filter_var($candidate->email, FILTER_VALIDATE_EMAIL);

        DB::transaction(function () use ($candidate, $isDriver, $hasEmail) {

            $pin = $this->generatePIN();

            $user = new User;
            $user->name = $candidate->first_name;
            $user->surname = $candidate->last_name;
            $user->category = $isDriver ? 'driver' : 'employee';
            $user->email = $candidate->email;
            $user->phonenumber = $candidate->phone;
            $user->use_email_as_username = $hasEmail;
            $user->username = $hasEmail ? $candidate->email : $candidate->phone;
            $user->password = Hash::make($pin);
            $user->save();

            $role = Role::where('name', $isDriver ? 'Driver' : 'Employee')->first();
            if($role){ $user->roles()->sync([$role->id]); }

            $jobTitle = optional(optional(
                Application::with('job_posting.job_title')->find($candidate->application_id)
            )->job_posting)->job_title;

            $employee = new Employee;
            $employee->company_id = $candidate->company_id;
            $employee->creator_id = Auth::id();
            $employee->user_id = $user->id;
            $employee->employee_number = $this->employeeNumber();
            $employee->name = $candidate->first_name;
            $employee->surname = $candidate->last_name;
            $employee->phonenumber = $candidate->phone;
            $employee->email = $candidate->email;
            $employee->pin = $pin;
            $employee->gender = $candidate->gender;
            $employee->dob = $candidate->dob;
            $employee->idnumber = $candidate->national_id;
            $employee->post = $jobTitle?->title;
            $employee->start_date = now()->toDateString();
            $employee->save();

            $position = new EmployeePosition;
            $position->employee_id = $employee->id;
            $position->job_title_id = $jobTitle?->id;
            $position->branch_id = $employee->branch_id ?? Null;
            $position->grade_id = $employee->grade_id ?? Null;
            $position->start_date = $employee->start_date;
            $position->changed_by = Auth::id();
            $position->change_reason = "Appointment";
            $position->remarks = "Recruitment Onboarding";
            $position->save();

            $candidate->employee_id = $employee->id;

            if($isDriver){
                $driver = new Driver;
                $driver->creator_id = Auth::id();
                $driver->employee_id = $employee->id;
                $driver->user_id = $user->id;
                $driver->driver_number = $this->driverNumber();
                $driver->license_number = $candidate->drivers_license_number;
                $driver->experience = $candidate->years_experience;
                $driver->status = 1;
                $driver->save();

                $candidate->driver_id = $driver->id;
            }

            $candidate->save();

            if($hasEmail){
                $company = Company::find($candidate->company_id)
                    ?? (Auth::user()->company ?? optional(Auth::user()->employee)->company);
                Mail::to($candidate->email)->send(new AccountCreationMail($user, $company, $pin));
            }
        });

        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => ($isDriver ? 'Driver' : 'Employee') . ' record auto-created for '
                         . $candidate->first_name . ' ' . $candidate->last_name . '.',
        ]);
    }

    // Centralised, reusable Livewire upload handler.
    private function storeUpload($file){
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileNameToStore = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('/documents', $fileNameToStore, 'my_files');
        return $fileNameToStore;
    }

    /* ===========================================================
     |  Application CRUD
     * =========================================================== */

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
            $recruitment_candidate->dob = $this->dob;
            $recruitment_candidate->age = Carbon::parse($this->dob)->age;
            $recruitment_candidate->email = $this->email;
            $recruitment_candidate->phone = $this->phonenumber;
            $recruitment_candidate->source = $this->source;
            $recruitment_candidate->national_id = $this->idnumber;
            $recruitment_candidate->drivers_license_number = $this->license_number;
            $recruitment_candidate->years_experience = $this->years_experience;
            $recruitment_candidate->status = $this->status;
            $recruitment_candidate->notes = $this->notes;
            $recruitment_candidate->save();

            $this->provisionStaff($recruitment_candidate);

            $this->dispatchBrowserEvent('hide-applicationModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Application Created Successfully!!"]);
        });
    }

    public function edit($id){

        $application = Application::find($id);
        $candidate = $application?->recruitment_candidate;
        $this->date = $application->date;
        $this->application_id = $application->id;
        $this->job_posting_id = $application->job_posting_id;
        $this->notes = $application->notes;
        $this->name = $candidate->first_name;
        $this->surname = $candidate->last_name;
        $this->idnumber = $candidate->national_id;
        $this->license_number = $candidate->drivers_license_number;
        $this->years_experience = $candidate->years_experience;
        $this->phonenumber = $candidate->phone;
        $this->email = $candidate->email;
        $this->dob = $candidate->dob->format('Y-m-d');
        $this->gender = $candidate->gender;
        $this->source = $candidate->source;
        $this->status = $candidate->status;

        $this->dispatchBrowserEvent('show-applicationEditModal');
    }

    public function update(){

        DB::transaction(function () {

            $this->validate();

            $application = Application::find($this->application_id);
            $application->date = $this->date;
            $application->notes = $this->notes;
            $application->job_posting_id = $this->job_posting_id;
            $application->save();

            $recruitment_candidate = RecruitmentCandidate::where('application_id', $application->id)->first();
            if($recruitment_candidate){
                $recruitment_candidate->application_id = $application->id;
                $recruitment_candidate->applied_at = $this->date;
                $recruitment_candidate->first_name = $this->name;
                $recruitment_candidate->last_name = $this->surname;
                $recruitment_candidate->gender = $this->gender;
                $recruitment_candidate->dob = $this->dob;
                $recruitment_candidate->age = Carbon::parse($this->dob)->age;
                $recruitment_candidate->email = $this->email;
                $recruitment_candidate->phone = $this->phonenumber;
                $recruitment_candidate->source = $this->source;
                $recruitment_candidate->national_id = $this->idnumber;
                $recruitment_candidate->drivers_license_number = $this->license_number;
                $recruitment_candidate->years_experience = $this->years_experience;
                $recruitment_candidate->status = $this->status;
                $recruitment_candidate->notes = $this->notes;
                $recruitment_candidate->update();

                $this->provisionStaff($recruitment_candidate);
            }

            $this->dispatchBrowserEvent('hide-applicationEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Application Register Update Successfully!!"]);
        });
    }

    public function delete($id){
        $this->application_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy(){
        $application = Application::find($this->application_id);
        $recruitment_candidate = $application?->recruitment_candidate;
        $recruitment_candidate?->checks()->delete();
        $recruitment_candidate?->decisions()->delete();
        $recruitment_candidate?->qualifications()->delete();
        $recruitment_candidate?->scores()->delete();
        $recruitment_candidate?->delete();
        $application?->delete();
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',['type'=>'success','message'=>"Application Deleted Successfully!!"]);
    }

    public function render()
    {
        $search = trim($this->search);

        $applications = Application::query()
            ->with([
                'job_posting.job_title',
                'recruitment_candidate',
                'recruitment_candidate.qualifications.qualification',
                'recruitment_candidate.checks',
                'recruitment_candidate.scores',
                'recruitment_candidate.decisions',
            ])
            ->when($this->from || $this->to, function ($q) {
                $from = $this->from ? Carbon::parse($this->from)->startOfDay() : null;
                $to   = $this->to   ? Carbon::parse($this->to)->endOfDay()   : null;

                if ($from && $to) {
                    $q->whereBetween('date', [$from, $to]);
                } elseif ($from) {
                    $q->where('date', '>=', $from);
                } else {
                    $q->where('date', '<=', $to);
                }
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->whereHas('job_posting', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('surname', 'like', "%{$search}%")
                          ->orWhereRaw("CONCAT(name,' ',surname) LIKE ?", ["%{$search}%"]);
                    })
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
