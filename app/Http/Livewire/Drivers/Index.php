<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Rank;
use App\Models\Grade;
use App\Models\Branch;
use App\Models\Driver;
// use App\Exports\DriversExport;
use Livewire\Component;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Department;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\DriversExport;
use App\Models\EmployeePosition;
use App\Exports\NewDriversExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
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

    public function exportDriversCSV(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.csv', Excel::CSV);
    }
    public function exportDriversPDF(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.pdf', Excel::DOMPDF);
    }
    public function exportDriversExcel(Excel $excel){
        return $excel->download(new DriversExport, 'drivers.xlsx');
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
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

     public function changePosition($id){
        $this->driver_id = $id;
        $this->driver = Driver::find($id);
        $this->employee = $this->driver->employee;
        $this->employee_id = $this->driver->employee ?->id;

        $this->dispatchBrowserEvent('show-changePositionModal');
       

      }
      
      public function updatePosition(){
        $this->employee_id = $id;
        $employee_position  = new EmployeePosition;
        $employee_position->employee_id = $this->employee_id;
        $employee_position->job_title_id = $this->job_title_id;
        $employee_position->grade_id = $this->grade_id;
        $employee_position->rank_id = $this->rank_id;
        $employee_position->branch_id = $this->branch_id;
        $employee_position->department_id = $this->department_id;
        $employee_position->start_date = $this->start_date;
        $employee_position->end_date = $this->end_date;
        $employee_position->changed_by = Auth::user()->id;
        $employee_position->change_reason = $this->change_reason;
        $employee_position->remarks = $this->remarks;
        $employee_position->save();
    
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
        ->withAggregate('employee', 'name as employee_name')
        ->withAggregate('employee', 'surname as employee_surname')
        ->where('archive', '0');

        // 🔎 Search
        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('driver_number', 'like', "%{$search}%")
                ->orWhere('license_number', 'like', "%{$search}%")
                ->orWhere('passport_number', 'like', "%{$search}%")
                ->orWhere('experience', 'like', "%{$search}%")
                ->orWhereHas('employee', function ($e) use ($search) {
                    $e->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%")
                        ->orWhere('post','like', "%{$search}%")
                        ->orWhere('email','like', "%{$search}%")
                        ->orWhere('phonenumber','like', "%{$search}%")
                        ->orWhereHas('grade', fn ($g) => $g->where('name','like', "%{$search}%"))
                        ->orWhereHas('branch', fn ($b) => $b->where('name','like', "%{$search}%"))
                        ->orWhereHas('departments', fn ($d) => $d->where('name','like', "%{$search}%"));
                });
            });
        }

        // 🧭 Order by related employee name, surname
        $drivers = $query->orderBy('employee_name', 'asc')
                        ->orderBy('employee_surname', 'asc')
                        ->paginate(10);

        return view('livewire.drivers.index', compact('drivers'));
    }
}
