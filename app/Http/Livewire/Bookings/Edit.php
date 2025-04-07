<?php

namespace App\Http\Livewire\Bookings;

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
use App\Models\Assignment;
use App\Models\Department;
use App\Models\ServiceType;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class Edit extends Component
{
    use WithFileUploads;

    public $trailers;
    public $trailer_id;
    public $type;
    public $assigned_to;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
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
    
    protected $queryString = ['searchVendor','searchVehicle','searchHorse','searchTrailer','searchEmployee','searchMechanic'];

    public $employees;
    public $employee_id;
    public $drivers;
    public $driver_id;
    public $in_date;
    public $in_time;
    public $estimated_out_date;
    public $estimated_out_time;
    public $out_date;
    public $odometer;
    public $out_time;
    public $station;
    public $mileage;
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
        $this->booking_number = $booking->booking_number;
        $this->selectedHorse = $booking->horse_id;
        $this->selectedVehicle = $booking->vehicle_id;
        $this->vendor_id = $booking->vendor_id;
        $this->trailer_id = $booking->trailer_id;
        $this->employee_id = $booking->employee_id;
        $this->service_type_id = $booking->service_type_id;
        $this->station = $booking->station;
        $this->mileage = $booking->odometer;
        $this->description = $booking->description;

        foreach ($booking->employees as $mechanic) {
            $this->mechanic_id[] = $mechanic->id;
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

        $department = Department::where('name','like', '%'.'Workshop'.'%')->first();
        $this->mechanics =   $department->employees;

        $this->vendors = Vendor::orderBy('name','asc')->get();

        $this->horses = Horse::where('service', 0)
        ->orderBy('registration_number','asc')->latest()->get();

        $this->employees = Employee::orderBy('name','asc')->get();

        $this->vehicles = Vehicle::where('service', 0)
        ->orderBy('registration_number','asc')->latest()->get();

        $this->drivers = Driver::withAggregate('employee','name')
        ->orderBy('employee_name','asc')->latest()->get();

        $this->trailers = Trailer::where('service', 0)
        ->orderBy('registration_number','asc')->latest()->get();


        $this->service_types = ServiceType::all();
    }

    public function updatedSelectedHorse($horse){

        if (!is_null($horse)) {
           $horse = Horse::find($horse);
           $this->mileage = $horse->mileage;
           $assignment = Assignment::where('horse_id',$horse)
                                    ->where('status', 1 )->get()->first();
           if ($assignment) {
            $this->driver_id = $assignment->driver_id;
           }
          
        }

    }
    public function updatedSelectedVehicle($vehicle){

        if (!is_null($vehicle)) {
           $vehicle = Vehicle::find($vehicle);
           $this->mileage = $vehicle->mileage;
        }

    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[
      'selectedVehicle.required' => 'Select Vehicle',
      'employee_id.required' => 'Select Employee',
      'driver_id.required' => 'Select Driver',
      'selectedHorse.required' => 'Select Horse',

  ];
    protected $rules = [
        'booking_number' => 'required',
        'in_time' => 'required',
        'in_date' => 'required',
        'estimated_out_time' => 'required',
        'estimated_out_date' => 'required',
        'station' => 'required',
        'mileage' => 'required',
        'description' => 'required',
        'service_type_id' => 'required',

    ];




    public function update(){

        $booking = Booking::find($this->booking_id);
        
        if ($this->assigned_to == "Vendor") {
            $booking->vendor_id = $this->vendor_id ? $this->vendor_id : Null ;
        }else {
            $booking->vendor_id = Null;
        }

        if ($this->type == "Horse") {
            $booking->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
            $booking->vehicle_id = Null;
            $booking->trailer_id = Null;
        }elseif ($this->type == "Trailer") {
            $booking->horse_id = Null;
            $booking->vehicle_id = Null;
            $booking->trailer_id = $this->trailer_id ? $this->trailer_id : Null;
        }elseif ($this->type == "Vehicle") {
            $booking->horse_id = Null;
            $booking->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
            $booking->trailer_id = Null;
        }

        $booking->employee_id = $this->employee_id ? $this->employee_id : Null;
        $booking->in_date = $this->in_date;
        $booking->in_time = $this->in_time;
        $booking->station = $this->station;
        $booking->odometer = $this->mileage;
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
      
        $mileage =  Mileage::where('booking_id',$booking->id);
        if (isset($mileage)) {
            $mileage->booking_id = $booking->id;
            $mileage->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
            $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
            $mileage->trailer_id = $this->trailer_id ? $this->trailer_id : Null;
            $mileage->mileage = $this->mileage;
            $mileage->date = $this->in_date;
            $mileage->category = "Booking";
            $mileage->update();
        }
       

        Session::flash('success','Booking Updated Successfully');
        return redirect()->route('bookings.index');

    }

    public function render()
    {
        return view('livewire.bookings.edit');
    }
}
