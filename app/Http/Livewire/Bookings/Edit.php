<?php

namespace App\Http\Livewire\Bookings;

use App\Models\Hour;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Vendor;
use App\Models\Booking;
use App\Models\JobType;
use App\Models\Mileage;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Breakdown;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\ServiceType;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class Edit extends Component
{
    use WithFileUploads;

    public $trailers;
    public $selectedTrailer;
    public $type;
    public $assigned_to;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $assets;
    public $selectedAsset;
    public $mechanics;
    public $mechanic_id;
    public $vendors;
    public $vendor_id;

    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchEmployee;
    public $searchMechanic;
    public $searchVendor;
    public $searchAsset;

    public $breakdowns;
    public $breakdown_id;
    
    protected $queryString = ['searchVendor','searchAsset','searchVehicle','searchHorse','searchTrailer','searchEmployee','searchMechanic'];

    public $company;
    public $employees;
    public $employee_id;
    public $in_date;
    public $in_time;
    public $estimated_out_date;
    public $estimated_out_time;
    public $out_date;
    public $odometer;
    public $out_time;
    public $station;
    public $mileage;
    public $hours;
    public $service_types;
    public $service_type_id;
    public $booking_number;
    public $description;
   

    public $inputs = [];
    public $i = 1;
    public $n = 1;
    
    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    
    public function remove($i)
    {
        unset($this->inputs[$i]);
    }



    public function mount($id){
        $booking = Booking::find($id);
        $this->company = Auth::user()->employee->company;
        $this->booking_number = $booking->booking_number;
        $this->selectedHorse = $booking->horse_id;
        $this->selectedVehicle = $booking->vehicle_id;
        $this->vendor_id = $booking->vendor_id;
        $this->selectedTrailer = $booking->trailer_id;
        $this->selectedAsset = $booking->asset_id;
        $this->employee_id = $booking->employee_id;
        $this->service_type_id = $booking->service_type_id;
        $this->station = $booking->station;
        $this->mileage = $booking->odometer;
        $this->breakdown_id = $booking->breakdown_id;
        $this->hours = $booking->hours;
        $this->description = $booking->description;

        foreach ($booking->employees as $key => $mechanic) {
            $this->mechanic_id[] = $mechanic->id;
            $this->i = $key;
            array_push($this->inputs ,$key);
        }
       
        $this->in_time = $booking->in_time;
        $this->in_date = $booking->in_date;
        $this->out_date = $booking->out_date;
        $this->out_time = $booking->out_time;
        $this->estimated_out_date = $booking->estimated_out_date;
        $this->estimated_out_time = $booking->estimated_out_time;
        $this->booking_id = $booking->id;
        $this->type = $booking->type;
        $this->assigned_to = $booking->assigned_to;
        $this->service_types = ServiceType::orderBy('name','asc')->get();
       
         $employee = Employee::find($this->employee_id );
            $driver = $employee->driver;
             if($driver){
                if($this->type == "Horse" && $this->selectedHorse){
                    $this->breakdowns = Breakdown::where('driver_id',$driver->id)->where('horse_id', $this->selectedHorse)->whereYear('date',date('Y'))->where('status',True)->orderBy('created_at','desc')->get();
                }elseif($this->type == "Vehicle" && $this->selectedVehicle){
                    $this->breakdowns = Breakdown::where('driver_id',$driver->id)->where('vehicle_id', $this->selectedVehicle)->whereYear('date',date('Y'))->where('status',True)->orderBy('created_at','desc')->get();
                }elseif($this->type == "Trailer" && $this->selectedTrailer){
                    $this->breakdowns = Breakdown::where('driver_id',$driver->id)->where('trailer_id', $this->selectedTrailer)->whereYear('date',date('Y'))->where('status',True)->orderBy('created_at','desc')->get();
                }
                
            }
    }

    public function updatedSelectedHorse($horse){

        if (!is_null($horse)) {
           $horse = Horse::find($horse);
           $this->mileage = $horse->mileage;
           $this->hours = $horse->hours;  
        }

    }

      public function updatedSelectedTrailer($id){
        if (!is_null($id)) {
           $trailer = Trailer::find($id);
           $this->mileage = $trailer ? $trailer->mileage : "";
          
        }

    }
    public function updatedSelectedVehicle($vehicle){

        if (!is_null($vehicle)) {
           $vehicle = Vehicle::find($vehicle);
           $this->mileage = $vehicle->mileage;
           $this->hours = $vehicle->hours;
        }

    }

    public function updated($value){
        $this->validateOnly($value);
    }
       protected $messages =[
      'employee_id.required' => 'Select Employee',
      'service_type_id.required' => 'Select Service Type',
  ];
    protected $rules = [
        'booking_number' => 'required',
        'in_time' => 'required',
        'in_date' => 'required',
        'station' => 'required',
        'mileage' => 'required',
        'description' => 'required',
        'service_type_id' => 'required',

    ];


    public function updatedEmployeeId($id){
        if(!is_null($id)){
            $employee = Employee::find($id);
            $driver = $employee->driver;
             if($driver){
                if($this->type == "Horse" && $this->selectedHorse){
                    $this->breakdowns = Breakdown::where('driver_id',$driver->id)->where('horse_id', $this->selectedHorse)->whereYear('date',date('Y'))->where('status',True)->orderBy('created_at','desc')->get();
                }elseif($this->type == "Vehicle" && $this->selectedVehicle){
                    $this->breakdowns = Breakdown::where('driver_id',$driver->id)->where('vehicle_id', $this->selectedVehicle)->whereYear('date',date('Y'))->where('status',True)->orderBy('created_at','desc')->get();
                }elseif($this->type == "Trailer" && $this->selectedTrailer){
                    $this->breakdowns = Breakdown::where('driver_id',$driver->id)->where('trailer_id', $this->selectedTrailer)->whereYear('date',date('Y'))->where('status',True)->orderBy('created_at','desc')->get();
                }
                
            }
        }
        
    }

    public function updatedBreakdownId($id){
        if(!is_null($id)){
            $breakdown = Breakdown::find($id);
            $this->description = $breakdown->description;
        }
    }


    public function update(){

        DB::transaction(function () {

        $booking = Booking::find($this->booking_id);
        
        $booking->vendor_id = $this->assigned_to === "Vendor" ? ($this->vendor_id ?: null) : null;
        $booking->breakdown_id = $this->breakdown_id;
    // Reset all IDs
        $booking->horse_id = null;
        $booking->vehicle_id = null;
        $booking->trailer_id = null;
        $booking->asset_id = null;
        $booking->odometer = null;
        $booking->hours = null;
       

        switch ($this->type) {
            case "Horse":
                $booking->odometer = $this->mileage;
                $booking->hours = $this->hours;
                $booking->horse_id = $this->selectedHorse ?: null;
                break;
            case "Trailer":
                $booking->odometer = $this->mileage;
                $booking->trailer_id = $this->selectedTrailer ?: null;
                break;
            case "Vehicle":
                $booking->odometer = $this->mileage;
                $booking->hours = $this->hours;
                $booking->vehicle_id = $this->selectedVehicle ?: null;
                break;
            case "Asset":
                $booking->asset_id = $this->selectedAsset ?: null;
                break;
        }

        $booking->employee_id = $this->employee_id ? $this->employee_id : Null;

        $booking->in_date = $this->in_date;
        $booking->in_time = $this->in_time;
        $booking->station = $this->station;
        $booking->description = $this->description;
        $booking->estimated_out_date = $this->estimated_out_date;
        $booking->type = $this->type;

        $booking->assigned_to = $this->assigned_to;
        $booking->estimated_out_time = $this->estimated_out_time;
        $booking->service_type_id = $this->service_type_id;
        $booking->status = 1;

        $booking->update();

        if ($this->assigned_to == "Mechanic") {
            $booking->employees()->detach();
            $booking->employees()->sync($this->mechanic_id);
        }else {
            $booking->employees()->detach();
        }
      
     
        $mileage =  Mileage::where('booking_id', $booking->id)->first();

        if ($mileage) {
            $mileage->booking_id = $booking->id;
            $mileage->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
            $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
            $mileage->trailer_id = $this->selectedTrailer ? $this->selectedTrailer : Null;
            $mileage->mileage = $this->mileage;
            $mileage->date = $this->in_date;
            $mileage->category = "Booking";
            $mileage->update();
        }

        $hours =  Hour::where('booking_id',$booking->id)->first();

        if ($hours) {
            $hours->booking_id = $booking->id;
            $hours->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
            $hours->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
            $hours->trailer_id = $this->selectedTrailer ? $this->selectedTrailer : Null;
            $hours->hours = $this->hours;
            $hours->date = $this->in_date;
            $hours->category = "Booking";
            $hours->update();
        }
       

        Session::flash('success','Booking Updated Successfully');
        return redirect()->route('bookings.index');

    });

    }

    public function render()
    {

          if (filled($this->searchHorse)) {
            $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->get();
        }else{
            $this->horses = Horse::with('horse_make:id,name','horse_model:id,name')->orderBy('registration_number','asc')->get();
        }

        if (filled($this->searchVendor)) {
            $this->vendors = Vendor::where('status',1)->where('name', 'LIKE', "%".$this->searchVendor."%")->get();
        }else{
            $this->vendors = Vendor::where('status',1)->orderBy('name','asc')->get();
        }

        if (filled($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
        }else{
              $this->vehicles = Vehicle::with('vehicle_make:id,name','vehicle_model:id,name')->orderBy('registration_number','asc')->get();
        }

        if (filled($this->searchAsset)) {
            $this->assets = Asset::query()->with('product:id,name','product.brand')->where('disposed', 0)->where('status', 1)
            ->where('serial_number', 'like', '%'.$this->searchAsset.'%')
            ->orWhereHas('product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->searchAsset.'%');
            })
             ->orWhereHas('product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->searchAsset.'%');
            })
            ->get();
            
        }else{
            $this->assets = Asset::with('product')->where('disposed', 0)->where('status', 1)->get()->sortBy('product.name');
        }

        if (filled($this->searchTrailer)) {
            $this->trailers = Trailer::where('registration_number', 'like', '%'.$this->searchTrailer.'%')->get();
        }else{
            $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        }

        if (filled($this->searchEmployee)) {
            $this->employees = Employee::where('archive',0)->where('status',1)->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchEmployee."%")
            ->get();
        }else{
            $this->employees = Employee::where('archive',0)->where('status',1)->orderBy('name')->orderBy('surname')->get();
        }
      
        if (filled($this->searchMechanic)) {
        $department = Department::where('name', 'like', '%Workshop%')->first();

            $this->mechanics = $department->employees()
                ->where(DB::raw("CONCAT(name, ' ', surname)"), 'like', '%' . $this->searchMechanic . '%')
                ->get();
        } else {
            $department = Department::where('name', 'like', '%Workshop%')->first();

            $this->mechanics = $department->employees()->get();
        }
        
        return view('livewire.bookings.edit');
    }
}
