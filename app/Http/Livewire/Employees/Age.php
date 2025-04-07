<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;
use App\Models\Employee;
use Maatwebsite\Excel\Excel;
use App\Exports\EmployeesAgeExport;

class Age extends Component
{
    public $employees;

    public function mount(){
        $this->employees = Employee::orderBy('name','asc')->get();
    }

    public function exportEmployeesAgeCSV(Excel $excel){

        return $excel->download(new EmployeesAgeExport, 'employees.csv', Excel::CSV);
    }
    public function exportEmployeesAgePDF(Excel $excel){

        return $excel->download(new EmployeesAgeExport, 'employees.pdf', Excel::DOMPDF);
    }
    public function exportEmployeesAgeExcel(Excel $excel){
        return $excel->download(new EmployeesAgeExport, 'employees.xlsx');
    }

    public function render()
    {
        return view('livewire.employees.age');
    }
}
