<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\VehiclesMileageExport;

class Mileage extends Component
{

    public $vehicles;
    public $vehicle_id;
    public $mileage;
    public $next_service;
    public $prev_service;
    public $prev_service_date;

    public function mount(){
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
    }

    public function exportVehiclesMileageCSV(Excel $excel){

        return $excel->download(new VehiclesMileageExport, 'vehicles_mileage_hours_report.csv', Excel::CSV);
    }
    public function exportVehiclesMileagePDF(Excel $excel){

        return $excel->download(new VehiclesMileageExport, 'vehicles_mileage_hours_report.pdf', Excel::DOMPDF);
    }
    public function exportVehiclesMileageExcel(Excel $excel){
        return $excel->download(new VehiclesMileageExport, 'vehicles_mileage_hours_report.xlsx');
    }

    private function resetInputFields(){
        $this->mileage = '';
        $this->next_service = '';
        $this->prev_service = '';
        $this->prev_service_date = '';
     
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'mileage' => 'required|numeric',
        'next_service' => 'required|numeric',
        'prev_service' => 'required|numeric',
        'prev_service' => 'required',
    ];
    


    public function edit($id){
        $vehicle = Vehicle::find($id);
        $this->mileage = $vehicle->mileage;
        $this->next_service = $vehicle->next_service;
        $this->prev_service = $vehicle->prev_service;
        $this->prev_service_date = $vehicle->prev_service_date;
        $this->vehicle_id = $id;
        $this->dispatchBrowserEvent('show-mileageModal');
       
    }

    public function update(){
        $vehicle = Vehicle::find($this->vehicle_id);
        $vehicle->mileage = $this->mileage;
        $vehicle->next_service = $this->next_service;
        $vehicle->prev_service = $this->prev_service;
        $vehicle->prev_service_date = $this->prev_service_date;
        $vehicle->update();

        $this->dispatchBrowserEvent('hide-mileageModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Horse Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        return view('livewire.vehicles.mileage',[
            'vehicles' => $this->vehicles
        ]);
    }
}
