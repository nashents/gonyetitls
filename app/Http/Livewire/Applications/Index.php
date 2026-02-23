<?php

namespace App\Http\Livewire\Applications;

use App\Exports\ApplicationsExport;
use App\Models\Application;
use App\Models\Employee;
use App\Models\JobPosting;
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
    public $drivers;
    public $selectedDriver;
    public $date;
    public $time;
    public $user_id;
    public $notes;
    public $job_postings;
    public $selectedjob_posting;
    public $job_posting_id;
    public $selected_job_posting;
    public $employees;
    public $status = [];
    public $shift = [];
    public $employee_id = [];
    public $checkin = [];
    public $checkout = [];
    public $is_drivers = False;

    public function mount(){
        $this->job_postings = JobPosting::orderBy('created_at','desc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
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
        $this->time = "";
        $this->notes = "";
        $this->selectedjob_posting = Null;
        $this->selected_job_posting = Null;
        $this->is_drivers = False;
    }
    protected $rules = [
        'date' => 'required',
        'time' => 'required',
        'selectedjob_posting' => 'required',
        
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
        $application->time = $this->time;
        $application->job_posting_id = $this->selectedjob_posting;
        $application->is_drivers = $this->is_drivers;
        $application->save();

        

        $this->dispatchBrowserEvent('hide-applicationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"application Register Marked Successfully!!"
        ]);
        
        });
    }
    
    public function edit($id){
    
        $application = Application::find($id);
        $this->selectedjob_posting = $application->job_posting_id;
        $this->date = $application->date;
        $this->time = $application->time;
        $this->user_id = $application->user_id;
        $this->is_drivers = $application->is_drivers ?? False;
        $this->application_id = $application->id;
        $application_registers = $application->application_registers;
       

          $this->dispatchBrowserEvent('show-applicationEditModal');
    }

    public function update(){

        DB::transaction(function () {
        
        $this->validate();

        $application = Application::find($this->application_id);
        $application->date = $this->date;
        $application->time = $this->time;
        $application->job_posting_id = $this->selectedjob_posting;
        $application->is_drivers = $this->is_drivers;
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

        $applications = Application::query()
           
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
