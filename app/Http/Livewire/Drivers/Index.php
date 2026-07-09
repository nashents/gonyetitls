<?php

namespace App\Http\Livewire\Drivers;

use App\Exports\DriversExport;
use App\Exports\NewDriversExport;
use App\Imports\DriversImport;
use App\Mail\AccountCreationMail;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Grade;
use App\Models\JobTitle;
use App\Models\Rank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
    private $drivers;
    public $driver_id;
    public $employee;
    public $departments;
    public $department_id;
    public $ranks;
    public $rank_id;
    public $branches;
    public $branch_id;
    public $grades;
    public $grade_id;
    public $job_titles;
    public $job_title_id;
    public $start_date;
    public $end_date;
    public $change_reason;
    public $remarks;
    public $employee_id;
    public $selected_employee;
    public $company;
    public $importFile;
    public $send_creds = False;

    public function exportDriversCSV(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.csv', Excel::CSV);
    }
    public function exportDriversPDF(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.pdf', Excel::DOMPDF);
    }
    public function exportDriversExcel(Excel $excel){
        return $excel->download(new DriversExport, 'drivers.xlsx');
    }

    public function importDrivers(){
      
        $file = $this->importFile;
        $import = new DriversImport($this->send_creds);
        $import->import($file);

        $this->dispatchBrowserEvent('hide-importModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Drivers Imported Successfully!!"
        ]);

        return redirect(request()->header('Referer'));
    }
  
       public function resetInputFields(){
        $this->start_date = "";
        $this->end_date = "";
        $this->grade_id = "";
        $this->branch_id = "";
        $this->department_id = "";
        $this->rank_id = "";
        $this->job_title_id = "";
        $this->change_reason = "";
        $this->remarks = "";
    }

   
    
    public function mount(){
        $this->resetPage();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->job_titles = JobTitle::orderBy('title','asc')->get();
        $this->grades = Grade::orderBy('grade_code','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->ranks = Rank::orderBy('name','asc')->get();
        $this->company = Auth::user()->employee ? Auth::user()->employee->company : Auth::user()->company;
    }

    public function updatingSearch()
    {
        $this->resetPage();
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
      
     public function changePosition($id){
        $this->employee_id = $id;
        
        $this->selected_employee = Employee::find($id);
        $employee_position = EmployeePosition::where('employee_id',$id)->latest()->first();
        if( $employee_position){
            $this->grade_id = $employee_position->grade_id;
            $this->department_id = $employee_position->department_id;
            $this->branch_id = $employee_position->branch_id;
            $this->job_title_id = $employee_position->job_title_id;
            $this->rank_id = $employee_position->rank_id;
        }else{
           

            $this->grade_id = $this->selected_employee->grade_id;
            $this->department_id = $this->selected_employee->departments->first()?->id;
            $this->branch_id = $this->selected_employee->branch_id;
            $this->job_title_id = JobTitle::where('title',$this->selected_employee->post)->first()?->id;
            $this->rank_id = $this->selected_employee->ranks->first()?->id;
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
            'message'=>"Driver Position Changed Successfully!!"
        ]);

      }

    public function render()
    {
    
    $query = Driver::query()
    ->with(['user:id,active','transporter:id,name','employee:id,name,surname'])
    ->leftJoin('employees', 'employees.id', '=', 'drivers.employee_id')
    ->select('drivers.*')                  // avoid ambiguous columns
    ->where('drivers.archive', 0)          // ✅ disambiguate
    ->whereNull('drivers.deleted_at');     // ✅ disambiguate

    // 🔎 Search
    if (filled($this->search)) {
        $search = $this->search;

        $query->where(function ($q) use ($search) {
            $q->where('drivers.driver_number', 'like', "%{$search}%")
            ->orWhere('drivers.license_number', 'like', "%{$search}%")
            ->orWhere('drivers.passport_number', 'like', "%{$search}%")
            ->orWhere('drivers.experience', 'like', "%{$search}%")

            // employee columns via the join
            ->orWhereRaw("concat(employees.name, ' ', employees.surname) like ?", ["%{$search}%"])
            ->orWhere('employees.post','like', "%{$search}%")
            ->orWhere('employees.employee_number','like', "%{$search}%")
            ->orWhere('employees.email','like', "%{$search}%")
            ->orWhere('employees.phonenumber','like', "%{$search}%");
        });

    // related lookups that aren’t on employees (keep these as relationship scopes)
    $query->orWhereHas('employee.grade', fn ($g) => $g->where('name','like', "%{$search}%"))
          ->orWhereHas('employee.branch', fn ($b) => $b->where('name','like', "%{$search}%"))
          ->orWhereHas('employee.departments', fn ($d) => $d->where('name','like', "%{$search}%"))
          ->orWhereHas('employee.ranks', fn ($d) => $d->where('name','like', "%{$search}%"))
          ->orWhereHas('employee.user.roles', fn ($ur) => $ur->where('name', 'like', "%{$search}%"));
}

// 🧭 Order by employee name, surname (from the join)
    $drivers = $query->orderBy('employees.name')
                 ->orderBy('employees.surname')
                 ->paginate(10);

return view('livewire.drivers.index', compact('drivers'));
    }
}
