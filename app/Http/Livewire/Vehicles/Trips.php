<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\VehicleTripExport;

class Trips extends Component
{
    public $trips;
    public $vehicle;
    public $vehicle_id;

    public function mount($id){
        $this->vehicle_id = $id;
        $this->vehicle = Vehicle::find($id);
        $this->trips = $this->vehicle->trips;
    }


    public function exportTripsCSV(Excel $excel){

        return $excel->download(new VehicleTripExport($this->vehicle_id), 'vehicle_trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new VehicleTripExport($this->vehicle_id), 'vehicle_trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new VehicleTripExport($this->vehicle_id), 'vehicle_trips.xlsx');
    }

    public function render()
    {
        return view('livewire.vehicles.trips');
    }
}
