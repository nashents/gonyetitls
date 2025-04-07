<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\VehiclesAgeExport;

class Age extends Component
{
    public $vehicles;

    public function mount(){
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
    }

    public function exportVehiclesAgeCSV(Excel $excel){

        return $excel->download(new VehiclesAgeExport, 'vehicles.csv', Excel::CSV);
    }
    public function exportVehiclesAgePDF(Excel $excel){

        return $excel->download(new VehiclesAgeExport, 'vehicles.pdf', Excel::DOMPDF);
    }
    public function exportVehiclesAgeExcel(Excel $excel){
        return $excel->download(new VehiclesAgeExport, 'vehicles.xlsx');
    }

    public function render()
    {
        return view('livewire.vehicles.age');
    }
}
