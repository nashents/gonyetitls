<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Driver;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\DriversAgeExport;

class Age extends Component
{
    public $drivers;

    public function mount(){
        $this->drivers = Driver::all();
    }

    public function exportDriversAgeCSV(Excel $excel){

        return $excel->download(new DriversAgeExport, 'drivers.csv', Excel::CSV);
    }
    public function exportDriversAgePDF(Excel $excel){

        return $excel->download(new DriversAgeExport, 'drivers.pdf', Excel::DOMPDF);
    }
    public function exportDriversAgeExcel(Excel $excel){
        return $excel->download(new DriversAgeExport, 'drivers.xlsx');
    }

    public function render()
    {
        return view('livewire.drivers.age');
    }
}
