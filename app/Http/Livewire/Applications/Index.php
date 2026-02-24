<?php

namespace App\Http\Livewire\Applications;

use App\Exports\ApplicationsExport;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\RecruitmentCandidate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
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
  

    public function mount(){
        $this->job_postings = JobPosting::orderBy('created_at','desc')->get();
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
