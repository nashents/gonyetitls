<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Driver;
use Livewire\Component;
use App\Models\Employee;
use Maatwebsite\Excel\Excel;
// use App\Exports\DriversExport;
use App\Exports\DriversExport;
use App\Exports\NewDriversExport;

class Index extends Component
{
    public $drivers;
    public $employee;

    public function exportDriversCSV(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.csv', Excel::CSV);
    }
    public function exportDriversPDF(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.pdf', Excel::DOMPDF);
    }
    public function exportDriversExcel(Excel $excel){
        return $excel->download(new DriversExport, 'drivers.xlsx');
    }
  
    
    public function mount(){
        $this->employees = Employee::where('archive','0')
                                    ->orderBy('employee_number', 'desc')->get();
        $this->drivers = Driver::with('user:id,active','transporter:id,name','employee:id,name,surname')->where('archive','0')
                                    ->orderBy('driver_number', 'desc')->get();
      }
    public function render()
    {
        return view('livewire.drivers.index');
    }
}
