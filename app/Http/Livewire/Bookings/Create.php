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
use App\Models\Notification;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use App\Mail\PendingNotificationEmails;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Create extends Component
{
    use WithFileUploads;

    public $trailers;
    public $trailer_id;
    public $type = NULL;
    public $assigned_to = NULL;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $vendors;
    public $vendor_id;
    public $mechanics;
    public $mechanic_id;
    public $employees;
    public $employee_id;


    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchEmployee;
    public $searchMechanic;
    public $searchVendor;
    
    protected $queryString = ['searchVehicle','searchVendor','searchHorse','searchTrailer','searchEmployee','searchMechanic'];

    public $drivers;
    public $driver_id;
    public $in_date;
    public $in_time;
    public $out_date;
    public $out_time;
    public $estimated_out_date;
    public $estimated_out_time;
    public $odometer;
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
    



    public function mount(){

        $department = Department::where('name','like', '%'.'Workshop'.'%')->first();
        $this->mechanics =   $department->employees;

        $this->horses = Horse::where('service', 0)
        ->orderBy('registration_number','asc')->latest()->get();

        $this->employees = Employee::orderBy('name','asc')->get();

        $this->vehicles = Vehicle::where('service', 0)
        ->orderBy('registration_number','asc')->latest()->get();

        $this->drivers = Driver::withAggregate('employee','name')
        ->orderBy('employee_name','asc')->latest()->get();

        $this->trailers = Trailer::where('service', 0)
        ->orderBy('registration_number','asc')->latest()->get();


        $this->vendors = Vendor::orderBy('name','asc')->get();

        // $value = 'Spares & Mechanics';
        // $this->vendors = Vendor::with(['vendor_type'])
        // ->whereHas('vendor_type', function($q) use($value) {
        // $q->where('name', '=', $value);
        // })->get();
        
        $this->service_types = ServiceType::all();
    
    }

    public function updatedSelectedHorse($id){
        if (!is_null($id)) {
           $horse = Horse::find($id);
           $this->mileage = $horse ? $horse->mileage : "";
           $this->hours = $horse ? $horse->hours : "";
           $assignment = Assignment::where('horse_id',$id)
                                    ->where('status', 1 )->get()->first();
           if ($assignment) {
            $this->driver_id = $assignment->driver_id;
           }
          
        }

    }
    public function updatedSelectedVehicle($id){

        if (!is_null($id)) {
           $vehicle = Vehicle::find($id);
           $this->mileage = $vehicle ? $vehicle->mileage : "";
           $this->hours = $vehicle ? $vehicle->hours : "";
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
        'estimated_out_date' => 'required',
        'estimated_out_time' => 'required',
        'station' => 'required',
        'mileage' => 'required',
        'description' => 'required',
        'service_type_id' => 'required',

    ];

    public function bookingNumber(){
       
        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $booking = Booking::orderBy('id', 'desc')->first();

        if (!$booking) {
            $booking_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $booking->id + 1;
            $booking_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $booking_number;


    }

    public function store(){
        $booking = new Booking;
        $booking->booking_number = $this->bookingNumber();
        $booking->user_id = Auth::user()->id;
        $booking->assigned_to = $this->assigned_to;

       if ($this->assigned_to == "Vendor") {
            $booking->vendor_id = $this->vendor_id ? $this->vendor_id : Null;
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
        $booking->estimated_out_date = $this->estimated_out_date;
        $booking->estimated_out_time = $this->estimated_out_time;
        $booking->station = $this->station;
        $booking->type = $this->type;
        $booking->odometer = $this->mileage;
        $booking->hours = $this->hours;
        $booking->description = $this->description;
        $booking->service_type_id = $this->service_type_id;
        $booking->status = 1;
        $booking->save();

        


        if ($this->assigned_to == "Mechanic") {
            $booking->employees()->attach($this->mechanic_id);
        }else {
            $booking->employees()->detach();
        }
 
       
        $notifications = Notification::where('category','Garage Booking Authorization')->where('status',1)->get();
        $company = Auth::user()->employee->company;
        
        if (isset($notifications)) {
            foreach ($notifications as $notification) {
                if (isset($notification->email)) {   
                    Mail::to($notification->email)->send(new PendingNotificationEmails($company, $notification, $booking));
                }elseif($notification->employee){
                    Mail::to($notification->employee->email)->send(new PendingNotificationEmails($company, $notification, $booking));
                }
               
            }
        }

        Session::flash('success','Booking Successfully Created');
        return redirect()->route('bookings.index');
    }
    public function render()
    {

        if (isset($this->searchHorse)) {
            $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->get();
        }
        if (isset($this->searchVendor)) {
            $this->vendors = Vendor::where('name', 'LIKE', "%".$this->searchVendor."%")->get();
        }
        if (isset($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
            
        }
        if (isset($this->searchTrailer)) {
            $this->trailers = Trailer::where('registration_number', 'like', '%'.$this->searchTrailer.'%')->get();
        }

        if (isset($this->searchEmployee)) {
            $this->employees = Employee::where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchEmployee."%")
            ->get();
        }
      
        if (isset($this->searchMechanic)) {
            $department = Department::where('name','like', '%'.'Workshop'.'%')->first();
            $this->mechanics =   $department->employees->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchMechanic."%");
        }
        

        return view('livewire.bookings.create');
    }
}
