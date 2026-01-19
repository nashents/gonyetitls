<?php

namespace App\Http\Livewire\Employees;

use App\Models\Leave;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Recovery;
use App\Models\Allowance;
use App\Models\Department;
use Livewire\WithPagination;
use App\Models\AllowanceDriver;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\Mail;

class Show extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
  
    public $employee_id;
    public $employee;
    public $company;
    public $user;
    public $driver;
    private $driver_allowances;
    private $recoveries;
    public $all_departments;
    public $employee_departments;
    public $department_id;
    public $trips;
    public $leaves;
    public $cashflows;
    public $use_email_as_username;
    public $driver_allowance;
    public $pattern;
    public $department;
    public $selected_department;


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

    public function mount($id){
        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        $this->all_departments = Department::orderBy('name','asc')->get();
        $this->employee = Employee::with('leaves','documents','dependants','departments','driver')->find($id);
        $this->user = $this->employee->user;
        $this->company = $this->employee->company;
        $this->employee_id = $id;
        $this->use_email_as_username =  $this->employee->user->use_email_as_username;
        $this->driver = $this->employee->driver;
        $this->leaves =  $this->employee->leaves;
        if(isset($this->driver)){
            $this->trips = $this->driver->trips;
            $this->cashflows = $this->driver->cash_flows;
        }

        $this->employee_id = $this->employee->id;
     
     
    }

    public function setUsername(){
        if ($this->use_email_as_username == TRUE) {

            $user = $this->employee->user;
            $user->username = $this->employee->email;
            $user->email = $this->employee->email;
            $user->update();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Email set as username successfully!!"
            ]);
        }elseif ($this->use_email_as_username == FALSE) {
            $user = $this->employee->user;
            $user->username = $this->employee->phonenumber;
            $user->phonenumber = $this->employee->phonenumber;
            $user->update();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Phonenumber set as username successfully!!"
            ]);
        }
    }



    public function sendCredentials(){
         if (isset($this->employee->email) && filter_var($this->employee->email, FILTER_VALIDATE_EMAIL)) {
            Mail::to($this->employee->email)->send(new AccountCreationMail($this->user, $this->company,$this->employee->pin));
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Credentials sent to ".$this->employee->email." successfully!!"
            ]);
        }
    }

 


    public function showRemove($id){
        $this->department_id = $id;
        $this->selected_department = Department::find($id);
        $this->dispatchBrowserEvent('show-removeDepartmentModal');
    }

    public function removeDepartment()
    {
        if (! filled($this->department_id)) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message' => 'No department selected.'
            ]);
            return;
        }

        $this->employee->departments()->detach($this->department_id);

        // Clear cached relationship and reload cleanly
        $this->employee->unsetRelation('departments');
       

        $this->dispatchBrowserEvent('hide-removeDepartmentModal');

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Department Removed Successfully!!'
        ]);
    }


    public function addDepartments()
    {
        // $this->department_id is an array like [0 => 8, 1 => 3, ...]
        $ids = collect($this->department_id ?? [])
            ->filter(fn ($v) => filled($v))     // remove null/empty
            ->map(fn ($v) => (int) $v)          // cast to int
            ->filter(fn ($v) => $v > 0)         // keep valid
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message' => 'Please select at least one department.'
            ]);
            return;
        }

        // avoids duplicates in pivot
        $this->employee->departments()->syncWithoutDetaching($ids);

        // refresh for UI
        $this->employee->unsetRelation('departments');
        $this->employee_departments = $this->employee->departments()->get();

        $this->dispatchBrowserEvent('hide-departmentModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Department(s) Added Successfully!!'
        ]);
    }




    public function render()
    {

        $this->all_departments = Department::orderBy('name','asc')->get();
        $this->employee = Employee::find($this->employee_id);
        $this->employee_departments = $this->employee->departments;
        if (isset($this->driver)) {
            return view('livewire.employees.show',[
                'all_departments' => $this->all_departments,
                'employee_departments' =>  $this->employee->departments()->get(),
                'driver_allowances' => AllowanceDriver::where('driver_id', $this->driver->id)->orderBy('created_at','desc')->paginate(10),
                'recoveries' => Recovery::where('driver_id', $this->driver->id)->orderBy('created_at','desc')->paginate(10)
            ]);
        }else{
            return view('livewire.employees.show',[
                'all_departments' => $this->all_departments,
                'employee_departments' =>  $this->employee->departments()->get(),
            ]);
        }
       
    }
}
