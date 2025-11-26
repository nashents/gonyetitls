<?php

namespace App\Http\Livewire\Employees;

use App\Models\Rank;
use App\Models\User;
use App\Models\Count;
use App\Models\Grade;
use App\Models\Branch;
use Livewire\Component;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Department;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\EmployeesExport;
use App\Models\EmployeePosition;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $employees;
    public $employee_id;
    public $departments;
    public $department_id;
    public $ranks;
    public $rank_id;
    public $branches;
    public $branch_id;
    public $grades;
    public $grade_id;
    public $job_titles;
    public $job_title;
    public $job_title_id;
    public $start_date;
    public $end_date;
    public $change_reason;
    public $remarks;
    public $company;

    public function exportEmployeesCSV(Excel $excel){

        return $excel->download(new EmployeesExport, 'employees.csv', Excel::CSV);
    }
    public function exportEmployeesPDF(Excel $excel){

        return $excel->download(new EmployeesExport, 'employees.pdf', Excel::DOMPDF);
    }
    public function exportEmployeesExcel(Excel $excel){
        return $excel->download(new EmployeesExport, 'employees.xlsx');
    }
  
    public function resetInputFields(){
        $this->start_date = Null;
        $this->end_date = Null;
        $this->grade_id = Null;
        $this->branch_id = Null;
        $this->department_id = Null;
        $this->rank_id = Null;
        $this->job_title_id = Null;
        $this->change_reason = Null;
        $this->remarks = Null;
    }

      public function refresh($category){

        if($category == "job_titles"){
            $this->job_titles = JobTitle::orderBy('title','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Jop Titles Refreshed Successfully!!."
            ]);
        }
        elseif($category == "grades"){
            $this->grades = Grade::orderBy('grade_name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Grades Refreshed Successfully!!."
            ]);
        }
        elseif($category == "branches"){
            $this->branches = Branch::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Branches Refreshed Successfully!!."
            ]);
        }
       
      }

    public function bulkSendCredentials(){

        $employees = Employee::whereHas('user')->with('user')->get();
       
            foreach ($employees as $employee) {
                $user = $employee->user;
                $company = $employee->company;
                if (isset($user)) {
                    if (!empty($employee->email) && filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($employee->email)->send(new AccountCreationMail($user, $company,$employee->pin));
                        $user->sent_credentials = True;
                        $user->update();
                    }
                }
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Credentials sent successfully!!"
            ]);

    }

     public function sendCredentials($id){
        $employee = Employee::find($id);
        $user = $employee->user;
        $company = $employee->company;
         if (isset($employee->email) && filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
            Mail::to($employee->email)->send(new AccountCreationMail($user, $company, $employee->pin));
            $user->sent_credentials = True;
            $user->update();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Credentials sent to ".$employee->email." successfully!!"
            ]);
        }
    }

       

    public function bulkUpdateEmployeePositions(){

        $created = 0;

        Employee::query()
        ->with('departments','ranks')
        ->where('archive', '0')
        ->doesntHave('employee_positions')
        ->select('id', 'post', 'branch_id', 'grade_id','start_date','created_at') // adjust to your columns
        ->orderBy('id')
        ->chunkById(500, function ($batch) use (&$created) {
           
            $rows = [];

            foreach ($batch as $e) {
                $rows[] = [
                    'employee_id'    => $e->id,
                    'department_id'  => $e->departments->first()?->id ?: null,
                    'job_title_id'   => JobTitle::where('title',$e->post)->first()?->id ?: null,
                    'branch_id'      => $e->branch_id ?: null,
                    'rank_id'        => $e->ranks->first()?->id ?: null,
                    'grade_id'       => $e->grade_id ?: null,
                    // include only if your table has this column:
                    'changed_by' => $e->user_id,
                    'start_date' => $e->start_date,
                    'created_at'     => $e->created_at,
                    'updated_at'     => $e->created_at,
                    'change_reason'     => "Appointment",
                    'remarks'     => "Initial Appointment",
                ];
            }

            if ($rows) {
                // Per-batch transaction keeps locks short
                DB::transaction(fn () => EmployeePosition::insert($rows));
                $created += count($rows);
            }
        });

        $company = $this->company;
        $company->positions = True;
        $company->save();

    }

    public function mount(){
        $this->resetPage();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->job_titles = JobTitle::orderBy('title','asc')->get();
        $this->grades = Grade::orderBy('grade_code','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->ranks = Rank::orderBy('name','asc')->get();

        $this->company = Auth::user()->employee ? Auth::user()->employee->company : Auth::user()->company;
       
        if($this->company->positions == False){
             $this->bulkUpdateEmployeePositions();
        }
       

      }

    public function updatingSearch()
    {
        $this->resetPage();
    }


      public function changePosition($id){
        $this->employee_id = $id;
        $employee = Employee::find($id);
        $employee_position = EmployeePosition::where('employee_id',$id)->latest()->first();
        if( $employee_position){
            $this->grade_id = $employee_position->grade_id;
            $this->department_id = $employee_position->department_id;
            $this->branch_id = $employee_position->branch_id;
            $this->job_title_id = $employee_position->job_title_id;
            $this->rank_id = $employee_position->rank_id;
        }else{
           

            $this->grade_id = $employee->grade_id;
            $this->department_id = $employee->departments->first()?->id;
            $this->branch_id = $employee->branch_id;
            $this->job_title_id = JobTitle::where('title',$employee->post)->first()?->id;
            $this->rank_id = $employee->ranks->first()?->id;
        }
        
        $this->dispatchBrowserEvent('show-changePositionModal');
       

      }
      
      public function changeUpdate(){
        
        $employee_position  = new EmployeePosition;
        $employee_position->employee_id = $this->employee_id ?? Null;
        $employee_position->job_title_id = $this->job_title_id ?? Null;
        $employee_position->rank_id = $this->rank_id ?? Null;
        $employee_position->branch_id = $this->branch_id ?? Null;
        $employee_position->department_id = $this->department_id ?? Null;
        $employee_position->grade_id = $this->grade_id ?? Null;
        $employee_position->start_date = $this->start_date ?? Null;
        $employee_position->end_date = $this->end_date ?? Null;
        $employee_position->changed_by = Auth::user()->id ?? Null;
        $employee_position->change_reason = $this->change_reason ?? Null;
        $employee_position->remarks = $this->remarks ?? Null;
        $employee_position->save();

        $post = JobTitle::find($this->job_title_id)?->title;
        $employee = Employee::find($this->employee_id);
        $employee->post = $post ?? null;
        $employee->branch_id = $this->branch_id ?? null;
        $employee->grade_id = $this->grade_id ?? null;
        $employee->update();
        $employee->ranks()->detach();
        $employee->ranks()->sync($this->rank_id);
        $employee->departments()->detach();
        $employee->departments()->sync($this->department_id);

        $this->dispatchBrowserEvent('hide-changePositionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Employee Position Changed Successfully!!"
        ]);

      }

      public function setUsernames(){

        $employees = Employee::all();

        foreach ($employees as $employee) {
            $user = $employee->user;
            if (isset($user)) {
                $use_email_as_username = $user->use_email_as_username;
                if ($use_email_as_username == TRUE) {
                    $user->username = $employee->email;
                    $user->email = $employee->email;
                    $user->update();
                   
                }elseif ($use_email_as_username == FALSE) {
                    $user->username = $employee->phonenumber;
                    $user->phonenumber = $employee->phonenumber;
                    $user->update();
                }
            }
            
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Usernames set successfully!!"
        ]);

       
    }
    
    public function render()
    {
        $search = trim((string) $this->search);

        $query = Employee::query()
            ->doesntHave('driver')
            ->where('archive', '0');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                ->orWhere('gender', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phonenumber', 'like', "%{$search}%")
                ->orWhere('post', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT_WS(' ', name, surname) LIKE ?", ["%{$search}%"])
                ->orWhereHas('ranks', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                ->orWhereHas('grade', fn ($g) => $g->where('name', 'like', "%{$search}%"))
                ->orWhereHas('branch', fn ($b) => $b->where('name', 'like', "%{$search}%"))
                ->orWhereHas('departments', fn ($d) => $d->where('name', 'like', "%{$search}%"))
                ->orWhereHas('user.roles', fn ($ur) => $ur->where('name', 'like', "%{$search}%"));
            });
        }

        $employees = $query->orderBy('name', 'asc')
                        ->orderBy('surname', 'asc')
                        ->paginate(10);

        return view('livewire.employees.index', compact('employees'));
        
    }
}
