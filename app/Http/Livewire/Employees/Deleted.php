<?php

namespace App\Http\Livewire\Employees;

use App\Models\User;
use App\Models\Driver;
use Livewire\Component;
use App\Models\Employee;
use Illuminate\Support\Facades\Session;

class Deleted extends Component
{
    public $employees;
    public $employee_id;

    public function mount(){
        $this->employees = Employee::onlyTrashed()->latest()->get();
    }

    public function restore($id){
        $this->employee_id = $id;
        $this->dispatchBrowserEvent('show-employeeRestoreModal');
    }
    public function update(){
        $employee =  Employee::withTrashed()->find($this->employee_id);
        $user_id = $employee->user_id;
        $driver_id = $employee->driver ? $employee->driver->id : "";
        Employee::withTrashed()->find($this->employee_id)->restore();
        User::withTrashed()->find( $user_id)->restore();
        if ($driver_id) {
            Driver::withTrashed()->find($driver_id)->restore();
        }
      
        Session::flash('success','Employee Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-employeeRestoreModal');
        return redirect()->route('employees.index');
    }
    public function render()
    {
        return view('livewire.employees.deleted');
    }
}
