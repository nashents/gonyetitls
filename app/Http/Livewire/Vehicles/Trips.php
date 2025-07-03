<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\VehicleTripExport;
use Illuminate\Support\Facades\Auth;

class Trips extends Component
{
    public $trips;
    public $vehicle;
    public $vehicle_id;
    public $company;
    public $user;
    public $employee;
    public $role_names = [];
    public $department_names = [];
    public $rank_names = [];

    public function mount($id){
        $this->vehicle_id = $id;
        $this->vehicle = Vehicle::find($id);
        $this->trips = $this->vehicle->trips;
         $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;
         foreach($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
    
        foreach($this->user->roles as $role) {
            $this->role_names[] = $role->name;
        }
    
        foreach($this->employee->ranks as $rank) {
            $this->rank_names[] = $rank->name;
        }
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
