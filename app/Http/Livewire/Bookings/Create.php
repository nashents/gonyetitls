<?php

namespace App\Http\Livewire\Bookings;

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
    public $selectedTrailer;
    public $type = "Horse";
    public $assigned_to = "Mechanic";
    public $horses;
    public $selectedAsset;
    public $assets;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $vendors;
    public $vendor_id;
    public $mechanics;
    public $mechanic_id;
    public $employees;
    public $employee_id;
    
    public $breakdowns;
    public $breakdown_id;


    public $searchAsset;
    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchEmployee;
    public $searchMechanic;
    public $searchVendor;
    
    protected $queryString = ['searchVehicle','searchAsset','searchVendor','searchHorse','searchTrailer','searchEmployee','searchMechanic'];


    public $in_date;
    public $company;
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
        $this->company = Auth::user()->employee->company;
        $this->service_types = ServiceType::orderBy('name','asc')->get();
        $this->breakdowns = collect();
    
    }

    public function updatedSelectedHorse($id){
        if (!is_null($id)) {
           $horse = Horse::find($id);
           $this->mileage = $horse ? $horse->mileage : "";
           $this->hours = $horse ? $horse->hours : "";
        }

    }
    public function updatedSelectedTrailer($id){
        if (!is_null($id)) {
           $trailer = Trailer::find($id);
           $this->mileage = $trailer ? $trailer->mileage : "";
          
        }

    }
    public function updatedSelectedVehicle($id){
        if (!is_null($id)) {
           $vehicle = Vehicle::find($id);
           $this->mileage = $vehicle ? $vehicle->mileage : "";
           $this->hours = $vehicle ? $vehicle->hours : "";
        }
    }

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

        DB::transaction(function () {

        $booking = new Booking;
        $booking->booking_number = $this->bookingNumber();
        $booking->user_id = Auth::user()->id;
        $booking->assigned_to = $this->assigned_to;
        $booking->breakdown_id = $this->breakdown_id;

        $booking->vendor_id = $this->assigned_to === "Vendor" ? ($this->vendor_id ?: null) : null;

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
        $booking->estimated_out_date = $this->estimated_out_date;
        $booking->estimated_out_time = $this->estimated_out_time;
        $booking->station = $this->station;
        $booking->type = $this->type;
        
        $booking->description = $this->description;
        $booking->service_type_id = $this->service_type_id;
        $booking->status = 1;
        $booking->save();

        if ($this->assigned_to == "Mechanic") {
            $booking->employees()->attach($this->mechanic_id);
        }else {
            $booking->employees()->detach();
        }
 
       
        $notifications = Notification::where('when','before')->where('category','Garage Booking Authorization')->where('status',1)->get();
        $company =  $this->company;
        
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                   $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($company, $notification, $booking));
                }
                }
            }
        }

        Session::flash('success','Booking Successfully Created');
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

        return view('livewire.bookings.create');
    }
}
