<?php

namespace App\Http\Livewire\Horses;

use App\Models\Bill;
use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Horse;
use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\TyreAssignment;
use App\Exports\HorseFuelExport;
use App\Exports\HorseBillsExport;
use App\Exports\HorseBookingExport;
use App\Exports\HorseTyreAssignmentExport;

class Show extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $horse;
    public $horses;
    public $horse_id;
    public $trips;
    public $total_usage;
    public $documents;
    public $fuel_balance;
    public $fuel_tank_capacity;
    public $mileage;
    public $hours;
    public $images;
    public $fitnesses;
    public $next_service;
    public $next_service_hours;
    public $active_option;
    public $horse_trips;


    public function mount($id){
        $this->horse = Horse::find($id);
        $this->horse_id = $id;
        $this->horse_trips = Trip::where('horse_id',$id)->whereYear('created_at',date('Y'))->get()->count();
        $this->trips = $this->horse->trips;
        if (isset($this->horse->trips)) {
            $this->total_usage = $this->horse->trips->where('trip_fuel','!=',null)->where('deleted_at',Null)->where('trip_status',"Offloaded")->where('trip_fuel','!=',"")->sum('trip_fuel');
        }
      
        $this->documents = $this->horse->horse_documents;
        $this->fuel_balance = $this->horse->fuel_balance;
        $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
        $this->mileage = $this->horse->mileage;
        $this->hours = $this->horse->hours;
        $this->next_service = $this->horse->next_service;
        $this->next_service_hours = $this->horse->next_service_hours;
        $this->images = $this->horse->horse_images;
        $this->fitnesses = $this->horse->fitnesses;
        $this->active_option = "horse";
    }

    public function setActive($option){
        $this->active_option = $option;
    }

    public function exportBookingsCSV(Excel $excel){

        return $excel->download(new HorseBookingExport($this->horse_id), 'horse_garage_bookings.csv', Excel::CSV);
    }
    public function exportBookingsPDF(Excel $excel){

        return $excel->download(new HorseBookingExport($this->horse_id), 'horse_garage_bookings.pdf', Excel::DOMPDF);
    }
    public function exportBookingsExcel(Excel $excel){
        return $excel->download(new HorseBookingExport($this->horse_id), 'horse_garage_bookings.xlsx');
    }

    public function exportFuelsCSV(Excel $excel){

        return $excel->download(new HorseFuelExport($this->horse_id), 'horse_fuel_orders.csv', Excel::CSV);
    }
    public function exportFuelsPDF(Excel $excel){

        return $excel->download(new HorseFuelExport($this->horse_id), 'horse_fuel_orders.pdf', Excel::DOMPDF);
    }
    public function exportFuelsExcel(Excel $excel){
        return $excel->download(new HorseFuelExport($this->horse_id), 'horse_fuel_orders.xlsx');
    }

    public function exportTyreAssignmentsCSV(Excel $excel){

        return $excel->download(new HorseTyreAssignmentExport($this->horse_id), 'horse_assigned_tyres.csv', Excel::CSV);
    }
    public function exportTyreAssignmentsPDF(Excel $excel){

        return $excel->download(new HorseTyreAssignmentExport($this->horse_id), 'horse_assigned_tyres.pdf', Excel::DOMPDF);
    }
    public function exportTyreAssignmentsExcel(Excel $excel){
        return $excel->download(new HorseTyreAssignmentExport($this->horse_id), 'horse_assigned_tyres.xlsx');
    }

    public function exportBillsCSV(Excel $excel){

        return $excel->download(new HorseBillsExport($this->horse_id), 'horse_bills.csv', Excel::CSV);
    }
    public function exportBillsPDF(Excel $excel){

        return $excel->download(new HorseBillsExport($this->horse_id), 'horse_bills.pdf', Excel::DOMPDF);
    }
    public function exportBillsExcel(Excel $excel){
        return $excel->download(new HorseBillsExport($this->horse_id), 'horse_bills.xlsx');
    }

    public function odometer($id){
        $this->horse_id = $id;
        $this->horse = Horse::find($id);
        $this->mileage = $this->horse->mileage;
        $this->hours = $this->horse->hours;
        $this->dispatchBrowserEvent('show-odometerModal');
    }
    public function nextService($id){
        $this->horse_id = $id;
        $this->horse = Horse::find($id);
        $this->next_service = $this->horse->next_service;
        $this->next_service_hours = $this->horse->next_service_hours;
        $this->dispatchBrowserEvent('show-nextServiceModal');
    }


    public function updateOdometer(){
        $horse = Horse::find($this->horse_id);
        $horse->mileage = $this->mileage;
        $horse->hours = $this->hours;
        $horse->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Horse Mileage Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-odometerModal');
        return redirect(request()->header('Referer'));
    }

    public function updateNextService(){
        $horse = Horse::find($this->horse_id);
        $horse->next_service = $this->next_service;
        $horse->next_service_hours = $this->next_service_hours;
        $horse->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Horse Next Service Mileage Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-nextServiceModal');
        return redirect(request()->header('Referer'));
    }


    public function fuelTank($id){
        $this->horse_id = $id;
        $this->horse = Horse::find($id);
        $this->fuel_balance = $this->horse->fuel_balance;
        $this->dispatchBrowserEvent('show-fuelTankModal');
    }

    public function updateFuelTank(){
        $horse = Horse::find($this->horse_id);
        $horse->fuel_balance = $this->fuel_balance;
        $horse->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Level Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-fuelTankModal');
        return redirect(request()->header('Referer'));
    }

    public function fuelTankCapacity($id){
        $this->horse_id = $id;
        $this->horse = Horse::find($id);
        $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
        $this->dispatchBrowserEvent('show-fuelTankCapacityModal');
    }

    public function updateFuelTankCapacity(){
        $horse = Horse::find($this->horse_id);
        $horse->fuel_tank_capacity = $this->fuel_tank_capacity;
        $horse->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Tank Capacity Updated Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-fuelTankCapacityModal');
        return redirect(request()->header('Referer'));
    }

    public function getBookingsProperty(){

        return Booking::query()->with('horse','employee','vendor','service_type')->where('horse_id',$this->horse_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }
    public function getFuelsProperty(){

        return Fuel::query()->with('horse','container','user')->where('horse_id',$this->horse_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }
    public function getTyreAssignmentsProperty(){

        return TyreAssignment::query()->with('horse','user')->where('horse_id',$this->horse_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }
   
    public function getBillsProperty(){

        return Bill::query()->with('vendor','container', 'horse', 'fuel','transporter','invoice','ticket','purchase')->where('horse_id',$this->horse_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }

    public function render()
    {
        $this->fuel_balance ;
        $this->mileage ;
        $this->hours ;

        return view('livewire.horses.show',[
            'fuel_balance' => $this->fuel_balance,
            'mileage' => $this->mileage,
            'hours' => $this->hours,
            'bookings' =>  $this->bookings,
            'bills' =>  $this->bills,
            'fuels' =>  $this->fuels,
            'tyre_assignments' =>  $this->tyre_assignments
        ]);
    }
}
