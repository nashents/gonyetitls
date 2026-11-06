<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;
use App\Models\Employee;
use Illuminate\Support\Facades\Session;

class Archived extends Component
{

    public $employees;
    public $employee_id;
  
    public function mount(){
        $this->employees = Employee::where('archive','1')->visibleTo()
                                    ->orderBy('employee_number', 'desc')->get();
      }

      public function restore($id){
        $this->employee_id = $id;
        $this->dispatchBrowserEvent('show-employeeRestoreModal');
    }
    public function update(){
        $employee =  Employee::withTrashed()->find($this->employee_id);
        \App\Support\HrAccess::authorizeEmployee($employee);
        $employee->archive = 0;
        $employee->update();
        Session::flash('success','Employee Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-employeeRestoreModal');
        return redirect()->route(\App\Support\HrAccess::driversOnly() ? 'drivers.index' : 'employees.index');
    }

    public function render()
    {
        return view('livewire.employees.archived');
    }
}
