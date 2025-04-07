<?php

namespace App\Http\Livewire\Employees;

use App\Models\User;
use App\Models\Count;
use App\Models\Branch;
use Livewire\Component;
use App\Models\Employee;
use Maatwebsite\Excel\Excel;
use App\Exports\EmployeesExport;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $employees;
    public $employee_id;

    public function exportEmployeesCSV(Excel $excel){

        return $excel->download(new EmployeesExport, 'employees.csv', Excel::CSV);
    }
    public function exportEmployeesPDF(Excel $excel){

        return $excel->download(new EmployeesExport, 'employees.pdf', Excel::DOMPDF);
    }
    public function exportEmployeesExcel(Excel $excel){
        return $excel->download(new EmployeesExport, 'employees.xlsx');
    }
  
    public function mount(){
        $this->employees = Employee::doesntHave('driver')->where('archive','0')
                                    ->orderBy('employee_number', 'desc')->get();
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
        return view('livewire.employees.index');
    }
}
