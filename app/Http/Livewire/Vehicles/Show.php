<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Log;
use App\Models\Bill;
use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\TyreAssignment;
use App\Exports\VehicleFuelExport;
use App\Exports\VehicleBillsExport;
use App\Exports\VehicleBookingExport;
use App\Exports\VehicleTyreAssignmentExport;

class Show extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $fuel_balance;
    public $fuel_tank_capacity;
    public $mileage;
    public $hours;
    public $next_service;
    public $next_service_hours;
    public $vehicle;
    public $vehicles;
    public $vehicle_id;
    public $usages;
    public $total_usage;
    public $documents;
    public $images;
    public $fitnesses;
    public $bills;
    public $vehicle_trips;

    public function mount($id){

        $this->vehicle = Vehicle::find($id);
        $this->vehicle_id = $id;
        $this->vehicle_trips = Trip::where('vehicle_id',$id)->whereYear('created_at',date('Y'))->get()->count();
     
        if (isset($this->vehicle->trips)) {
            $this->total_usage = $this->vehicle->trips->where('trip_fuel','!=',null)->where('trip_fuel','!=',"")->sum('trip_fuel');
        }
        $this->documents = $this->vehicle->vehicle_documents;
        $this->fuel_balance = $this->vehicle->fuel_balance;
        $this->fuel_tank_capacity = $this->vehicle->fuel_tank_capacity;
        $this->mileage = $this->vehicle->mileage;
        $this->hours = $this->vehicle->hours;
        $this->next_service = $this->vehicle->next_service;
        $this->next_service_hours = $this->vehicle->next_service_hours;
        $this->images = $this->vehicle->vehicle_images;
        $this->fitnesses = $this->vehicle->fitnesses;

    }

        public function exportBookingsCSV(Excel $excel){

        return $excel->download(new VehicleBookingExport($this->vehicle_id), 'vehicle_garage_bookings.csv', Excel::CSV);
    }
    public function exportBookingsPDF(Excel $excel){

        return $excel->download(new VehicleBookingExport($this->vehicle_id), 'vehicle_garage_bookings.pdf', Excel::DOMPDF);
    }
    public function exportBookingsExcel(Excel $excel){
        return $excel->download(new VehicleBookingExport($this->vehicle_id), 'vehicle_garage_bookings.xlsx');
    }

    public function exportFuelsCSV(Excel $excel){

        return $excel->download(new VehicleFuelExport($this->vehicle_id), 'vehicle_fuel_orders.csv', Excel::CSV);
    }
    public function exportFuelsPDF(Excel $excel){

        return $excel->download(new VehicleFuelExport($this->vehicle_id), 'vehicle_fuel_orders.pdf', Excel::DOMPDF);
    }
    public function exportFuelsExcel(Excel $excel){
        return $excel->download(new VehicleFuelExport($this->vehicle_id), 'vehicle_fuel_orders.xlsx');
    }

    public function exportTyreAssignmentsCSV(Excel $excel){

        return $excel->download(new VehicleTyreAssignmentExport($this->vehicle_id), 'vehicle_assigned_tyres.csv', Excel::CSV);
    }
    public function exportTyreAssignmentsPDF(Excel $excel){

        return $excel->download(new VehicleTyreAssignmentExport($this->vehicle_id), 'vehicle_assigned_tyres.pdf', Excel::DOMPDF);
    }
    public function exportTyreAssignmentsExcel(Excel $excel){
        return $excel->download(new VehicleTyreAssignmentExport($this->vehicle_id), 'vehicle_assigned_tyres.xlsx');
    }

    public function exportBillsCSV(Excel $excel){

        return $excel->download(new VehicleBillsExport($this->vehicle_id), 'vehicle_bills.csv', Excel::CSV);
    }
    public function exportBillsPDF(Excel $excel){

        return $excel->download(new VehicleBillsExport($this->vehicle_id), 'vehicle_bills.pdf', Excel::DOMPDF);
    }
    public function exportBillsExcel(Excel $excel){
        return $excel->download(new VehicleBillsExport($this->vehicle_id), 'vehicle_bills.xlsx');
    }


    public function odometer($id){
        $this->vehicle_id = $id;
        $this->vehicle = Vehicle::find($id);
        $this->mileage = $this->vehicle->mileage;
        $this->hours = $this->vehicle->hours;
        $this->dispatchBrowserEvent('show-odometerModal');
    }
    public function nextService($id){
        $this->vehicle_id = $id;
        $this->vehicle = Vehicle::find($id);
        $this->next_service = $this->vehicle->next_service;
        $this->next_service_hours = $this->vehicle->next_service_hours;
        $this->dispatchBrowserEvent('show-nextServiceModal');
    }


    public function updateOdometer(){
        $vehicle = Vehicle::find($this->vehicle_id);
        $vehicle->mileage = $this->mileage;
        $vehicle->hours = $this->hours;
        $vehicle->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vehicle Mileage Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-odometerModal');
        return redirect(request()->header('Referer'));
    }

    public function updateNextService(){
        $vehicle = Vehicle::find($this->vehicle_id);
        $vehicle->next_service = $this->next_service;
        $vehicle->next_service_hours = $this->next_service_hours;
        $vehicle->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vehicle Next Service Mileage Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-nextServiceModal');
        return redirect(request()->header('Referer'));
    }


    public function fuelTank($id){
        $this->vehicle_id = $id;
        $this->vehicle = Vehicle::find($id);
        $this->fuel_balance = $this->vehicle->fuel_balance;
        $this->dispatchBrowserEvent('show-fuelTankModal');
    }

    public function updateFuelTank(){
        $vehicle = Vehicle::find($this->vehicle_id);
        $vehicle->fuel_balance = $this->fuel_balance;
        $vehicle->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Level Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-fuelTankModal');
        return redirect(request()->header('Referer'));
    }

    public function fuelTankCapacity($id){
        $this->vehicle_id = $id;
        $this->vehicle = Vehicle::find($id);
        $this->fuel_tank_capacity = $this->vehicle->fuel_tank_capacity;
        $this->dispatchBrowserEvent('show-fuelTankCapacityModal');
    }

    public function updateFuelTankCapacity(){
        $vehicle = Vehicle::find($this->vehicle_id);
        $vehicle->fuel_tank_capacity = $this->fuel_tank_capacity;
        $vehicle->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Tank Capacity Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-fuelTankCapacityModal');
        return redirect(request()->header('Referer'));
    }

    public function getBookingsProperty(){

        return Booking::query()->with('vehicle','employee','vendor','service_type')->where('vehicle_id',$this->vehicle_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }
    public function getFuelsProperty(){

        return Fuel::query()->with('vehicle','container','user')->where('vehicle_id',$this->vehicle_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }
    public function getTyreAssignmentsProperty(){

        return TyreAssignment::query()->with('vehicle','user')->where('vehicle_id',$this->vehicle_id)->where('status',1)->orderBy('created_at','desc')->paginate(10);
 
    }
    public function getLogsProperty(){

        return Log::query()->with('vehicle','user')->where('vehicle_id',$this->vehicle_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
 
    }

    public function getBillsProperty(){

        return Bill::query()->with('vendor','container', 'trailer', 'fuel','transporter','invoice','ticket','purchase')->where('vehicle_id',$this->vehicle_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }

    public function render()
    {
        $this->fuel_balance ;
        $this->mileage ;
        $this->hours ;

       

        return view('livewire.vehicles.show',[
            'fuel_balance' => $this->fuel_balance,
            'mileage' => $this->mileage,
            'hours' => $this->hours,
            'bookings' =>  $this->bookings,
            'fuels' =>  $this->fuels,
            'tyre_assignments' =>  $this->tyre_assignments,
            'logs' =>  $this->logs,
            'bills' =>  $this->bills
        ]);
    }
}
