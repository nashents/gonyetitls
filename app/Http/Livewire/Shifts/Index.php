<?php

namespace App\Http\Livewire\Shifts;

use Carbon\Carbon;
use App\Models\Fuel;
use App\Models\Hour;
use App\Models\Team;
use App\Models\Trip;
use App\Models\User;
use App\Models\Work;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Shift;
use App\Models\TopUp;
use App\Models\Driver;
use App\Models\Account;
use App\Models\Cluster;
use App\Models\Company;
use App\Models\Mileage;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Location;
use App\Models\TripType;
use App\Models\Container;
use App\Models\Rehandling;
use App\Models\Destination;
use App\Models\Measurement;
use App\Models\Transporter;
use App\Models\DeliveryNote;
use App\Models\ExchangeRate;
use App\Models\LoadingPoint;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\ShiftsExport;
use App\Imports\ShiftsImport;
use Livewire\WithFileUploads;
use App\Models\OffloadingPoint;
use App\Imports\ShiftTripsImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;

class Index extends Component
{

    use WithPagination;
    use WithFileUploads;


    protected $paginationTheme = 'bootstrap';
    public $search;
    public $shift_filter;
    protected $queryString = ['search','searchVehicle','searchHorse','searchDriver'];
    public $from;
    public $to;
    private $shifts;
    public $shift_id;
    public $status;
    public $shift_start_time;
    public $shift_end_time;
    public $depart_workshop_time;
    public $arrive_location_time;
    public $depart_location_time;
    public $arrive_workshop_time;
    public $selectedStatus = "Offloaded";
    public $type;
    public $shift_type;
    public $for;
    public $date;
    public $user_id;
    public $employee_user_id;
    public $containers;
    public $selectedContainer;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $customers;
    public $customer_id;

    public $selected_trip;
    public $selected_rehandling;

    //Shifts Filters

    public $filter_offloading_point_id;
    public $filter_loading_point_id;
    public $filter_to_destination;
    public $filter_from_destination;
    public $filter_user_id;
    public $filter_cargo_id;
    public $filter_customer_id;
    public $filter_haulage_type;
    public $filter_driver_id;
    public $filter_vehicle_id;
    public $filter_horse_id;
    public $filter_transporter_id;
    public $filter_shift_type;
    public $shift_open_mileage;
    public $shift_open_hours;
    public $shift_close_hours;
    public $shift_close_mileage;

    public $selectedLoadingPoint;
    public $selectedOffloadingPoint;
    public $selectedDriver;
    public $employees;
    public $selectedEmployee;
    public $selectedCustomer;
    public $measurements;

    public $destinations;

    // trip vars

    public $haulage_type = [];
    public $selectedFrom = [];
    public $selectedTo = [];
    public $arrive_lp = [];
    public $arrive_op = [];
    public $depart_lp = [];
    public $depart_op = [];
    public $offloading_point_id = [];
    public $loading_point_id = [];
    public $weight = [];
    public $freight  = [];
    public $trip_ref = [];
    public $rate = [];
    public $starting_mileage = [];
    public $ending_mileage = [];
    public $starting_hours = [];
    public $ending_hours = [];
    public $litreage_at_20 = [];
    public $litreage = [];
    public $quantity = [];
    public $measurement = [];
    public $selectedCargo = [];

    // current trip vars

    public $current_selectedCargo = [];
    public $current_haulage_type = [];
    public $current_selectedFrom = [];
    public $current_selectedTo = [];
    public $current_arrive_lp = [];
    public $current_arrive_op = [];
    public $current_depart_lp = [];
    public $current_depart_op = [];
    public $current_offloading_point_id = [];
    public $current_loading_point_id = [];
    public $current_cargo_type = [];
    public $current_weight = [];
    public $current_freight  = [];
    public $current_trip_ref = [];
    public $current_rate = [];
    public $current_starting_mileage = [];
    public $current_ending_mileage = [];
    public $current_starting_hours = [];
    public $current_ending_hours = [];
    public $current_litreage_at_20 = [];
    public $current_litreage = [];
    public $current_quantity = [];
    public $current_measurement = [];

    public $fuel_order = False;
    public $transporters;
    public $selectedTransporter;
     // vehicles vars
    public $all_vehicles = False;
    public $vehicles;
    public $searchVehicle;
    public $selectedVehicle;
     // horses vars
    public $all_horses = False;
    public $horses;
    public $selectedHorse;
    public $searchHorse;
    // driver vars
    public $all_drivers = False;
    public $searchDriver;
    public $drivers;
    public $driver_id;
    public $equipment;
    public $trailer_id;
    public $accounts;
    public $account_id;

    public $teams;
    public $team_id;
    public $cargos;
   
    public $loading_points;
 
    public $offloading_points;
   
   

    public $works;

    public $locations;
   
    public $calculated_mileage;

    public $exchange_rate;
    public $exchange_amount;
    public $turnover;

  // Rehandling Vars
    public $work_id = [];
    public $location_id = [];
    public $loads = [];
    public $start_time = [];
    public $start_hours = [];
    public $start_mileage = [] ;
    public $stop_time = [];
    public $end_hours = [];
    public $end_mileage = [];

 // current rehandling vars  

    public $current_work_id = [];
    public $current_location_id = [];
    public $current_loads = [];
    public $current_start_time = [];
    public $current_start_hours = [];
    public $current_start_mileage = [];
    public $current_stop_time = [];
    public $current_end_hours = [];
    public $current_end_mileage = [];

    public $name;
    public $location;
    public $team;
    public $odometer;
   

     //fuel vars

    public $company;
    public $employee;
    public $user;
    public $from_destination;
    public $to_destination;
    public $vehicle_id;
    public $horse_id;
    public $cargo_id;
    public $transporter_id;
    public $users;
    public $filters = [];

    public $selected_container;
    public $fuel_category;
    public $selectedFuelCurrency;
    public $selected_fuel_currency;
    public $fuel_exchange_rate;
    public $fuel_exchange_amount;
    public $fuel_quantity = 0 ;
    public $container_balance;
    public $unit_price = 0;
    public $fuel_amount;
    public $mileage;
    public $hours;
    public $fuel_comments;
    public $fuel_id;
    public $importFile;
    public $shift_tripsimportFile;
    public $shift_trips;
    public $shift_rehandlings;

    public $cargo_type = [];
    public $calculation_measurement = [];

   
    public $liquid_measurements;
    public $solid_measurements;

    public $trip_type;

    public $trip_inputs = [];
    public $t = 1;
    public $r = 1;

    public function addTrip($t)
    {
        $t = $t + 1;
        $this->t = $t;
        
        array_push($this->trip_inputs ,$t);

        $this->weight[$t] = $this->company->default_weight;
        $this->rate[$t]   = $this->company->default_rate;

    }
    public function removeTrip($t)
    {
        unset($this->trip_inputs[$t]);
    }

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


    
    private function resetInputFields(){
        $this->name = Null;
        $this->shift_start_time = Null;
        $this->for = Null;
        $this->depart_workshop_time = Null;
        $this->arrive_location_time = Null;
        $this->depart_location_time = Null;
        $this->arrive_workshop_time = Null;
        $this->fuel_category = Null;
        $this->account_id = Null;
        $this->fuel_quantity = Null;
        $this->unit_price = Null;
        $this->fuel_amount = Null;
        $this->mileage = Null;
        $this->hours = Null;
        $this->fuel_comments = Null;
        $this->customer_id = Null;
        $this->driver_id = Null;
        $this->selectedTransporter = Null;
        $this->shift_end_time = Null;
        $this->shift_trips = Null;
        $this->shift_rehandlings = Null;
        $this->type = Null;
        $this->shift_id = Null;
        $this->team_id = Null;
        $this->date = Null;
        $this->from_destination = Null;
        $this->to_destination = Null;
        $this->location = Null;
        $this->start_time = [];
        $this->stop_time = [];
        $this->work_id = [];
        $this->inputs = [];
        $this->trip_inputs = [];
        $this->location_id = [];
        $this->shift_open_mileage = Null;
        $this->shift_open_hours = Null;
        $this->shift_close_mileage = Null;
        $this->shift_close_hours = Null;
        $this->start_mileage = Null;
        $this->start_hours = Null;
        $this->end_mileage = Null;
        $this->end_hours = Null;
        $this->arrive_lp = [];
        $this->arrive_op = [];
        $this->depart_lp = [];
        $this->depart_op = [];
        $this->selectedFrom = [];
        $this->selectedTo = [];
        $this->haulage_type = [];
        $this->weight = [];
        $this->loads = [];
        $this->rate = [];
        $this->trip_ref = [];
        $this->loading_point_id = [];
        $this->offloading_point_id = [];
        $this->selectedCargo = [];
        $this->selectedContainer = Null;
        $this->selectedCurrency = Null;
        $this->starting_mileage = [];
        $this->ending_mileage = [];
        $this->starting_hours = [];
        $this->ending_hours = [];
        $this->litreage_at_20 = [];
        $this->litreage = [];
        $this->quantity = [];
        $this->measurement = [];
        //current vars
        $this->current_arrive_lp = [];
        $this->current_arrive_op = [];
        $this->current_depart_lp = [];
        $this->current_depart_op = [];
        $this->current_selectedFrom = [];
        $this->current_selectedTo = [];
        $this->current_haulage_type = [];
        $this->current_cargo_type = [];
        $this->current_weight = [];
        $this->current_rate = [];
        $this->current_trip_ref = [];
        $this->current_loading_point_id = [];
        $this->current_offloading_point_id = [];
        $this->current_selectedCargo = [];
        $this->current_starting_mileage = [];
        $this->current_ending_mileage = [];
        $this->current_starting_hours = [];
        $this->current_ending_hours = [];
        $this->current_litreage_at_20 = [];
        $this->current_litreage = [];
        $this->current_quantity = [];
        $this->current_measurement = [];
        $this->fuel_order = False;
        $this->selectedFuelCurrency = Null;
        $this->selectedHorse = Null;
        $this->selectedVehicle = Null;

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openClose($id){
        $this->shift_id = $id;
        $this->dispatchBrowserEvent('show-closeShiftModal');
    }

    public function closeShift(){

        $shift = Shift::find($this->shift_id);
        $shift->status = $this->status;
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-closeShiftModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Shift Closed Successfully!!"
        ]);
        
    }

    

     public function updatedSelectedCargo($id , $key)
    {
        if(is_null($id) && is_null($key)){
           return ;
        }

        $cargo = Cargo::find($id);
        $this->cargo_type[$key] = $cargo?->type;
        if(isset($this->cargo_type[$key])){
            if($this->cargo_type[$key] == "Solid"){
                $this->calculation_measurement[$key] = "weight";
            }elseif($this->cargo_type[$key] == "Liquid"){
                $this->calculation_measurement[$key] = "litreage_at_20";
            }
        }
    
    }


    public function mount(){
        $this->resetPage();
        $this->reset(['searchHorse','searchDriver','searchVehicle','search']);
        $this->user = Auth::user();
        $this->equipment = "Horse";
        $this->shift_filter = "created_at";
        $this->employee =  $this->user->employee;
        $this->company = Company::with('currency')->find( $this->employee->company_id);
     
        $this->team = $this->employee->teams->first();
        $this->team_id =  $this->team?->id;
        $this->liquid_measurements = Measurement::where('cargo_type','Liquid')->orderBy('name','asc')->get();
        $this->solid_measurements = Measurement::where('cargo_type','Solid')->orderBy('name','asc')->get();  
        $this->users = User::where('category','employee')->where('active',1)->orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->trip_type = TripType::where('name','Local')->first();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->selectedCurrency = 1;
        $this->drivers = Driver::query()
                        ->with('employee')
                        ->join('employees', 'employees.id', '=', 'drivers.employee_id')
                        ->where('employees.archive',0)
                        ->where('employees.status',1)
                        ->orderBy('employees.name', 'asc')
                        ->orderBy('employees.surname', 'asc')
                        ->select('drivers.*') // important!
                        ->get();
        $this->horses = Horse::where('archive', 0)->orderBy('fleet_number','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->transporters = Transporter::with('vehicles:id,registration_number','vehicles.vehicle_make:id,name','vehicles.vehicle_model:id,name','horses:id,registration_number','horses.horse_make:id,name','horses.horse_model:id,name','cargos:id,name','trailers:id,registration_number,make,model','drivers:id','drivers.employee:id,name,surname')->where('authorization','approved')->orderBy('name','asc')->get();
        $this->vehicles = Vehicle::where('archive', 0)->orderBy('fleet_number','asc')->get();
        $this->containers = Container::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->works = Work::orderBy('description','asc')->get();
        $this->locations = Location::orderBy('name','asc')->get();
        $this->teams = Team::orderBy('name','asc')->get();
    }

    
    public function refresh($category){

  
        if($category == 'transporters'){
            $this->transporters = Transporter::with('vehicles:id,registration_number','vehicles.vehicle_make:id,name','vehicles.vehicle_model:id,name','horses:id,registration_number','horses.horse_make:id,name','horses.horse_model:id,name','cargos:id,name','trailers:id,registration_number,make,model','drivers:id','drivers.employee:id,name,surname')->where('authorization','approved')->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporters Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'horses'){
            if (isset($this->selectedStatus) && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$this->selectedTransporter)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
            }else{
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$this->selectedTransporter)
                ->where('status', 1)
                ->where('service',0)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Horses Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'vehicles'){
            if (isset($this->selectedStatus) && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
              
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$this->selectedTransporter)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
       
            }else{
              
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$this->selectedTransporter)
                ->where('status', 1)
                ->where('service',0)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
             
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vehicles Refreshed Successfully!!."
            ]);
        }
        
        elseif($category == 'drivers'){
            if (isset($this->selectedStatus) && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
                 $this->drivers = Driver::query()
                        ->with('employee')
                        ->join('employees', 'employees.id', '=', 'drivers.employee_id')
                        ->where('employees.archive',0)
                        ->orderBy('employees.name', 'asc')
                        ->orderBy('employees.surname', 'asc')
                        ->select('drivers.*') // important!
                        ->get();
            }else{
                 $this->drivers = Driver::query()
                        ->with('employee')
                        ->join('employees', 'employees.id', '=', 'drivers.employee_id')
                         ->where('drivers.transporter_id',$this->selectedTransporter)
                         ->where('employees.archive',0)
                        ->where('employees.status',1)
                        ->orderBy('employees.name', 'asc')
                        ->orderBy('employees.surname', 'asc')
                        ->select('drivers.*') // important!
                        ->get();
              
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Drivers Refreshed Successfully!!."
            ]);
        }
     
        elseif($category == 'customers'){
            $this->customers = Customer::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customers Refreshed Successfully!!."
            ]);
        }
      
     
        elseif($category == 'destinations'){
           $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'loading_points'){
            $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loading Points Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'offloading_points'){
            $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Offloading Points Refreshed Successfully!!."
            ]);
        }
 
  
        elseif($category == 'cargos'){
            if ($this->selectedTransporter) {
                $this->cargos = Transporter::find($this->selectedTransporter)->cargos->sortBy('name');
            }else{
                $this->cargos = Cargo::orderBy('name','asc')->get();
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargos Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'measurements'){
            $this->measurements = Measurement::orderBy('name','asc')->get();
            $this->liquid_measurements = Measurement::where('cargo_type','Liquid')->get();
            $this->solid_measurements = Measurement::where('cargo_type','Solid')->get(); 
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Measurements Refreshed Successfully!!."
            ]);
        }
          elseif($category == "works"){
            $this->works = Work::where('status',1)->orderBy('description','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Work Descriptions Refreshed Successfully!!."
            ]);
        }
        elseif($category == "locations"){
            $this->locations = Location::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Locations Refreshed Successfully!!."
            ]);
        }
        elseif($category == "teams"){
            $this->teams = Team::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Teams Refreshed Successfully!!."
            ]);
        }
     
    
        
    }

    public function getDestination($id){
        if(is_null($id)){
            return ;
        }

        $destination = Destination::find($id);
        return $destination->city;
    }

      public function updatedSearchDriver()
        {
         
            $term = trim((string) $this->searchDriver);

            $query = Driver::query()
                ->with('employee:id,name,surname')
                ->join('employees', 'drivers.employee_id', '=', 'employees.id')  // for sorting
                ->select('drivers.*')                                            // avoid column conflicts
                ->where('drivers.archive', 0);

            // Optional transporter filter
            if (!empty($this->selectedTransporter)) {
                $query->where('drivers.transporter_id', $this->selectedTransporter);
            }

            // Flexible search: name, surname, or "name surname"
            if ($term !== '') {
                $query->whereHas('employee', function ($q) use ($term) {
                    $q->where(function ($qq) use ($term) {
                        $qq->where('name', 'like', "%{$term}%")
                        ->orWhere('surname', 'like', "%{$term}%")
                        ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$term}%"]);
                    });
                });
            }

            // Status gating: only apply drivers.status = 1 if NOT in special statuses
            $specialStatuses = ['Scheduled', 'Offloaded', 'Cancelled'];
            if (!in_array($this->selectedStatus, $specialStatuses, true)) {
                $query->where('drivers.status', 1); // qualify to avoid ambiguity
            }

            $this->drivers = $query
                ->orderBy('employees.name', 'asc')
                ->orderBy('employees.surname', 'asc')
                ->get();
        }

        public function updatedSearchHorse()
    {
        $term = trim((string) $this->searchHorse);

        $query = Horse::query()
            ->with('horse_make:id,name', 'horse_model:id,name')
            ->where('archive', 0)
            ->where('registration_number', 'like', "%{$term}%")
            ->orWhere('fleet_number', 'like', "%{$term}%");
           

        // Optional transporter filter
        if (!empty($this->selectedTransporter)) {
            $query->where('transporter_id', $this->selectedTransporter);
        }

        // Only enforce active/service filters when not in the special statuses
        $specialStatuses = ['Scheduled', 'Offloaded', 'Cancelled'];
        if (!in_array($this->selectedStatus, $specialStatuses, true)) {
            $query->where('status', 1)
                ->where('service', 0);
        }

        // Order by registration number
        $this->horses = $query
            ->orderBy('registration_number', 'asc')
            ->get();
    }

    public function updatedSearchVehicle()
    {
        $term = trim((string) $this->searchVehicle);

        $query = Vehicle::query()
            ->with('vehicle_make:id,name', 'vehicle_model:id,name')
            ->where('archive', 0)
            ->where('registration_number', 'like', "%{$term}%")
            ->orWhere('fleet_number', 'like', "%{$term}%");

        // Optional transporter filter
        if (!empty($this->selectedTransporter)) {
            $query->where('transporter_id', $this->selectedTransporter);
        }

        // Apply status and service filters only if not in special statuses
        $specialStatuses = ['Scheduled', 'Offloaded', 'Cancelled'];
        if (!in_array($this->selectedStatus, $specialStatuses, true)) {
            $query->where('status', 1)
                ->where('service', 0);
        }

        // Order by registration number (ascending)
        $this->vehicles = $query
            ->orderBy('registration_number', 'asc')
            ->get();
    }

    public function updatingCloseMileage(){
        $this->odometer = $this->close_mileage;
    }

    public function updatedSelectedCurrency($id){
        if(!is_null($id)){
            $this->selected_currency = Currency::find($id);
            if($id != $this->company->currency_id){
                $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                    ->where('status', 1)
                    ->where('expiry', '>', Carbon::today())
                    ->first();
                if ($predefined_exchange_rate) {   
                    $this->exchange_rate = $predefined_exchange_rate->exchange_rate;
                }
            }
        }
    }

    public function updatedSelectedFuelCurrency($id){
        if(!is_null($id)){
            $this->selected_fuel_currency = Currency::find($id);
            if($id != $this->company->currency_id){
                $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                    ->where('status', 1)
                    ->where('expiry', '>', Carbon::today())
                    ->first();
                
                if ($predefined_exchange_rate) {   
                    $this->fuel_exchange_rate = $predefined_exchange_rate->exchange_rate;
                }
            }
           
            
        }
    }
    

      

        public function updatedSelectedContainer($id)
            {
                if (!is_null($id) ) {
                    $container = Container::find($id);
                    $this->selected_container = Container::find($id);
                    $this->container_balance = $container->balance;
                    $this->selectedFuelCurrency = $container->currency_id;
                }
            }

            

        public function updatedAllHorses($status){
        if(!is_null($status)){
            if($status == True){
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
              
            }else{
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$this->selectedTransporter)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
               
            }

        }
       
    }
    public function updatedAllVehicles($status){
        if(!is_null($status)){
            if($status == True){
             
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
               
            }else{
                
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$this->selectedTransporter)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
               
            }

        }
       
    }

        public function updatedSelectedTransporter($id)
    {
        if (!is_null($id) ) {
            $this->selectedTransporter = $id;
            $transporter = Transporter::find($id);
            $this->cargos = $transporter->cargos->sortBy('name');

            if (isset($this->selectedStatus) && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                 $this->drivers = Driver::query()
                        ->with('employee')
                        ->join('employees', 'employees.id', '=', 'drivers.employee_id')
                         ->where('drivers.transporter_id',$id)
                        ->where('employees.archive',0)
                        ->orderBy('employees.name', 'asc')
                        ->orderBy('employees.surname', 'asc')
                        ->select('drivers.*') // important!
                        ->get();
            }else{
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$id)
                ->where('status', 1)
                ->where('service',0)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$id)
                ->where('status', 1)
                ->where('service',0)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->drivers = Driver::query()
                        ->with('employee')
                        ->join('employees', 'employees.id', '=', 'drivers.employee_id')
                          ->where('drivers.transporter_id',$id)
                        ->where('employees.archive',0)
                        ->where('employees.status',1)
                        ->orderBy('employees.name', 'asc')
                        ->orderBy('employees.surname', 'asc')
                        ->select('drivers.*') // important!
                        ->get();
            }
     
          
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'type' => 'required',
        'date' => 'required',
        'shift_start_time' => 'nullable|date_format:Y-m-d\TH:i',
        'shift_end_time'   => 'nullable|date_format:Y-m-d\TH:i',
    ];

    public function exportShiftsCSV(Excel $excel){

        return $excel->download(new ShiftsExport($this->from, $this->to, $this->shift_filter, $this->search, $this->filters), 'shifts_'.time().'.csv', Excel::CSV);
    }
    public function exportShiftsPDF(Excel $excel){

        return $excel->download(new ShiftsExport($this->from, $this->to, $this->shift_filter, $this->search, $this->filters), 'shifts_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportShiftsExcel(Excel $excel){
        return $excel->download(new ShiftsExport($this->from, $this->to, $this->shift_filter, $this->search, $this->filters), 'shifts_'.time().'.xlsx');
    }

        public function importShifts(){
      
        $file = $this->importFile;
        $import = new ShiftsImport($this->for);
        $import->import($file);

        $this->dispatchBrowserEvent('hide-shiftsImportModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Shift(s) Imported Successfully!!"
        ]);

        return redirect(request()->header('Referer'));
    }
        
    public function importShiftTrips()
    {
        // Validate the file before doing anything
        $this->validate([
            'shift_tripsimportFile' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $file = $this->shift_tripsimportFile;

            // Additional guard: make sure it's a valid UploadedFile instance
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                throw new \Exception("Invalid file uploaded.");
            }

            $import = new ShiftTripsImport($this->for);
            $import->import($file);

            $this->dispatchBrowserEvent('hide-shiftTripsImportModal');

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Shift Trip(s) Imported Successfully!"
            ]);
        } catch (NoFilePathGivenException $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => "No file was received. Please upload a file first."
            ]);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => "Import failed due to validation errors in the file."
            ]);
            // Optionally, handle and log $failures
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => "Import failed: " . $e->getMessage()
            ]);
        }

        return redirect(request()->header('Referer'));
    }

       public function shiftNumber(){

     if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
 
        $shift = Shift::orderBy('id','desc')->first();

        if (!$shift) {
            $shift_number =  $initials .'ST'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $shift->id + 1;
            $shift_number =  $initials .'ST'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $shift_number;

    }

        public function orderNumber(){

      if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $fuel = Fuel::orderBy('id','desc')->first();

        if (!$fuel) {
            $fuel_number =  $initials .'FO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $fuel->id + 1;
            $fuel_number =  $initials .'FO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $fuel_number;
    }

        public function updatedUnitPrice(){
             $this->calculateFuelAmount();
        }
   
  

        public function calculateFuelAmount(){
            if(($this->unit_price && is_numeric($this->unit_price)) && ($this->fuel_quantity  && is_numeric($this->fuel_quantity) )){
                $this->fuel_amount = $this->unit_price * $this->fuel_quantity;
            }
        }

       

    public function calculateFuelConsumption($id)
    {
        $shift = Shift::find($id);
        if (!$shift) return;

        $fuel = $shift->fuel;
        if (!$fuel || !is_numeric($fuel->quantity) || $fuel->quantity <= 0) return;

        $rehandlings = $shift->rehandlings;
        if (!$rehandlings || $rehandlings->isEmpty()) return;

        $distance = null;
        $hours_distance = null;

        $first = $rehandlings->first();
        $last = $rehandlings->count() > 1
            ? $rehandlings->sortByDesc('created_at')->first()
            : $first;

        if (is_numeric($first->start_mileage) && is_numeric($last->end_mileage)) {
            $distance = $last->end_mileage - $first->start_mileage;
        }

        if (is_numeric($first->start_hours) && is_numeric($last->end_hours)) {
            $hours_distance = $last->end_hours - $first->start_hours;
        }

        $total_fuel = $fuel->quantity;

        if (is_numeric($distance) && $distance > 0) {
            $shift->fuel_consumption_mileage = $distance / $total_fuel;
        }

        if (is_numeric($hours_distance) && $hours_distance > 0) {
            $shift->fuel_consumption_hours = $hours_distance / $total_fuel;
        }

        $shift->save();
    }

    public function updatedShiftStartTime($value)
    {
        $this->shift_start_time = $this->normalizeDateTimeLocal($value);
    }

    public function updatedShiftEndTime($value)
    {
        $this->shift_end_time = $this->normalizeDateTimeLocal($value);
    }

    private function normalizeDateTimeLocal($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') return null;

        // Strict check for datetime-local format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            return null; // drop invalid values
        }

        // If it matches, keep it as-is (DB format can be handled later)
        return $value;
    }


    public function calculatedShiftDuration($shift){

        try {
            $start = Carbon::createFromFormat('Y-m-d\TH:i', $shift->shift_start_time);
        } catch (\Exception $e) {
            $start = null;
        }
        try {
            $end = Carbon::createFromFormat('Y-m-d\TH:i', $shift->shift_end_time);
        } catch (\Exception $e) {
            $end = null;
        }

        if ($start == null || $end == null) {
            return null;
        }

        // If you have dates for the shift times, parse them directly
        // Otherwise, handle cases where only the time is given

        // If only time is stored and end is "before" start, assume it's the next day
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        // Get total seconds difference (works for > 24 hours too)
        $diffInSeconds = $end->diffInSeconds($start);

        // Convert to hours, minutes, and seconds
        $hours = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        // Format as HH:MM:SS, even if hours > 24
         $durationFormatted = sprintf('%02dH: %02dM: %02dS', $hours, $minutes, $seconds);
        return $durationFormatted;
    }

      public function tripNumber(){

        if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
 
        $trip = Trip::orderBy('id','desc')->first();

        if (!$trip) {
            $trip_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $trip->id + 1;
            $trip_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $trip_number;

    }
      public function rehandlingNumber(){

        if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
 
        $rehandling = Rehandling::orderBy('id','desc')->first();

        if (!$rehandling) {
            $rehandling_number =  $initials .'R'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $rehandling->id + 1;
            $rehandling_number =  $initials .'R'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $rehandling_number;

    }

      private function calculateTimeDifference($start, $end)
    {
        if ($start && $end) {
            $start = $start instanceof Carbon ? $start : Carbon::parse($start);
            $end = $end instanceof Carbon ? $end : Carbon::parse($end);
            $diff = $end->diffInMinutes($start);
            return sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
        }
        return null;
    }
    

    public function createTrips($shift_id){
      
            if($this->shift_trips){
              
                foreach($this->shift_trips as $key => $value){

                    $trip = Trip::find($value->id);
                  
                    if(isset($this->current_trip_ref[$key])){
                        $trip->trip_ref = $this->current_trip_ref[$key];

                    }

                    $trip->horse_id =  $this->selectedHorse ?: null;
                    $trip->vehicle_id =  $this->selectedVehicle ?: null;
                    $trip->transporter_id = $this->selectedTransporter ?: null;
                    $trip->driver_id = $this->driver_id ?: null;
                    $trip->customer_id = $this->customer_id ?: null;
                    $trip->freight_calculation = "rate_weight";
                    $trip->calculation_measurement = "weight";
                    $trip->currency_id = $this->selectedCurrency ?: $this->company->currency_id;
                    $trip->exchange_rate = $this->exchange_rate;
                    $trip->start_date = $this->date;
                   
                    $trip->trip_type_id = $this->trip_type?->id;
                    $trip->with_cargos = True;
                    $trip->end_date = $this->date;
               
                    if(isset($this->current_arrive_lp[$key]) && isset($this->current_depart_lp[$key])){
                        $trip->arrive_loading_point = $this->current_arrive_lp[$key];
                        $trip->depart_loading_point = $this->current_depart_lp[$key];
                        $trip->loading_time = $this->calculateTimeDifference($this->current_arrive_lp[$key], $this->current_depart_lp[$key]);
                    }
               
                    if(isset($this->current_arrive_op[$key]) && isset($this->current_depart_op[$key])){
                        $trip->arrive_offloading_point = $this->current_arrive_op[$key];
                        $trip->depart_offloading_point = $this->current_depart_op[$key];
                        $trip->offloading_time = $this->calculateTimeDifference($this->current_arrive_op[$key], $this->current_depart_op[$key]);
                    }
                    $cargo_type = Null;
                    if(isset($this->current_selectedCargo[$key])){
                        $trip->cargo_id = $this->current_selectedCargo[$key];
                        $cargo = Cargo::find($this->current_selectedCargo[$key]);
                        $cargo_type = $cargo?->type;
                    }
                    if(isset($this->current_haulage_type[$key])){
                        $trip->haulage_type = $this->current_haulage_type[$key];

                    }

                    if(isset($this->current_starting_mileage[$key])){
                        $trip->starting_mileage = $this->current_starting_mileage[$key];
                    }

                    if(isset($this->current_starting_hours[$key])){
                        $trip->starting_hours = $this->current_starting_hours[$key];
                    }

                    if(isset($this->current_ending_mileage[$key])){
                        $trip->ending_mileage = $this->current_ending_mileage[$key];
                    }

                    if(isset($this->current_ending_hours[$key])){
                        $trip->ending_hours = $this->current_ending_hours[$key];
                    }
                    if((isset($this->current_starting_mileage[$key]) && is_numeric($this->current_starting_mileage[$key])) && (isset($this->current_ending_mileage[$key]) && is_numeric($this->current_ending_mileage[$key])) && $this->current_ending_mileage[$key] >= $this->current_starting_mileage[$key]){
                        $trip->distance = $this->current_ending_mileage[$key] - $this->current_starting_mileage[$key];
                    }
                    if((isset($this->current_starting_hours[$key]) && is_numeric($this->current_starting_hours[$key])) && (isset($this->current_ending_hours[$key]) && is_numeric($this->current_ending_hours[$key])) && $this->current_ending_hours[$key] >= $this->current_starting_hours[$key]){
                        $trip->hours_distance = $this->current_ending_hours[$key] - $this->current_starting_hours[$key];
                    }
               
                    if(isset($this->current_selectedFrom[$key])){
                        $trip->from = $this->current_selectedFrom[$key];
                    }
                    if(isset($this->current_selectedTo[$key])){
                        $trip->to = $this->current_selectedTo[$key];
                    }
                    if(isset($this->current_offloading_point_id[$key])){
                        $trip->offloading_point_id = $this->current_offloading_point_id[$key];
                    }
                    if(isset($this->current_loading_point_id[$key])){
                        $trip->loading_point_id = $this->current_loading_point_id[$key];
                    }
                    if(isset($this->current_rate[$key])){
                        $trip->rate = $this->current_rate[$key];
                    }
                    if(isset($this->current_weight[$key])){
                        $trip->weight = $this->current_weight[$key];
                    }
                    if(isset($this->current_litreage[$key])){
                        $trip->litreage = $this->current_litreage[$key];
                    }
                    if(isset($this->current_litreage_at_20[$key])){
                        $trip->litreage_at_20 = $this->current_litreage_at_20[$key];
                    }

                    if($cargo_type == "Solid"){
                    if((isset($this->current_rate[$key]) && is_numeric($this->current_rate[$key])) && (isset($this->current_weight[$key]) && is_numeric($this->current_weight[$key])) ){
                        $this->current_freight[$key] = $this->current_rate[$key] * $this->current_weight[$key]; 
                    }
                    }else{
                        if((isset($this->current_rate[$key]) && is_numeric($this->current_rate[$key])) && (isset($this->current_litreage_at_20[$key]) && is_numeric($this->current_litreage_at_20[$key])) ){
                        $this->current_freight[$key] = $this->current_current_rate[$key] * $this->current_litreage_at_20[$key]; 
                    }
                }
                
                    if(isset($this->current_freight[$key])){
                        $trip->freight = $this->current_freight[$key];
                        $trip->turnover = $this->current_freight[$key];
                        $this->turnover = $this->current_freight[$key];
                    }
                


                    if(isset($this->current_quantity[$key])){
                        $trip->quantity = $this->current_quantity[$key];
                    }
                    
                    if(isset($this->current_measurement[$key])){
                        $trip->measurement = $this->current_measurement[$key];
                    }
                
                
                    if($this->selectedCurrency != $this->company->currency_id){
                        if(is_numeric($this->exchange_rate) && $this->exchange_rate > 0){
                            $trip->exchange_customer_freight = $this->current_freight[$key] * $this->exchange_rate;
                        }

                    }
               
                    $trip->trip_status = "Offloaded";
                    $trip->trip_status_date = $this->date;

                    
                    $trip->authorization = "approved";
                    $trip->authorization_date = date('Y-m-d');
                    $trip->authorized_by_id = $this->user->id;
                    $trip->save();
                    if(!empty($this->trailer_id)){
                        $trip->trailers()->sync($this->trailer_id);
                    }
     
                
                $delivery_note = DeliveryNote::where("trip_id", $trip->id)->first();
                $delivery_note->user_id =  $trip->user->id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->measurement = $trip->measurement;
                $delivery_note->distance = $trip->distance;
                $delivery_note->loaded_date = $trip->start_date;
                $delivery_note->offloaded_date = $trip->end_date;
                
                if (isset($trip->cargo)) {
                if ($trip->cargo->type == "Liquid") {
                    $delivery_note->loaded_litreage = $trip->litreage;
                    $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
                    }elseif($trip->cargo->type == "Solid") {
                        $delivery_note->loaded_quantity = $trip->quantity;
                    }
                }
   
                $delivery_note->loaded_weight = $trip->weight;
                $delivery_note->loaded_rate = $trip->rate;
                $delivery_note->loaded_freight = $trip->freight;
                $delivery_note->save();
                    
                }

            }    

            if(isset($this->haulage_type)){

                foreach($this->haulage_type as $key => $value){

                    $trip = new Trip;
                    $trip->trip_number = $this->tripNumber();

                    if(isset($this->trip_ref[$key])){
                        $trip->trip_ref = $this->trip_ref[$key];

                    }

                    $trip->shift_id = $shift_id;
                    $trip->user_id =  $this->user->id ?: null;
                    $trip->company_id = $this->company->id ?: null;
                    $trip->horse_id =  $this->selectedHorse ?: null;
                    $trip->vehicle_id =  $this->selectedVehicle ?: null;
                    $trip->transporter_id = $this->selectedTransporter ?: null;
                    $trip->driver_id = $this->driver_id ?: null;
                    $trip->customer_id = $this->customer_id ?: null;
                    $trip->freight_calculation = "rate_weight";
                    $trip->calculation_measurement = "weight";
                    $trip->currency_id = $this->selectedCurrency ?: $this->company->currency_id;
                    $trip->exchange_rate = $this->exchange_rate;
                    $trip->start_date = $this->date;
                   
                    $trip->trip_type_id = $this->trip_type?->id;
                    $trip->with_cargos = True;
                    $trip->end_date = $this->date;
               
                    if(isset($this->arrive_lp[$key]) && isset($this->depart_lp[$key])){
                        $trip->arrive_loading_point = $this->arrive_lp[$key];
                        $trip->depart_loading_point = $this->depart_lp[$key];
                        $trip->loading_time = $this->calculateTimeDifference($this->arrive_lp[$key], $this->depart_lp[$key]);
                    }
               
                    if(isset($this->arrive_op[$key]) && isset($this->depart_op[$key])){
                        $trip->arrive_offloading_point = $this->arrive_op[$key];
                        $trip->depart_offloading_point = $this->depart_op[$key];
                        $trip->offloading_time = $this->calculateTimeDifference($this->arrive_op[$key], $this->depart_op[$key]);
                    }
                
                    if(isset($this->selectedCargo[$key])){
                        $trip->cargo_id = $this->selectedCargo[$key];

                    }
                    if(isset($this->haulage_type[$key])){
                        $trip->haulage_type = $this->haulage_type[$key];

                    }

                    if(isset($this->starting_mileage[$key])){
                        $trip->starting_mileage = $this->starting_mileage[$key];
                    }

                    if(isset($this->starting_hours[$key])){
                        $trip->starting_hours = $this->starting_hours[$key];
                    }

                    if(isset($this->ending_mileage[$key])){
                        $trip->ending_mileage = $this->ending_mileage[$key];
                    }

                    if(isset($this->ending_hours[$key])){
                        $trip->ending_hours = $this->ending_hours[$key];
                    }
                    if((isset($this->starting_mileage[$key]) && is_numeric($this->starting_mileage[$key])) && (isset($this->ending_mileage[$key]) && is_numeric($this->ending_mileage[$key])) && $this->ending_mileage[$key] >= $this->starting_mileage[$key]){
                        $trip->distance = $this->ending_mileage[$key] - $this->starting_mileage[$key];
                    }
                    if((isset($this->starting_hours[$key]) && is_numeric($this->starting_hours[$key])) && (isset($this->ending_hours[$key]) && is_numeric($this->ending_hours[$key])) && $this->ending_hours[$key] >= $this->starting_hours[$key]){
                        $trip->hours_distance = $this->ending_hours[$key] - $this->starting_hours[$key];
                    }
               
                    if(isset($this->selectedFrom[$key])){
                        $trip->from = $this->selectedFrom[$key];
                    }
                    if(isset($this->selectedTo[$key])){
                        $trip->to = $this->selectedTo[$key];
                    }
                    if(isset($this->offloading_point_id[$key])){
                        $trip->offloading_point_id = $this->offloading_point_id[$key];
                    }
                    if(isset($this->loading_point_id[$key])){
                        $trip->loading_point_id = $this->loading_point_id[$key];
                    }
                    if(isset($this->rate[$key])){
                        $trip->rate = $this->rate[$key];
                    }
                    if(isset($this->weight[$key])){
                        $trip->weight = $this->weight[$key];
                    }
                    if(isset($this->litreage[$key])){
                        $trip->litreage = $this->litreage[$key];
                    }
                    if(isset($this->litreage_at_20[$key])){
                        $trip->litreage_at_20 = $this->litreage_at_20[$key];
                    }

                    if($this->cargo_type == "Solid"){
                    if((isset($this->rate[$key]) && is_numeric($this->rate[$key])) && (isset($this->weight[$key]) && is_numeric($this->weight[$key])) ){
                        $this->freight[$key] = $this->rate[$key] * $this->weight[$key]; 
                    }
                    }else{
                        if((isset($this->rate[$key]) && is_numeric($this->rate[$key])) && (isset($this->litreage_at_20[$key]) && is_numeric($this->litreage_at_20[$key])) ){
                        $this->freight[$key] = $this->rate[$key] * $this->litreage_at_20[$key]; 
                    }
                }
                
                    if(isset($this->freight[$key])){
                        $trip->freight = $this->freight[$key];
                        $trip->turnover = $this->freight[$key];
                        $this->turnover = $this->freight[$key];
                    }
                


                    if(isset($this->quantity[$key])){
                        $trip->quantity = $this->quantity[$key];
                    }
                    
                    if(isset($this->measurement[$key])){
                        $trip->measurement = $this->measurement[$key];
                    }
                
               
                
                
                    if($this->selectedCurrency != $this->company->currency_id){
                        if(is_numeric($this->exchange_rate) && $this->exchange_rate > 0){
                            $trip->exchange_customer_freight = $this->freight[$key] * $this->exchange_rate;
                        }

                    }
               
                    $trip->trip_status = "Offloaded";
                    $trip->trip_status_date = $this->date;

                    
                    $trip->authorization = "approved";
                    $trip->authorization_date = date('Y-m-d');
                    $trip->authorized_by_id = $this->user->id;
                    $trip->save();
                    if(!empty($this->trailer_id)){
                        $trip->trailers()->sync($this->trailer_id);
                    }
     
                
                $delivery_note = new DeliveryNote;
                $delivery_note->user_id =  $trip->user->id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->measurement = $trip->measurement;
                $delivery_note->distance = $trip->distance;
                $delivery_note->loaded_date = $trip->start_date;
                $delivery_note->offloaded_date = $trip->end_date;
                
                if (isset($trip->cargo)) {
                if ($trip->cargo->type == "Liquid") {
                    $delivery_note->loaded_litreage = $trip->litreage;
                    $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
                    }elseif($trip->cargo->type == "Solid") {
                        $delivery_note->loaded_quantity = $trip->quantity;
                    }
                }
   
                $delivery_note->loaded_weight = $trip->weight;
                $delivery_note->loaded_rate = $trip->rate;
                $delivery_note->loaded_freight = $trip->freight;
                $delivery_note->save();
                    
                }

            }    
        
            
                
      
            // end trip creation logic
    }

    public function updatedSelectedHorse($id){
        if (!is_null($id)) {
            $horse = Horse::find($id);
            $this->shift_open_mileage = $horse->mileage;
            $assignment = $horse?->assignment;
            $this->driver_id = $assignment?->driver_id;
            $cluster = Cluster::where('horse_id',$id)->where('status',True)->first();
            $trailers = $cluster?->trailers;
            if($trailers){
                foreach($trailers as $trailer){
                    $this->trailer_id[] = $trailer->id;
                }
            }
        }
    }

    public function createRehandlingWork($shift_id){

         if (isset($this->shift_rehandlings) && isset($this->work_id)) {

            foreach ($this->shift_rehandlings as $key => $value) {
                $rehandling = Rehandling::find($value->id);
                
                $rehandling->currency_id = $this->selectedCurrency;
                if (isset($this->current_location_id[$key])){
                    $rehandling->location_id = $this->current_ocation_id[$key];
                }
                if (isset($this->current_work_id[$key])){
                    $rehandling->work_id = $this->current_work_id[$key];
                }
                if (isset($this->current_start_time[$key])){
                    $rehandling->start_time = $this->current_start_time[$key];
                }
                if (isset($this->current_starting_hours[$key])){
                    $rehandling->open_hours = $this->current_starting_hours[$key];
                }
                if (isset($this->current_starting_mileage[$key])){
                    $rehandling->open_mileage = $this->current_starting_mileage[$key];
                }
                if (isset($this->current_ending_mileage[$key])){
                    $rehandling->close_mileage = $this->current_ending_mileage[$key];
                }
                if (isset($this->current_ending_hours[$key])){
                    $rehandling->close_hours = $this->current_ending_hours[$key];
                }
                if (isset($this->current_stop_time[$key])){
                    $rehandling->stop_time = $this->current_stop_time[$key];
                }
                if (isset($this->current_weight[$key])){
                    $rehandling->weight = $this->current_weight[$key];
                }
                if (isset($this->current_freight[$key])){
                    $rehandling->freight = $this->current_freight[$key];
                }
                if (isset($this->current_selectedCargo[$key])){
                    $rehandling->cargo_id = $this->current_selectedCargo[$key];
                }
                if (isset($this->current_loads[$key])){
                    $rehandling->loads = $this->current_loads[$key];
                }
     
                $rehandling->save();
            }
        }

         if ($this->work_id) {
            foreach ($this->work_id as $key => $value) {
                $rehandling = new Rehandling;
                $rehandling->user_id = Auth::user()->id;
                $rehandling->shift_id = $shift_id;
                $rehandling->rehandling_number = $this->rehandlingNumber();
                $rehandling->currency_id = $this->selectedCurrency;
                if (isset($this->location_id[$key])){
                    $rehandling->location_id = $this->location_id[$key];
                }
                if (isset($this->selectedCargo[$key])){
                    $rehandling->cargo_id = $this->selectedCargo[$key];
                }
                if (isset($this->work_id[$key])){
                    $rehandling->work_id = $this->work_id[$key];
                }
                if (isset($this->start_time[$key])){
                    $rehandling->start_time = $this->start_time[$key];
                }
                if (isset($this->starting_hours[$key])){
                    $rehandling->open_hours = $this->starting_hours[$key];
                }
                if (isset($this->starting_mileage[$key])){
                    $rehandling->open_mileage = $this->starting_mileage[$key];
                }
                if (isset($this->ending_mileage[$key])){
                    $rehandling->close_mileage = $this->ending_mileage[$key];
                }
                if (isset($this->ending_hours[$key])){
                    $rehandling->close_hours = $this->ending_hours[$key];
                }
                if (isset($this->stop_time[$key])){
                    $rehandling->stop_time = $this->stop_time[$key];
                }
                if (isset($this->weight[$key])){
                    $rehandling->weight = $this->weight[$key];
                }
                if (isset($this->loads[$key])){
                    $rehandling->weight = $this->loads[$key];
                }
                if (isset($this->freight[$key])){
                    $rehandling->freight = $this->freight[$key];
                }
     
                $rehandling->save();
            }
        }
    }
    

    public function store(){
       

        if($this->for == "Trips") {
            $this->validate([
                'selectedCurrency'     => 'nullable',
                'rate.*'               => 'nullable|numeric|min:0',
                'weight.*'             => 'nullable|numeric|min:0',
                'litreage_at_20.*'     => 'nullable|numeric|min:0',
                'starting_mileage.*'   => 'nullable|numeric|min:0',
                'ending_mileage.*'     => 'nullable|numeric|min:0',
                'starting_hours.*'     => 'nullable|numeric|min:0',
                'ending_hours.*'       => 'nullable|numeric|min:0',
                'loading_point_id.*'   => 'nullable|integer|exists:loading_points,id',
                'offloading_point_id.*'=> 'nullable|integer|exists:offloading_points,id',
                'shift_start_time' => 'nullable|date_format:Y-m-d\TH:i',
                'shift_end_time'   => 'nullable|date_format:Y-m-d\TH:i',
                // …add the rest as needed
            ]);
        } 

    DB::transaction(function () {
    
      
        $shift = new Shift;
        $shift->user_id = Auth::user()->id;
        $shift->shift_number = $this->shiftNumber();
        $shift->for = $this->for;
        $shift->type = $this->type;
        $shift->shift_start_time = $this->shift_start_time;
        $shift->shift_end_time = $this->shift_end_time;
        $shift->customer_id = $this->customer_id ?? null;
        $shift->team_id = $this->team_id ?? null;
        $shift->driver_id = $this->driver_id ?? null;
        $shift->currency_id = $this->selectedCurrency ?? null;
        $shift->transporter_id = $this->selectedTransporter ?? null;
        $shift->horse_id = $this->equipment === "Horse" ? $this->selectedHorse : null;
        $shift->vehicle_id = $this->equipment === "Vehicle" ? $this->selectedVehicle : null;
        $shift->equipment = $this->equipment;
        $shift->fuel_order = $this->fuel_order;
        $shift->depart_workshop_time = $this->depart_workshop_time;
        $shift->arrive_location_time = $this->arrive_location_time;
        $shift->depart_location_time = $this->depart_location_time;
        $shift->arrive_workshop_time = $this->arrive_workshop_time;
        $shift->total_fuel = $this->fuel_quantity;
        $shift->open_mileage = $this->shift_open_mileage;
        $shift->close_mileage = $this->shift_close_mileage;
      

        if ($this->for === "Trips") {
            if (is_numeric($this->shift_open_mileage) && is_numeric($this->shift_close_mileage)) {
                $actual_mileage = $this->shift_close_mileage - $this->shift_open_mileage;
                $shift->actual_mileage = $actual_mileage;

                if ($actual_mileage > 0 && is_numeric($this->fuel_quantity) && $this->fuel_quantity > 0) {
                    $shift->fuel_consumption_mileage = $actual_mileage / $this->fuel_quantity;
                }
            }
        }

        
        $shift->calculated_mileage = $this->calculated_mileage;
        $shift->date = $this->date;
        $shift->exchange_amount = $this->exchange_amount;
        $shift->exchange_rate = $this->exchange_rate;
          
        $shift->status = '1';

        $shift->save();

        $shift->loading_points()->sync($this->loading_point_id);
        $shift->offloading_points()->sync($this->offloading_point_id);

        $this->for == "Trips" ? $this->createTrips($shift->id) : $this->createRehandlingWork($shift->id);
         

        if ($this->fuel_order) {
               
                $fuel = new Fuel;
                $fuel->user_id = $shift->user->id;
                $fuel->order_number = $this->orderNumber();
                $fuel->horse_id = $this->selectedHorse ?? null;
                $fuel->account_id = $this->account_id ?? null;
                $fuel->vehicle_id = $this->selectedVehicle ?? null;
                $fuel->currency_id = $this->selectedFuelCurrency;
                $fuel->shift_id = $shift->id;
                $fuel->type = isset($this->selectedVehicle) ? "Vehicle" : (isset($this->selectedHorse) ? "Horse" : null);
                $fuel->driver_id = $this->driver_id ?? null;
                $fuel->container_id = $this->selectedContainer ?? null;
                $fuel->date = $this->date;
                $fuel->unit_price = $this->unit_price;
                $fuel->quantity = $this->fuel_quantity;
                $fuel->amount = $this->fuel_amount;
                $fuel->odometer = $this->mileage;
                $fuel->hours = $this->hours;
                $fuel->category = $this->fuel_category;
                $fuel->exchange_amount = $this->fuel_exchange_amount;
                $fuel->exchange_rate = $this->fuel_exchange_rate;
                $fuel->fillup = 1;
                $fuel->status = 1;
                $fuel->comments = $this->fuel_comments;
                $fuel->save();
            
            }

        $this->calculateFuelConsumption($shift->id);

        $this->dispatchBrowserEvent('hide-shiftModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Shift Created Successfully!!"
        ]);

    });
    }


    public function removeShow($id){
        $this->selected_trip = Trip::find($id);
        $this->shift_id = $this->selected_trip?->shift_id;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeShiftTrip(){ 
        
        $this->selected_trip->delete();
        $this->shift_trips = Trip::where('shift_id',$this->shift_id)->get();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Deleted Successfully!!"
        ]);
       

   

    }
    public function removeRehandlingShow($id){
        $this->selected_rehandling = Rehandling::find($id);
        $this->shift_id = $this->selected_rehandling?->shift_id;
        $this->dispatchBrowserEvent('show-removeRehandlingModal');
    }

    public function removeShiftRehandling(){ 
        
        $this->selected_rehandling->delete();
        $this->shift_rehandlings = Rehandling::where('shift_id',$this->shift_id)->get();
        $this->dispatchBrowserEvent('hide-removeRehandlingModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rehandle Item Deleted Successfully!!"
        ]);
       

   

    }



    public function edit($id){

    $shift = Shift::find($id);
    
    $this->equipment = $shift->equipment;
    $this->shift_start_time = $shift->shift_start_time;
    $this->shift_end_time = $shift->shift_end_time;
    $this->date = $shift->date;
    $this->type = $shift->type;
    $this->updatedSelectedTransporter($shift->transporter_id);
    $this->selectedTransporter = $shift->transporter_id;
    $this->selectedHorse = $shift->horse_id;
    $this->team_id = $shift->team_id;
    $this->driver_id = $shift->driver_id;
    $this->selectedVehicle = $shift->vehicle_id;
    $this->customer_id = $shift->customer_id;
    $this->depart_workshop_time = $shift->depart_workshop_time;
    $this->arrive_location_time = $shift->arrive_location_time;
    $this->depart_location_time = $shift->depart_location_time;
    $this->depart_location_time = $shift->depart_location_time;
    $this->calculated_mileage = $shift->calculated_mileage;
    $this->shift_open_mileage = $shift->open_mileage;
    $this->shift_close_mileage = $shift->close_mileage;
    $this->shift_open_hours = $shift->shift_open_hours;
    $this->shift_close_hours = $shift->shift_close_hours;
    $this->shift_id = $shift->id;
    $this->fuel_order = $shift->fuel_order;
    $this->status = $shift->status;

    $this->for = $shift->for;

    if($this->for == "Trips"){
       
        $this->shift_trips = $shift->trips;

        if($this->shift_trips){
            foreach($this->shift_trips as $trip){
                $this->current_trip_ref[] = $trip->trip_ref;
                $this->current_haulage_type[] = $trip->haulage_type;
                $this->current_rate[] = $trip->rate;
                $this->current_selectedCargo[] = $trip->cargo_id;
                $cargo = Cargo::find($trip->cargo_id);
                 $this->current_cargo_type[] = $cargo?->type;
                if(isset($cargo?->type)){
                    if( $cargo?->type == "Solid"){
                        $this->calculation_measurement[] = "weight";
                    }elseif($cargo?->type == "Liquid"){
                        $this->calculation_measurement[] = "litreage_at_20";
                    }
                }
                $this->current_weight[] = $trip->weight;
                $this->current_quantity[] = $trip->quantity;
                $this->current_measurement[] = $trip->measurement;
                $this->current_litreage[] = $trip->litreage;
                $this->current_litreage_at_20[] = $trip->litreage_at_20;
                $this->current_starting_mileage[] = $trip->starting_mileage;
                $this->current_ending_mileage[] = $trip->ending_mileage;
                $this->current_starting_hours[] = $trip->starting_hours;
                $this->current_ending_hours[] = $trip->ending_hours;
                $this->current_selectedFrom[] = $trip->from;
                $this->current_loading_point_id[] = $trip->loading_point_id;
                $this->current_offloading_point_id[] = $trip->offloading_point_id;
                $this->current_selectedTo[] = $trip->to;
                $this->current_arrive_op[] = $trip->arrive_offloading_point;
                $this->current_depart_op[] = $trip->depart_offloading_point;
                $this->current_arrive_lp[] = $trip->arrive_loading_point;
                $this->current_depart_lp[] = $trip->depart_loading_point;
            }
        }

    }elseif($this->for == "Rehandlings"){

        $this->shift_rehandlings = $shift->rehandlings;

        if($this->shift_rehandlings){
            foreach($this->shift_rehandlings as $rehandling){
                $this->current_location_id[] = $rehandling->location_id;
                $this->current_work_id[] = $rehandling->work_id;
                $this->current_start_time[] = $rehandling->start_time;
                $this->current_open_mileage[] = $rehandling->open_mileage;
                $this->current_close_mileage[] = $rehandling->close_mileage;
                $this->current_open_hours[] = $rehandling->open_hours;
                $this->current_close_hours[] = $rehandling->close_hours;
                $this->current_stop_time[] = $rehandling->stop_time;
                $this->current_weight[] = $rehandling->weight;
                $this->current_freight[] = $rehandling->freight;
            }
        }
    }
  
    $fuel = $shift->fuel;
    if($fuel){
    $this->selectedFuelCurrency = $fuel->currency_id;          
    $this->selectedContainer=  $fuel->container_id;
    $this->account_id =  $fuel->account_id;
    $this->date = $fuel->date;
    $this->unit_price = $fuel->unit_price;
    $this->fuel_quantity = $fuel->quantity;
    $this->fuel_amount = $fuel->amount;
    $this->mileage = $fuel->odometer;
    $this->hours = $fuel->hours;
    $this->fuel_category = $fuel->category;
    $this->fuel_exchange_amount = $fuel->exchange_amount;
    $this->fuel_exchange_rate = $fuel->exchange_rate;
     $this->fuel_comments = $fuel->comments;
    }

    $this->dispatchBrowserEvent('show-shiftEditModal');

    }

    public function update()
    {

         if($this->for == "Trips") {
            $this->validate([
                'selectedCurrency'     => 'nullable',
                'rate.*'               => 'nullable|numeric|min:0',
                'weight.*'             => 'nullable|numeric|min:0',
                'litreage_at_20.*'     => 'nullable|numeric|min:0',
                'starting_mileage.*'   => 'nullable|numeric|min:0',
                'ending_mileage.*'     => 'nullable|numeric|min:0',
                'starting_hours.*'     => 'nullable|numeric|min:0',
                'ending_hours.*'       => 'nullable|numeric|min:0',
                'loading_point_id.*'   => 'nullable|integer|exists:loading_points,id',
                'offloading_point_id.*'=> 'nullable|integer|exists:offloading_points,id',
                'shift_start_time' => 'nullable|date_format:Y-m-d\TH:i',
                'shift_end_time'   => 'nullable|date_format:Y-m-d\TH:i',
                // …add the rest as needed
            ]);
        } 

         DB::transaction(function () {

            if ($this->shift_id) {

            $shift = Shift::find($this->shift_id);
            $shift->for = $this->for;
            $shift->type = $this->type;
            $shift->shift_start_time = $this->shift_start_time;
            $shift->shift_end_time = $this->shift_end_time;
            $shift->customer_id = $this->customer_id ?? null;
            $shift->team_id = $this->team_id ?? null;
            $shift->driver_id = $this->driver_id ?? null;
            $shift->currency_id = $this->selectedCurrency ?? null;
            $shift->transporter_id = $this->selectedTransporter ?? null;
            $shift->horse_id = $this->equipment === "Horse" ? $this->selectedHorse : null;
            $shift->vehicle_id = $this->equipment === "Vehicle" ? $this->selectedVehicle : null;
            $shift->equipment = $this->equipment;
            $shift->fuel_order = $this->fuel_order;
            $shift->depart_workshop_time = $this->depart_workshop_time;
            $shift->arrive_location_time = $this->arrive_location_time;
            $shift->depart_location_time = $this->depart_location_time;
            $shift->arrive_workshop_time = $this->arrive_workshop_time;
            $shift->total_fuel = $this->fuel_quantity;
            $shift->open_mileage = $this->shift_open_mileage;
            $shift->close_mileage = $this->shift_close_mileage;

            if ($this->for === "Trips") {

                if (is_numeric($this->shift_open_mileage) && is_numeric($this->shift_close_mileage)) {
                    $actual_mileage = $this->shift_close_mileage - $this->shift_open_mileage;
                    $shift->actual_mileage = $actual_mileage;

                    if ($actual_mileage > 0 && is_numeric($this->fuel_quantity) && $this->fuel_quantity > 0) {
                        $shift->fuel_consumption_mileage = $actual_mileage / $this->fuel_quantity;
                    }
                }

            }

          
            $shift->calculated_mileage = $this->calculated_mileage;
            $shift->date = $this->date;
           
            $shift->exchange_amount = $this->exchange_amount;
            $shift->exchange_rate = $this->exchange_rate;
            $shift->status = '1';
            $shift->update();

            $shift->loading_points()->sync($this->current_loading_point_id);
            $shift->offloading_points()->sync($this->current_offloading_point_id);
            $shift->loading_points()->sync($this->loading_point_id);
            $shift->offloading_points()->sync($this->offloading_point_id);
           
            $this->for == "Trips" ? $this->createTrips($shift->id) : $this->createRehandlingWork($shift->id);

            if ($this->fuel_order) {

                // (Optional) you were fetching this but not using it anywhere
                $container = Container::find($this->selectedContainer);

                $type = isset($this->selectedVehicle)
                    ? "Vehicle"
                    : (isset($this->selectedHorse) ? "Horse" : null);

                $fuel = Fuel::updateOrCreate(
                    // ✅ Match (find existing row)
                    [
                        'shift_id' => $shift->id,
                        // If a shift can have multiple fuel rows, add another discriminator:
                        // 'fillup' => 1,
                    ],
                    // ✅ Update / Create values
                    [
                        'user_id'         => $shift->user->id,
                        // Only used when creating (see note below)
                        'order_number'    => $shift->fuel ? $shift->fuel->order_number : $this->orderNumber(),

                        'account_id'        => $this->account_id ?? null,
                        'horse_id'        => $this->selectedHorse ?? null,
                        'vehicle_id'      => $this->selectedVehicle ?? null,
                        'currency_id'     => $this->selectedFuelCurrency,
                        'type'            => $type,
                        'driver_id'       => $this->driver_id ?? null,
                        'container_id'    => $this->selectedContainer ?? null,
                        'date'            => $this->date,
                        'unit_price'      => $this->unit_price,
                        'quantity'        => $this->fuel_quantity,
                        'amount'          => $this->fuel_amount,
                        'odometer'        => $this->mileage,
                        'hours'           => $this->hours,
                        'category'        => $this->fuel_category,
                        'exchange_amount' => $this->fuel_exchange_amount,
                        'exchange_rate'   => $this->fuel_exchange_rate,
                        'fillup'          => 1,
                        'status'          => 1,
                        'comments'        => $this->fuel_comments,
                    ]
                );
            }

             $this->calculateFuelConsumption($shift->id);

            $this->dispatchBrowserEvent('hide-shiftEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Shift Updated Successfully!!"
            ]);

        }
    });
    
    }

     public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
            $employee = Employee::find($id);
            $this->employee_user_id = $employee->user_id;
        }
    }
    public function render()
    {

         $this->filters = [
            'shift_filter' => $this->shift_filter,
            'transporter_id' => $this->transporter_id,
            'customer_id' => $this->customer_id,
            'driver_id' => $this->driver_id,
            'user_id' => $this->user_id,
            'horse_id' => $this->horse_id,
            'from_destination' => $this->from_destination,
            'to_destination' => $this->to_destination,
            'cargo_id' => $this->cargo_id,
            'vehicle_id' => $this->vehicle_id,
            'haulage_type' => $this->haulage_type,
            'loading_point_id' => $this->loading_point_id,
            'offloading_point_id' => $this->offloading_point_id,
            'shift_type' => $this->shift_type,
        ];

        if ((isset($this->fuel_exchange_rate) && $this->fuel_exchange_rate > 0 && is_numeric($this->fuel_exchange_rate)) && (isset($this->fuel_amount) && $this->fuel_amount > 0 && is_numeric($this->fuel_amount)) ) {
            $this->fuel_exchange_amount = $this->fuel_exchange_rate * $this->fuel_amount;
        }

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {
            $this->exchange_amount = $this->exchange_rate * $this->total;
        }

        

        $baseQuery = Shift::query()
       ->with([
            'trips:id,shift_id,trip_number,trip_ref,haulage_type,from,to,loading_point_id,cargo_id,offloading_point_id,arrive_loading_point,depart_loading_point,arrive_offloading_point,depart_offloading_point,weight',
            'trips.loading_point:id,name',
            'trips.offloading_point:id,name',
            'trips.cargo:id,name',
            'customer:id,name',
            'driver',
            'horse',
            'vehicle',
            'transporter',
            'fuel',
       ]);

        /**
         * 1) Date filtering: range if from/to, else default current month/year
         */
        $baseQuery->when(
            filled($this->from) && filled($this->to),
            fn (Builder $q) => $q->whereBetween($this->shift_filter, [
                Carbon::parse($this->from)->startOfDay(),
                Carbon::parse($this->to)->endOfDay(),
            ]),
            fn (Builder $q) => $q->whereMonth($this->shift_filter, now()->month)
                ->whereYear($this->shift_filter, now()->year)
        );

        /**
         * 2) Dropdown / selected filters
         *    (Assuming these values are IDs)
         */
        $baseQuery
        ->when(filled($this->filter_transporter_id), fn (Builder $q) => $q->where('transporter_id', $this->filter_transporter_id))
        ->when(filled($this->filter_customer_id), fn (Builder $q) => $q->where('customer_id', $this->filter_customer_id))
        ->when(filled($this->filter_driver_id), fn (Builder $q) => $q->where('driver_id', $this->filter_driver_id))
        ->when(filled($this->filter_horse_id), fn (Builder $q) => $q->where('horse_id', $this->filter_horse_id))
        ->when(filled($this->filter_vehicle_id), fn (Builder $q) => $q->where('vehicle_id', $this->filter_vehicle_id))
        ->when(filled($this->filter_cargo_id), fn (Builder $q) => $q->where('cargo_id', $this->filter_cargo_id))
        ->when(filled($this->filter_shift_type), fn (Builder $q) => $q->where('type', $this->filter_shift_type))
        ->when(filled($this->filter_user_id), fn (Builder $q) => $q->where('user_id', $this->filter_user_id))

        // trips-based loading/offloading filters
        ->when(filled($this->filter_from_destination), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('from', $this->filter_from_destination))
        )
        ->when(filled($this->filter_to_destination), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('to', $this->filter_to_destination))
        )
        ->when(filled($this->filter_haulage_type), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', $this->filter_haulage_type))
        )
        ->when(filled($this->filter_loading_point_id), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('loading_point_id', $this->filter_loading_point_id))
        )
        ->when(filled($this->filter_offloading_point_id), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('offloading_point_id', $this->filter_offloading_point_id))
        );

        /**
         * 3) Search filtering (free text)
         */
        $baseQuery->when(filled($this->search), function (Builder $q) {
            $term = trim($this->search);
            $like = "%{$term}%";

            $q->where(function (Builder $query) use ($term, $like) {
                $query->where('shift_number', 'like', $like)
                    ->orWhere('type', 'like', $like)
                    ->orWhere('date', 'like', $like)
                    ->orWhere('for', 'like', $like)
                    ->orWhereHas('customer', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('team', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('horse', function (Builder $qq) use ($like) {
                        $qq->where('registration_number', 'like', $like)
                        ->orWhere('fleet_number', 'like', $like);
                    })
                    ->orWhereHas('vehicle', function (Builder $qq) use ($like) {
                        $qq->where('registration_number', 'like', $like)
                        ->orWhere('fleet_number', 'like', $like);
                    })
                    ->orWhereHas('cargo', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('transporter', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('driver.employee', function (Builder $qq) use ($like) {
                        $qq->where(DB::raw("CONCAT(name,' ',surname)"), 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like);
                    });
            });
        });

        return view('livewire.shifts.index', [
            'shifts' => $baseQuery
                ->orderBy($this->shift_filter, 'desc')
                ->paginate(10),
        ]);
        
       
    }
}
