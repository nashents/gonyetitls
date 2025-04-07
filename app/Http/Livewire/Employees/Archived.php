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
        $this->employees = Employee::where('archive','1')
                                    ->orderBy('employee_number', 'desc')->get();
      }

      public function restore($id){
        $this->employee_id = $id;
        $this->dispatchBrowserEvent('show-employeeRestoreModal');
    }
    public function update(){
        $employee =  Employee::withTrashed()->find($this->employee_id);
        $employee->archive = 0;
        $employee->update();
        Session::flash('success','Employee Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-employeeRestoreModal');
        return redirect()->route('employees.index');
    }

    public function render()
    {
        return view('livewire.employees.archived');
    }
}
