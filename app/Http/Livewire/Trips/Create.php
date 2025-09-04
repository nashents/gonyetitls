<?php

namespace App\Http\Livewire\Trips;

use Carbon\Carbon;
use App\Models\Fuel;
use App\Models\Hour;
use App\Models\Rate;
use App\Models\Trip;
use App\Models\Agent;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Route;
use App\Models\Shift;
use App\Models\TopUp;
use App\Models\Border;
use App\Models\Broker;
use App\Models\Driver;
use App\Models\Account;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Mileage;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Distance;
use App\Models\Employee;
use App\Models\EmptyRun;
use App\Models\TripType;
use App\Models\Allowance;
use App\Models\Consignee;
use App\Models\Container;
use App\Models\FuelCount;
use App\Models\Quotation;
use App\Models\TripCount;
use App\Models\TripGroup;
use App\Models\TruckStop;
use App\Models\Assignment;
use App\Models\Commission;
use App\Models\TripReturn;
use App\Models\Destination;
use App\Models\Measurement;
use App\Models\Transporter;
use App\Models\TripExpense;
use App\Models\DeliveryNote;
use App\Models\ExchangeRate;
use App\Models\LoadingPoint;
use App\Models\Notification;
use App\Models\RouteExpense;
use App\Models\ClearingAgent;
use Livewire\WithFileUploads;
use App\Models\AllowanceDriver;
use App\Models\ExpenseCategory;
use App\Models\OffloadingPoint;
use App\Models\TrailerAssignment;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;
use Illuminate\Support\Facades\Session;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;

class Create extends Component
{
    use WithFileUploads;
    protected $queryString = ['searchTrip','searchVehicle','searchHorse','searchTrailer','searchDriver'];
    public $trip_id;
    public $trip_types;
    public $haulage_type;
    public $selectedTripType;
    public $shifts;
    public $shift = False;
    public $selectedShift;
    public $trip_type_name;
    public $trip_ref;
    public $trip_groups;
    public $trip_group_id;
    public $manifest_comments;
    public $volume;
    public $temparature;
    public $net_weight;
    public $seal_number;

    //searching existing trips
    public $searchTrip;
    public $trips;
    public $selectedTrip;
    public $borders;
    public $selectedBorder;
    public $border_inputs = [];
    public $b = 1;
    public $c = 1;

    public function borderAdd($b)
    {
        $b = $b + 1;
        $this->b = $b;
        array_push($this->border_inputs ,$b);
    }

    public function borderRemove($b)
    {
        unset($this->border_inputs[$b]);
    }
    public $clearing_agents;
    public $clearing_agent_id;
    public $cd3_number;
    public $manifest_number;
    public $cd1_number;
    public $selectedStatus;
    public $transporters;
    public $selectedTransporter;
    public $brokers;
    public $selectedBroker;
    public $mode_of_transport;
    //horse vars
    public $all_horses = False;
    public $horses;
    public $selectedHorse;
    public $searchHorse;
    //vehicle vars
    public $all_vehicles = False;
    public $vehicles;
    public $searchVehicle;
    public $selectedVehicle;
    //trailers vars
    public $with_trailer;
    public $all_trailers = False;
    public $searchTrailer;
    public $trailers;
    public $trailer_id;
    public $trailer_inputs = [];
    public $t = 1;
    public $s = 1;
    public function trailerAdd($t)
    {
        $t = $t + 1;
        $this->t = $t;
        array_push($this->trailer_inputs ,$t);
    }
    public function trailerRemove($t)
    {
        unset($this->trailer_inputs[$t]);
    }
    // driver vars
    public $all_drivers = False;
    public $searchDriver;
    public $drivers;
    public $driver_id;
    public $notes;
    //driver allowance vars
    public $allowance_inputs = [];
    public $x = 1;
    public $z = 1;
    public function addAllowance($x)
    {
        $x = $x + 1;
        $this->x = $x;
        array_push($this->allowance_inputs ,$x);
    }
    public function removeAllowance($x)
    {
        unset($this->allowance_inputs[$x]);
    }
    public $allowances;
    public $allowance_id;
    public $allowance_category;
    public $selectedAllowanceCurrency;
    public $allowance_currency_id;
    public $allowance_amount;
    public $allowance_exchange_rate;
    public $allowance_exchange_amount;

    public $allowance;
    public $allowance_title;
    public $allowance_description;

    public $customers;
    public $customer_id;
    public $consignees;
    public $consignee_id;
    // Agent Vars
    public $agents;
    public $agent_id;
    public $commission;
    public $commission_amount;

 
    public $emptyrun_origin;
    public $emptyrun_origin_starting_mileage;
    public $emptyrun_origin_ending_mileage;
    public $emptyrun_origin_distance;
    public $emptyrun_origin_fuel_quantity;
    public $emptyrun_origin_fuel_amount;
    public $emptyrun_origin_currency_id;
    public $emptyrun_destination;
    public $emptyrun_destination_starting_mileage;
    public $emptyrun_destination_ending_mileage;
    public $emptyrun_destination_distance;
    public $emptyrun_destination_fuel_quantity;
    public $emptyrun_destination_currency_id;
    public $emptyrun_destination_fuel_amount;

    // location vars
    public $destinations;
    public $destination_id;
    public $selectedFrom;
    public $loading_points;
    public $loading_point_id;
    public $selectedTo;
    public $offloading_points;
    public $offloading_point_id;
    public $routes;
    public $selectedRoute;
    //truck stop vars
    public $truck_stops;
    public $truck_stop_id;
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

    public $start_date;
    public $end_date;
    public $starting_mileage;
    public $ending_mileage;
    public $starting_hours;
    public $hours;
    public $ending_hours;
    public $distance;
    public $trip_fuel;

    //cargo vars
    public $with_cargos = True;
    public $selectedCargo;
    public $cargos;
    public $cargo;
    public $cargo_type;
    public $cargo_details;
    public $weight;
    public $quantity;
    public $measurements;
    public $measurement;
    public $liquid_measurements;
    public $solid_measurements;
    public $litreage;
    public $litreage_at_20;

    //freight vars
    public $freight_calculation;
    public $calculation_measurement;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $exchange_rate;
    public $exchange_customer_freight;
    public $exchange_transporter_freight;
    
    //rates
    public $with_customer_rates;
    public $defined_customer_rates;
    public $defined_transporter_rates;
    public $selectedDefinedCustomerRate;
    public $rate;
    public $transporter_rate;
    public $freight;
    public $transporter_freight;
    public $transporter_agreement = False;
    public $with_transporter_rates;
    public $selectedDefinedTransporterRate;
    public $comments;

    //trip expenses
    public $trip_expenses = False;
    public $expenses;
    public $expense_id;
    public $category;
    public $expense_currency_id = [];
    public $amount = [];
    public $expense_exchange_rate;
    public $expense_exchange_amount;
    public $fuel_order = False;
    public $exchange_rates;

    //trip timelines
    public $arrive_lp;
    public $depart_lp;
    public $arrive_op;
    public $depart_op;
    public $timelines = False;

    //fuel vars
    public $selectedContainer;
    public $containers;
    public $selected_container;
    public $fuel_category;
    public $date;
    public $selectedFuelCurrency;
    public $selected_fuel_currency;
    public $selected_allowance_currency;
    public $fuel_exchange_rate;
    public $fuel_exchange_amount;
    public $fuel_quantity;
    public $horse_selected;
    public $vehicle_selected;
    public $fuel_tank_capacity;
    public $fuel_balance;
    public $horse_fuel_total;
    public $vehicle_fuel_total;
    public $container_balance;
    public $unit_price = 0;
    public $fuel_amount;
    public $transporter_price = 0;
    public $transporter_total;
    public $odometer;
    public $fuel_comments;
    public $customer_updates = False;
    public $fuel_consumption_loaded_standard;
    public $fuel_consumption_empty_standard;
    public $fuel_profit;


    //Google Maps Api
    public $duration;
  
    public $cost_of_sales = 0;
    public $turnover = 0;
    public $net_profit;
    public $total_transporter_expenses = 0;
    public $total_customer_expenses = 0;
    public $total_expenses = 0;

    public $rank_names;
    public $role_names;
    public $department_names;
    public $company;
    public $employee;
    public $user;
 
    
 

    public function updatedSelectedContainer($id)
    {
        if (!is_null($id) ) {
            $container = Container::find($id);
            $this->selected_container = Container::find($id);
            $top_ups = TopUp::where('container_id',$id)->where('rate','!=', NULL)->where('rate','!=',"")->where('currency_id',$container->currency_id)->get();
            
            $topups_price_total = TopUp::where('container_id',$container->id)->where('rate','!=', NULL)->where('rate','!=',"")->where('rate', 'REGEXP', '^[0-9]+$')->where('currency_id',$container->currency_id)->get()->sum('rate');
            $topups_count = $top_ups->count();
            if ((isset($topups_count) && $topups_count > 0) && (isset($topups_price_total) && $topups_price_total > 0)) {
                $this->unit_price = number_format($topups_price_total/$topups_count,2);
            }
            
            $this->container_balance = $container->balance;
            $this->selectedFuelCurrency = $container->currency_id;
        }
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

    public function updatedFuelOrder(){
        $this->fuel_quantity = $this->trip_fuel;
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
    public function updatedAllTrailers($status){
        if(!is_null($status)){
            if($status == True){
               
                $this->trailers = Trailer::where('archive',0)
                ->orderBy('registration_number','asc')->get();
               
            }else{
              
                $this->trailers = Trailer::where('transporter_id',$this->selectedTransporter)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
              
            }

        }
       
    }

    public function updatedAllDrivers($status){

        if(!is_null($status)){
            if($status == True){
                $this->drivers = Driver::query()->with('employee:id,name,surname')
                ->withAggregate('employee','name')
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();
            }else{
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$this->selectedTransporter)
                ->withAggregate('employee','name')
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();
            }

        }
       
    }

    public function updatedSelectedTrip($id){
        if(!is_null($id)){
            $initial_trip = Trip::find($id);
            if(isset($initial_trip)){
                $this->selectedTransporter = $initial_trip->transporter_id;
                $this->trip_ref = $initial_trip->trip_ref;
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$initial_trip->transporter_id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$initial_trip->transporter_id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->trailers = Trailer::where('transporter_id',$initial_trip->transporter_id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$initial_trip->transporter_id)
                ->withAggregate('employee','name')
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();

                if( $initial_trip->horse_id){
                    $this->mode_of_transport = "Horse";
                    $this->selectedHorse = $initial_trip->horse_id;
                }elseif($initial_trip->vehicle_id){
                    $this->mode_of_transport = "Vehicle";
                    $this->selectedVehicle = $initial_trip->vehicle_id;
                }
              
              
                $initial_trip_trailers = $initial_trip->trailers;
                if($initial_trip_trailers){
                    $this->with_trailer = True;
                    foreach($initial_trip_trailers as $trailer){
                        $this->trailer_id[] = $trailer->id;
                    }
                }
                $this->driver_id = $initial_trip->driver_id;
            }
        }
    }



    public function updatedSelectedRoute($id)
    {
        if (!is_null($id)) {
            $this->truck_stops = TruckStop::where('route_id', $id)->orderBy('name', 'asc')->get();

            $route_expenses = RouteExpense::where('route_id', $id)->get();

            // Reset existing data
            $this->expense_id = [];
            $this->category = [];
            $this->expense_currency_id = [];
            $this->amount = [];
            $this->expense_exchange_rate = [];
            $this->expense_exchange_amount = [];

            if($route_expenses){
                $this->trip_expenses = True;
                foreach ($route_expenses as $route_expense) {
                    $id = $route_expense->expense_id;

                    $this->expense_id[$id] = $id;
                    $this->category[$id] = $route_expense->category;
                    $this->expense_currency_id[$id] = $route_expense->currency_id;
                    $this->amount[$id] = $route_expense->amount;

                    // Optional: handle exchange rate & converted amount if relevant
                    if ($route_expense->currency_id != ($this->company->currency_id ?? null)) {
                        $this->expense_exchange_rate[$id] = $route_expense->exchange_rate;
                        $this->expense_exchange_amount[$id] = $route_expense->exchange_amount;
                    }
                }
            }
           
        }
    }


    public function updatedSelectedCargo($id)
    {
            if (!is_null($id)) {
                $this->cargo = Cargo::find($id);
                $this->cargo_type = $this->cargo->type;
                if(isset($this->cargo_type)){
                    if($this->cargo_type == "Solid"){
                        $this->calculation_measurement = "weight";
                    }elseif($this->cargo_type == "Liquid"){
                        $this->calculation_measurement = "litreage_at_20";
                    }
                }
            }
    }
    public function updatedSelectedTripType($id)
    {
            if (!is_null($id)) {
                $this->trip_type_name = TripType::find($id)->name;
               
                if(isset($this->trip_type_name) && $this->trip_type_name === "Return"){

                    $this->trips = Trip::select('id', 'trip_number', 'trip_ref', 'customer_id', 'horse_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'horse:id,registration_number',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->orderBy('start_date', 'desc')
                    ->get();
                }
            }
    }


    public function updatedSelectedHorse($id)
    {
        if (!is_null($id) ) {
            $horse = Horse::find($id);
            $this->horse_selected = Horse::find($id);
           
            $assignment = Assignment::where('horse_id',$id)
                                    ->where('status', 1)->first();

            if($horse){
                 $trailer_assignments = $horse->trailer_assignments->where('status',1);
                $this->odometer = $horse->mileage;
                $this->fuel_tank_capacity = $horse->fuel_tank_capacity;
                $this->starting_mileage = $horse->mileage;
                $this->starting_hours = $horse->hours;
                $this->fuel_consumption_loaded_standard = $horse->fuel_consumption_loaded_standard;
                $this->fuel_consumption_empty_standard = $horse->fuel_consumption_empty_standard;
                $this->fuel_balance = $horse->fuel_balance;
                
                if ($assignment instanceof Assignment) {
                    $driver = $assignment->driver;
                    $this->driver_id = $driver?->id;
                } 
                                
                if (isset( $trailer_assignments) && $trailer_assignments->count()> 0) {
                    $this->with_trailer = True;
                    foreach ($trailer_assignments as $trailer_assignment) {
                        $this->trailer_id[] = $trailer_assignment->trailer_id;
                    }
                    
                }   
            }
           
                                    
                          
           
        }
    }
    public function updatedSelectedVehicle($id)
    {
        if (!is_null($id) ) {
            $vehicle = Vehicle::find($id);
            $this->vehicle_selected = Vehicle::find($id);
            $this->selectedVehicle= $id;
           
            $assignment = VehicleAssignment::where('vehicle_id',$id)
                                    ->where('status', 1)->first();
                                    
            if($vehicle){
                $this->odometer = $vehicle->mileage;
                $this->fuel_tank_capacity = $vehicle->fuel_tank_capacity;
                $this->starting_mileage = $vehicle->mileage;
                $this->starting_hours = $vehicle->hours;
                $this->fuel_consumption_loaded_standard = $vehicle->fuel_consumption_loaded_standard;
                $this->fuel_consumption_empty_standard = $vehicle->fuel_consumption_empty_standard;
                $this->fuel_balance = $vehicle->fuel_balance;
                
                if ($assignment instanceof Assignment) {
                    $employee = $assignment->employee;
                    $driver = $employee?->driver;
                    $this->driver_id = $driver?->id;
                } 
            }                       
           
        }
    }

    public function updatedSelectedQuotation($id){
            if (!is_null($id)) {
              $quotation = Quotation::find($id);
              if (isset($quotation)) {
                $this->customer_id = $quotation->customer ? $quotation->customer->id : "";
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
                $this->trailers = Trailer::where('transporter_id',$id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$id)
                ->withAggregate('employee','name')
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();
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
                $this->trailers = Trailer::where('transporter_id',$id)
                ->where('status', 1)
                ->where('service',0)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$id)
                ->withAggregate('employee','name')
                ->where('status', 1)
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();
            }
     
          
        }
    }

    public function updatedSelectedShift($id){

        if(!is_null($id)){
            $shift = Shift::find($id);
            if($shift){
               
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$shift->transporter_id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$shift->transporter_id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->trailers = Trailer::where('transporter_id',$shift->transporter_id)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$shift->transporter_id)
                ->withAggregate('employee','name')
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();

                $transporter = Transporter::find($shift->transporter_id);
                $this->cargos = $transporter->cargos->sortBy('name');
                $this->selectedStatus = "Offloaded";
                $trip_type = TripType::where('name','Local')->first();
                $this->selectedTripType = $trip_type ? $trip_type->id : Null;
                $this->trip_type_name =  $trip_type->name;
                $this->with_trailer = True;
                $this->timelines = True;

                $this->selectedTransporter = $shift->transporter_id;
                if($shift->horse_id){
                    $horse = $shift->horse;
                    $cluster = $horse->cluster;
                    if($cluster){
                        foreach($cluster->trailers as $trailer){
                            $this->trailer_id[] = $trailer->id;
                        }
                    }
                    $this->selectedHorse = $shift->horse_id;
                    $this->mode_of_transport = "Horse";
                }elseif($shift->vehicle_id){
                    $this->mode_of_transport = "Vehicle";
                    $this->selectedHorse = $shift->vehicle_id;
                }
                $this->driver_id = $shift->driver_id;
              
                $this->selectedCurrency = $shift->currency_id;
                $this->start_date = Carbon::parse($shift->date . ' ' . $shift->shift_start_time)
                    ->format('Y-m-d\TH:i');

                $this->end_date = Carbon::parse($shift->date . ' ' . $shift->shift_end_time)
                    ->format('Y-m-d\TH:i');
                $this->customer_id = $shift->customer_id;
                $this->selectedCargo = $shift->cargo_id;
            }
           

        }
       
    }

    public function updatedSelectedBroker($id)
    {
        if (!isset($this->selectedTransporter)) {
            if (!is_null($id) ) {
                $broker = Broker::find($id);
                if(isset($broker)){
                    $this->cargos = $broker->cargos->sortBy('name');
                    if (isset($this->selectedStatus) && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
                        $this->horses = $broker->horses->where('archive',0);
                        $this->trailers = $broker->trailers->where('archive',0);
                        $this->drivers = $broker->drivers->where('archive',0);
                    }else{
                        $this->horses = $broker->horses
                        ->where('status', 1)
                        ->where('archive',0)
                        ->where('service',0);
                        $this->trailers = $broker->trailers
                        ->where('status', 1)
                        ->where('archive',0)
                        ->where('service',0);
                        $this->drivers = $broker->drivers
                        ->where('status', 1)
                        ->where('archive',0);
                    }
                }
               
               
            }
        }

    }

   

    public function updatedExpenseExchangeRate($value, $key){
        if ((isset($this->expense_exchange_rate[$key]) && $this->expense_exchange_rate[$key] > 0) && (isset($this->amount[$key]) && $this->amount[$key] > 0) ) {
            $this->expense_exchange_amount[$key] = $this->expense_exchange_rate[$key] * $this->amount[$key];
        }
    }
    public function updatedAmount($value, $key){
        if ((isset($this->expense_exchange_rate[$key]) && $this->expense_exchange_rate[$key] > 0) && (isset($this->amount[$key]) && $this->amount[$key] > 0) ) {
            $this->expense_exchange_amount[$key] = $this->expense_exchange_rate[$key] * $this->amount[$key];
        }
    }
    public function updatedExpenseExchangeAmount($value, $key){
        if ((isset($this->expense_exchange_rate[$key]) && $this->expense_exchange_rate[$key] > 0) && (isset($this->amount[$key]) && $this->amount[$key] > 0) ) {
            $this->expense_exchange_amount[$key] = $this->expense_exchange_rate[$key] * $this->amount[$key];
        }
    }
    
    

    public function updatedAllowanceAmount($value, $key){
        if ((isset($this->allowance_exchange_rate[$key]) && $this->allowance_exchange_rate[$key] > 0) && (isset($this->allowance_amount[$key]) && $this->allowance_amount[$key] > 0) ) {
            $this->allowance_exchange_amount[$key] = $this->allowance_exchange_rate[$key] * $this->allowance_amount[$key];
        }
    }
    public function updatedAllowanceExchangeRate($value, $key){
        if ((isset($this->allowance_exchange_rate[$key]) && $this->allowance_exchange_rate[$key] > 0) && (isset($this->allowance_amount[$key]) && $this->allowance_amount[$key] > 0) ) {
            $this->allowance_exchange_amount[$key] = $this->allowance_exchange_rate[$key] * $this->allowance_amount[$key];
        }
    }
    public function updatedAllowanceExchangeAmount($value, $key){
        if ((isset($this->allowance_exchange_rate[$key]) && $this->allowance_exchange_rate[$key] > 0) && (isset($this->allowance_amount[$key]) && $this->allowance_amount[$key] > 0) ) {
            $this->allowance_exchange_amount[$key] = $this->allowance_exchange_rate[$key] * $this->allowance_amount[$key];
        }
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

    public function manifestNumber(){

        $trip = Trip::orderBy('id','desc')->first();

        if (!$trip) {
            $manifest_number =  'MAN'. str_pad(1, 5, "0", STR_PAD_LEFT)."/".Carbon::now()->format('dmY');
        }else {
            $number = $trip->id + 1;
            $manifest_number =  'MAN'. str_pad($number, 5, "0", STR_PAD_LEFT)."/".Carbon::now()->format('dmY');
        }

        return  $manifest_number;

    }

    private function resetInputFields(){
        $this->allowance_title = '';
        $this->allowance_currency_id = '';
    }

    public function calculateFuelConsumption($id)
    {
        $trip = Trip::find($id);
        if (!$trip) return;

        $fuels = $trip->fuels;

        $distance = null;
        if (is_numeric($this->starting_mileage) && is_numeric($this->ending_mileage)) {
            $distance = $this->ending_mileage - $this->starting_mileage;
        } else {
            $distance = $this->distance ?? 0;
        }

        $hours_distance = null;
        if (is_numeric($this->starting_hours) && is_numeric($this->ending_hours)) {
            $hours_distance = $this->ending_hours - $this->starting_hours;
        }

        $total_fuel = $fuels && $fuels->count() > 0 
            ? $fuels->sum('quantity') 
            : ($this->trip_fuel ?? 0);

        if (is_numeric($distance) && $distance > 0 && $total_fuel > 0) {
            $trip->fuel_consumption_mileage = $distance / $total_fuel;
        }

        if (is_numeric($hours_distance) && $hours_distance > 0 && $total_fuel > 0) {
            $trip->fuel_consumption_hours = $hours_distance / $total_fuel;
        }

        $trip->save();
    }
   
    
   
    public function storeAllowance(){

        $allowance = new Allowance;
        $allowance->user_id =  $this->user->id;
        $allowance->name = $this->allowance_title;
        $allowance->currency_id = $this->allowance_currency_id;
        $allowance->amount = $this->allowance_amount;
        $allowance->description = $this->allowance_description;
        $allowance->save();

        $this->allowance_title = $allowance->name;

        $this->dispatchBrowserEvent('hide-allowanceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Allowance Created Successfully Successfully!!"
        ]);
        
    }




    public function mount(){
        $this->freight_calculation = 'flat_rate';
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->manifest_number =  $this->manifestNumber();
        $this->company = Company::with('currency')->find($this->employee->company_id);
        $this->exchange_rates = ExchangeRate::all();
        $this->shifts = Shift::where('for','Trips')->where('status','1')->latest()->get();
        $this->liquid_measurements = Measurement::where('cargo_type','Liquid')->orderBy('name','asc')->get();
        $this->solid_measurements = Measurement::where('cargo_type','Solid')->orderBy('name','asc')->get();  
        $this->emptyrun_destination = False;
        $this->emptyrun_origin = False;
        $this->transporters = Transporter::with('vehicles:id,registration_number','vehicles.vehicle_make:id,name','vehicles.vehicle_model:id,name','horses:id,registration_number','horses.horse_make:id,name','horses.horse_model:id,name','cargos:id,name','trailers:id,registration_number,make,model','drivers:id','drivers.employee:id,name,surname')->where('authorization','approved')->orderBy('name','asc')->get();
        $this->containers = Container::orderBy('name','asc')->latest()->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->trip_groups = TripGroup::where('status',1)->latest()->get();
        $this->defined_customer_rates = Rate::where('category','Customer')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
        $this->defined_transporter_rates = Rate::where('category','Transporter')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
        $this->allowances = Allowance::orderBy('name','asc')->get();
        $this->routes = Route::with('truck_stops:id,name')->where('status',1)->orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->truck_stops = collect();
        $this->with_customer_rates = "custom";
        $this->with_transporter_rates = "custom";
        $this->horses =  collect();
        $this->vehicles =  collect();
        $this->borders = Border::with('clearing_agents:id,name')->orderBy('name','asc')->get();
        $this->clearing_agents = ClearingAgent::orderBy('name','asc')->get();
        $this->trailers = collect();
        $this->drivers = collect();
        $this->rate = 0;
        $this->freight = 0;
        $this->transporter_rate = 0;
        $this->transporter_freight = 0;
        $this->agents = Agent::orderBy('name','asc')->get();
        $this->trip_types = TripType::orderBy('name','asc')->get();
        $this->account = Account::with('expenses')->where('name','Trip Expense')->first();
        $this->expenses = Expense::whereHas('account', function($q){
            $q->where('name', 'Trip Expense');
         })->orderBy('name','asc')->get();

        

        foreach($this->expenses as $expense){
                $id = $expense->id;
                $this->category[$id] = $expense->category;
                $this->expense_currency_id[$id] = $expense->currency_id;
                $this->amount[$id] = $expense->amount;
                // Optional: handle exchange rate & converted amount if relevant
                if ($expense->currency_id != ($this->company->currency_id ?? null)) {
                    $this->expense_exchange_rate[$id] = $expense->exchange_rate;
                    $this->expense_exchange_amount[$id] = $expense->exchange_amount;
                }
         }

       

         $departments = $this->employee->departments;
         foreach($departments as $department){
             $this->department_names[] = $department->name;
         }
         $roles = Auth::user()->roles;
         foreach($roles as $role){
             $this->role_names[] = $role->name;
         }
         $ranks = $this->employee->ranks;
         foreach($ranks as $rank){
             $this->rank_names[] = $rank->name;
         }

        $this->consignees = Consignee::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->brokers = Broker::orderBy('name','asc')->latest()->get();
        $this->cargos = collect();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
      }


      public function updated($value){
          $this->validateOnly($value);
      }

      protected $messages =[
        'customer_id.required' => 'Please select a customer',
        'selectedHorse.required' => 'Please select a horse',
        'selectedVehicle.required' => 'Please select a vehicle',
        'selectedTransporter.required' => 'Please select a transporter',
        'driver_id.required' => 'Please select a driver',
        'trailer_id.0.required' => 'Please select a driver',
        'trailer_id.*.required' => 'Please select a driver',
        'selectedCargo.required' => 'Please select your cargo',
        'selectedTo.required' => 'Please select a starting point ',
        'selectedFrom.required' => 'Please select your destination ',

    ];
      protected $rules = [
          'customer_id' => 'required',
          'selectedHorse' => 'nullable',
          'selectedVehicle' => 'nullable',
          'selectedTransporter' => 'required',
          'driver_id' => 'required',
          'selectedTripType' => 'required',
          'selectedCargo' => 'required',
          'selectedCurrency' => 'required',
          'selectedFrom' => 'required',
          'selectedTo' => 'required',
          'litreage_at_20' => 'nullable',
          'weight' => 'nullable',
          'freight' => 'required',
          'start_date' => 'required',
          'manifest_number' => 'nullable|unique:trips,manifest_number,NULL,id,deleted_at,NULL|string|min:2',
          'cd3_number' => 'nullable|unique:trips,cd3_number,NULL,id,deleted_at,NULL|string|min:2',
          'cd1_number' => 'nullable|unique:trips,cd1_number,NULL,id,deleted_at,NULL|string|min:2',
          'selectedStatus' => 'required',
          'selectedContainer' => 'required',
          'expense_id.*' => 'nullable|exists:expenses,id',
          'start_date' => 'required',
         
      ];

     

      public function updatedSelectedDefinedCustomerRate($id){
            if(!is_null($id)){
                $defined_customer_rate = Rate::find($id);
                $this->rate = $defined_customer_rate->rate;
                $this->freight = $defined_customer_rate->freight;
                $this->weight = $defined_customer_rate->weight;
                $this->litreage = $defined_customer_rate->litreage;
                $this->distance = $defined_customer_rate->distance;
                $this->selectedFrom = $defined_customer_rate->from;
                $this->selectedTo = $defined_customer_rate->to;
                $this->loading_point_id = $defined_customer_rate->loading_point_id;
                $this->offloading_point_id = $defined_customer_rate->offloading_point_id;
                $this->selectedCurrency = $defined_customer_rate->currency_id;
            }
      }
      public function updatedSelectedDefinedTransporterRate($id){
        if(!is_null($id)){
            $defined_transporter_rate = Rate::find($id);
            $this->transporter_rate = $defined_transporter_rate->rate;
            $this->transporter_freight = $defined_transporter_rate->freight;
        }
      }


      private function addToCategoryTotal($category, $amount)
    {
        if (is_numeric($amount)) {
            switch ($category) {
                case 'Transporter':
                    $this->total_transporter_expenses += $amount;
                    break;
                case 'Customer':
                    $this->total_customer_expenses += $amount;
                    break;
                case 'Self':
                    $this->total_expenses += $amount;
                    break;
            }
        }
    }

    private function getBaseAmount($amount, $exchangeAmount, $currency)
    {
        return ($currency == $this->company->currency_id) ? $amount : $exchangeAmount;
    }

    private function recalculateExpenses($trip_id){

        $trip = Trip::find($trip_id);
        $this->trip_expenses = TripExpense::where('trip_id', $trip_id)->get();
        
        $this->total_transporter_expenses = 0;
        $this->total_customer_expenses = 0;
        $this->total_expenses = 0;
        
        if ($this->trip_expenses->isNotEmpty()) {
            foreach ($this->trip_expenses as $expense) {
                $use_amount = ($expense->currency_id == Auth::user()->employee->company->currency_id) 
                    ? $expense->amount 
                    : $expense->exchange_amount;
        
                if (is_numeric($use_amount)) {
                    switch ($expense->category) {
                        case 'Transporter':
                            $this->total_transporter_expenses += $use_amount;
                            break;
                        case 'Customer':
                            $this->total_customer_expenses += $use_amount;
                            break;
                        case 'Self':
                            $this->total_expenses += $use_amount;
                            break;
                    }
                }
            }
        }
        
        $this->cost_of_sales = $this->total_expenses;
        $trip->cost_of_sales = $this->cost_of_sales;
        $this->turnover = $trip->turnover;
        
        if ($this->cost_of_sales > 0 && $this->turnover > 0) {
            $this->net_profit = $this->turnover - $this->cost_of_sales;
            $trip->net_profit = $this->net_profit;
        
            if ($this->net_profit > 0) {
                $trip->markup_percentage = ($this->net_profit / $this->cost_of_sales) * 100;
                $trip->net_profit_percentage = ($this->net_profit / $this->turnover) * 100;
            } else {
                $trip->markup_percentage = 0;
                $trip->net_profit_percentage = 0;
            }
        } else {
            $trip->net_profit_percentage = 100;
            $trip->markup_percentage = 100;
        }
        
        $trip->update();

      
    }

    private function saveEmptyRun($trip, $isOrigin = true)
    {
        $emptyrun = new EmptyRun;
        $emptyrun->trip_id = $trip->id;

        if ($isOrigin) {
            $emptyrun->emptyrun_origin = true;
            $emptyrun->distance = $this->emptyrun_origin_distance;
            $emptyrun->starting_mileage = $this->emptyrun_origin_starting_mileage;
            $emptyrun->ending_mileage = $this->emptyrun_origin_ending_mileage;
            $emptyrun->currency_id = $this->emptyrun_origin_currency_id;
            $emptyrun->fuel_quantity = $this->emptyrun_origin_fuel_quantity;
            $emptyrun->fuel_amount = $this->emptyrun_origin_fuel_amount;
        } else {
            $emptyrun->emptyrun_destination = true;
            $emptyrun->distance = $this->emptyrun_destination_distance;
            $emptyrun->starting_mileage = $this->emptyrun_destination_starting_mileage;
            $emptyrun->ending_mileage = $this->emptyrun_destination_ending_mileage;
            $emptyrun->currency_id = $this->emptyrun_destination_currency_id;
            $emptyrun->fuel_quantity = $this->emptyrun_destination_fuel_quantity;
            $emptyrun->fuel_amount = $this->emptyrun_destination_fuel_amount;
        }

        $emptyrun->save();
    }


    private function syncRelations($trip)
    {
        if ($this->with_trailer && !empty($this->trailer_id)) {
            $trip->trailers()->sync($this->trailer_id);
        }
        if (!empty($this->selectedBorder)) {
            $trip->borders()->sync($this->selectedBorder);
        }
        if (!empty($this->clearing_agent_id)) {
            $trip->clearing_agents()->sync($this->clearing_agent_id);
        }
        if (!empty($this->truck_stop_id)) {
            $trip->truck_stops()->sync($this->truck_stop_id);
        }
    }


    private function resetAssetStatus($horse_id = null, $vehicle_id = null, $driver_id = null, $trailer_ids = [])
    {
        if (isset($horse_id) && !empty($horse_id)) {
            Horse::withTrashed()->where('id', $horse_id)->update(['status' => 1]);
        }

        if (isset($vehicle_id) && !empty($vehicle_id)) {
            Vehicle::withTrashed()->where('id', $vehicle_id)->update(['status' => 1]);
        }

        if (isset($driver_id) && !empty($driver_id)) {
            Driver::withTrashed()->where('id', $driver_id)->update(['status' => 1]);
        }

        if (!empty($trailer_ids)) {
            foreach ($trailer_ids as $trailer_id) {
                if (isset($trailer_id) && !empty($trailer_id)) {
                    Trailer::withTrashed()->where('id', $trailer_id)->update(['status' => 1]);
                }
            }
        }
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

    public function store(){

        // $this->validate();
        //start trip creation logic
        // try{
        DB::transaction(function () {

                $trip = new Trip;
                $trip->trip_number = $this->tripNumber();
                $trip->trip_ref = $this->trip_ref;
                $trip->user_id =  $this->user->id;
                $trip->company_id = $this->company->id;
                $trip->horse_id = $this->mode_of_transport === "Horse" ? $this->selectedHorse : null;
                $trip->vehicle_id = $this->mode_of_transport === "Vehicle" ? $this->selectedVehicle : null;
                $trip->transporter_id = $this->selectedTransporter;
                $trip->trip_group_id = $this->trip_group_id ?: null;
                $trip->agent_id = $this->agent_id ?: null;
                $trip->customer_updates = $this->customer_updates;
                $trip->transporter_agreement = $this->transporter_agreement;
                $trip->manifest_comments = $this->manifest_comments;
                $trip->volume = $this->volume;
                $trip->temparature = $this->temparature;
                $trip->net_weight = $this->net_weight;
                $trip->seal_number = $this->seal_number;
                $trip->fuel_order = $this->fuel_order;
                $trip->driver_id = $this->driver_id ?: null;
                $trip->with_customer_rates = $this->with_customer_rates;
                $trip->with_transporter_rates = $this->with_transporter_rates;
                $trip->broker_id = $this->selectedBroker;
                $trip->initial_trip_id = $this->trip_type_name === "Return" ? $this->selectedTrip : null;
                $trip->customer_id = $this->customer_id;
                $trip->consignee_id = $this->consignee_id ?: null;
                $trip->freight_calculation = $this->freight_calculation;
                $trip->calculation_measurement = $this->calculation_measurement;
                $trip->currency_id = $this->selectedCurrency;
                $trip->cd3_number = $this->cd3_number;
                $trip->shift_id = $this->selectedShift;
                $trip->shift = $this->shift;
                $trip->notes = $this->notes;
                $trip->arrive_loading_point = $this->arrive_lp;
                $trip->depart_loading_point = $this->depart_lp;
                $trip->loading_time = $this->calculateTimeDifference($this->arrive_lp, $this->depart_lp);
                $trip->arrive_offloading_point = $this->arrive_op;
                $trip->depart_offloading_point = $this->depart_op;
                $trip->offloading_time = $this->calculateTimeDifference($this->arrive_op, $this->depart_op);
                $trip->cd1_number = $this->cd1_number;
                $trip->manifest_number = $this->manifest_number;
                $trip->cargo_id = $this->selectedCargo;
                $trip->trip_type_id = $this->selectedTripType;
                $trip->haulage_type = $this->haulage_type;
                $trip->starting_mileage = $this->starting_mileage;
                $trip->starting_hours = $this->starting_hours;
                $trip->ending_mileage = $this->ending_mileage;
                $trip->ending_hours = $this->ending_hours;
                $trip->trip_fuel = $this->trip_fuel;
                $trip->defined_customer_rate_id = $this->selectedDefinedCustomerRate;
                $trip->defined_transporter_rate_id = $this->selectedDefinedTransporterRate;
                $trip->from = $this->selectedFrom;
                $trip->to = $this->selectedTo;
                $trip->offloading_point_id = $this->offloading_point_id;
                $trip->loading_point_id = $this->loading_point_id;
                $trip->start_date = $this->start_date;
                $trip->cargo_details = $this->cargo_details;
                $trip->with_cargos = $this->with_cargos;
                $trip->end_date = $this->end_date;
                $trip->rate = $this->rate;
                $trip->transporter_rate = $this->transporter_rate;
                $trip->quantity = $this->quantity;
                $trip->litreage = $this->litreage;
                $trip->litreage_at_20 = $this->litreage_at_20;
                $trip->measurement = $this->measurement;
                $trip->weight = $this->weight;
                $trip->freight = $this->freight;
                $trip->transporter_freight = $this->transporter_freight;
                $trip->exchange_rate = $this->exchange_rate;
                $trip->exchange_customer_freight = $this->exchange_customer_freight;
                $trip->exchange_transporter_freight = $this->exchange_transporter_freight;
                $trip->turnover = $this->freight;
                $this->turnover = $this->freight;
               
                $trip->trip_status = $this->selectedStatus;
                $trip->trip_status_date = $this->start_date;
                $trip->route_id = $this->selectedRoute;
                $trip->distance = $this->distance;
                $trip->comments = $this->comments;
                $trip->emptyrun_origin = $this->emptyrun_origin;
                $trip->emptyrun_destination = $this->emptyrun_destination;
                $trip->save();

                $this->calculateFuelConsumption($trip->id);
                $this->syncRelations($trip);

                if($this->emptyrun_origin) $this->saveEmptyRun($trip, true);
                if($this->emptyrun_destination) $this->saveEmptyRun($trip, false);

     
                $last_mileage = Mileage::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
      
                if(isset($this->starting_mileage)){
                    if(isset($last_mileage)){
                        if($last_mileage->mileage < $this->ending_mileage){
                            $mileage = new Mileage;
                            $mileage->user_id = $this->user->id;
                            $mileage->trip_id = $trip->id;
                            $mileage->horse_id = $this->selectedHorse ?:  Null;
                            $mileage->vehicle_id = $this->selectedVehicle ?: Null;
                            $mileage->mileage = $this->starting_mileage;
                            $mileage->date = $this->start_date;
                            $mileage->category = "Trip";
                            $mileage->position = "starting";
                            $mileage->save();
                        }
                    }else{
                        $mileage = new Mileage;
                        $mileage->user_id = $this->user->id;
                        $mileage->trip_id = $trip->id;
                        $mileage->horse_id = $this->selectedHorse ?:  Null;
                        $mileage->vehicle_id = $this->selectedVehicle ?: Null;
                        $mileage->mileage = $this->starting_mileage;
                        $mileage->date = $this->start_date;
                        $mileage->category = "Trip";
                        $mileage->position = "starting";
                        $mileage->save();
                    }          
                }
             
                $last_hours = Hour::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
      
                if(isset($this->starting_hours)){
                    if(isset($last_hours)){
                        if($last_hours->hour < $this->ending_hours){
                            $hours = new Hour;
                            $hours->user_id = $this->user->id;
                            $hours->trip_id = $trip->id;
                            $hours->horse_id = $this->selectedHorse ?:  Null;
                            $hours->vehicle_id = $this->selectedVehicle ?: Null;
                            $hours->hours = $this->starting_hours;
                            $hours->date = $this->start_date;
                            $hours->category = "Trip";
                            $hours->position = "starting";
                            $hours->save();
                        }
                    }else{
                        $hours = new Hour;
                        $hours->user_id = $this->user->id;
                        $hours->trip_id = $trip->id;
                        $hours->horse_id = $this->selectedHorse ?:  Null;
                        $hours->vehicle_id = $this->selectedVehicle ?: Null;
                        $hours->hours = $this->starting_hours;
                        $hours->date = $this->start_date;
                        $hours->category = "Trip";
                        $hours->position = "starting";
                        $hours->save();
                    }          
                }
             
          

                $last_mileage = Mileage::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
                if(isset($this->ending_mileage)){
                    if(isset($last_mileage)){
                        if($last_mileage->mileage < $this->ending_mileage){
                            $mileage = new Mileage;
                            $mileage->user_id = $this->user->id;
                            $mileage->trip_id = $trip->id;
                            $mileage->horse_id = $this->selectedHorse ?:  Null;
                            $mileage->vehicle_id = $this->selectedVehicle ?: Null;
                            $mileage->mileage = $this->ending_mileage;
                            $mileage->date = $this->end_date;
                            $mileage->category = "Trip";
                            $mileage->position = "ending";
                            $mileage->save();
                        }
                    }else{
                        $mileage = new Mileage;
                        $mileage->user_id = $this->user->id;
                        $mileage->trip_id = $trip->id;
                        $mileage->horse_id = $this->selectedHorse ?:  Null;
                        $mileage->vehicle_id = $this->selectedVehicle ?: Null;
                        $mileage->mileage = $this->ending_mileage;
                        $mileage->date = $this->end_date;
                        $mileage->category = "Trip";
                        $mileage->position = "ending";
                        $mileage->save();
                    }
                }
            
                $last_hours = Hour::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
                if(isset($this->ending_hours)){
                    if(isset($last_hours)){
                        if($last_hours->hours < $this->ending_hours){
                            $hours = new Hour;
                            $hours->user_id = $this->user->id;
                            $hours->trip_id = $trip->id;
                            $hours->horse_id = $this->selectedHorse ?:  Null;
                            $hours->vehicle_id = $this->selectedVehicle ?: Null;
                            $hours->hours = $this->ending_hours;
                            $hours->date = $this->end_date;
                            $hours->category = "Trip";
                            $hours->position = "ending";
                            $hours->save();
                        }
                    }else{
                        $hours = new Hour;
                        $hours->user_id = $this->user->id;
                        $hours->trip_id = $trip->id;
                        $hours->horse_id = $this->selectedHorse ?:  Null;
                        $hours->vehicle_id = $this->selectedVehicle ?: Null;
                        $hours->hours = $this->ending_hours;
                        $hours->date = $this->end_date;
                        $hours->category = "Trip";
                        $hours->position = "ending";
                        $hours->save();
                    }
                }

   

                if (in_array($this->selectedStatus, ["Offloaded", "Cancelled", "Scheduled"])) {

                $this->resetAssetStatus($trip->horse_id, $trip->vehicle_id, $trip->driver_id,  $trip->trailers->pluck('id')->toArray());
                    
                    $breakdown_assignments = $trip->breakdown_assignments;
                    if ($breakdown_assignments->count()>0) {
                    
                        foreach ($trip->breakdown_assignments as $breakdown_assignment) {
                            $this->resetAssetStatus(
                                $breakdown_assignment->horse_id,
                                $breakdown_assignment->vehicle_id,
                                $breakdown_assignment->driver_id,
                                $breakdown_assignment->trailers->pluck('id')->toArray()
                            );
                        }
                        # code...
                    }
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
                $delivery_note->transporter_loaded_rate = $trip->transporter_rate;
                $delivery_note->transporter_loaded_freight = $trip->transporter_freight;
                $delivery_note->save();
                


                if (isset($this->agent_id) && !empty($this->agent_id)) {
                    $commission = new Commission;
                    $commission->user_id =   $this->user->id ;
                    $commission->trip_id =  $trip->id ;
                    $commission->agent_id =  $this->agent_id ;
                    $commission->commission =  $this->commission ;
                    $commission->amount =  $this->commission_amount ;
                    $commission->date =  $this->start_date ;
                    $commission->status =  1 ;
                    $commission->save();
                }
     

                if(isset($this->driver_id) && !empty($this->driver_id)){
                    if (isset($this->allowance_id) && !empty($this->allowance_id)) {

                        foreach ($this->allowance_id as $key => $value) {

                            $allowance_driver = new AllowanceDriver;
                            $allowance_driver->driver_id = $this->driver_id ? $this->driver_id : Null;
                            $allowance_driver->trip_id = $trip->id ? $trip->id : Null;
                            if (isset($this->allowance_id[$key])) {
                            $allowance_driver->allowance_id = $this->allowance_id[$key] ? $this->allowance_id[$key] : Null;                    
                            }
                            if (isset($this->selectedAllowanceCurrency[$key])) {
                            $allowance_driver->currency_id = $this->selectedAllowanceCurrency[$key] ? $this->selectedAllowanceCurrency[$key] : Null;                    
                            }
                            if (isset($this->allowance_amount[$key])) {
                            $allowance_driver->amount = $this->allowance_amount[$key] ? $this->allowance_amount[$key] : Null;                    
                            }
                            $allowance_driver->save();

                            
                            $trip_expense = new TripExpense();
                            $trip_expense->user_id = $this->user->id;
                            $trip_expense->trip_id = $trip->id;
                            $trip_expense->allowance_id = $this->allowance_id[$key] ?? null;
                            $trip_expense->currency_id = $this->selectedAllowanceCurrency[$key] ?? null;
                            $trip_expense->category = $this->allowance_category[$key] ?? null;
                            $trip_expense->amount = $this->allowance_amount[$key] ?? null;
                            $trip_expense->exchange_rate = $this->allowance_exchange_rate[$key] ?? null;
                            $trip_expense->exchange_amount = $this->allowance_exchange_amount[$key] ?? null;
                            
                            // Handle totals
                            $category = $this->allowance_category[$key] ?? null;
                            $currency_id = $this->selectedAllowanceCurrency[$key] ?? null;
                            $amount = $this->allowance_amount[$key] ?? 0;
                            $exchange_amount = $this->allowance_exchange_amount[$key] ?? 0;
                            

                            $this->addToCategoryTotal(
                                $category,
                                $amount = ($currency_id == $this->company->currency_id) ? $amount : $exchange_amount
                            );
        
                            $trip_expense->save();
                        
                        }
                        
                    }
                }
  
                if($this->shift == False){
                    if ($this->fuel_order) {
                        $fuel_fillup = $trip->fuels->where('fillup', 1)->first();
                        $container = Container::find($this->selectedContainer);
                    
                        $fuel = new Fuel;
                        $fuel->user_id = $this->user->id;
                        $fuel->order_number = $this->orderNumber();
                        $fuel->horse_id = $this->selectedHorse ?? null;
                        $fuel->vehicle_id = $this->selectedVehicle ?? null;
                        $fuel->currency_id = $this->selectedFuelCurrency;
                        $fuel->trip_id = $trip->id;
                        $fuel->type = isset($this->selectedVehicle) ? "Vehicle" : (isset($this->selectedHorse) ? "Horse" : null);
                        $fuel->driver_id = $this->driver_id ?? null;
                        $fuel->container_id = $this->selectedContainer ?? null;
                        $fuel->date = $this->date;
                        $fuel->unit_price = $this->unit_price;
                        $fuel->quantity = $this->fuel_quantity;
                        $fuel->amount = $this->fuel_amount;
                        $fuel->transporter_price = $this->transporter_price;
                        $fuel->transporter_total = $this->transporter_total;
                        $fuel->hours = $this->fuel_profit;
                        $fuel->odometer = $this->odometer;
                        $fuel->odometer = $this->hours;
                        $fuel->category = $this->fuel_category;
                        $fuel->exchange_amount = $this->fuel_exchange_amount;
                        $fuel->exchange_rate = $this->fuel_exchange_rate;
                        $fuel->fillup = 1;
                        $fuel->status = 1;
                        $fuel->comments = $this->fuel_comments;
                        $fuel->save();
                    
                        $fuel_expense = Expense::where('name', 'Fuel Topup')->first();
                    
                        $trip_expense = new TripExpense;
                        $trip_expense->user_id = $this->user->id;
                        $trip_expense->trip_id = $trip->id;
                        $trip_expense->fuel_id = $fuel->id;
                        $trip_expense->expense_id = $fuel_expense->id ?? null;
                        $trip_expense->currency_id = $this->selectedFuelCurrency;
                        $trip_expense->category = $this->fuel_category;
                        $trip_expense->amount = $this->fuel_amount;
                        $trip_expense->exchange_rate = $this->fuel_exchange_rate;
                        $trip_expense->exchange_amount = $this->fuel_exchange_amount;

                        $category = $this->fuel_category ?? null;
                        $currency_id = $this->selectedFuelCurrency ?? null;
                        $amount =  $this->fuel_amount ?? 0;
                        $exchange_amount = $this->fuel_exchange_amount ?? 0;
                    
                        // Add fuel to totals
                        $this->addToCategoryTotal(
                            $category,
                            $amount = ($currency_id == $this->company->currency_id) ? $amount : $exchange_amount
                        );
                    
                        $trip_expense->save();
                    }
                }
                

                if ($this->trip_expenses && $this->expense_id) {
                    foreach ($this->expense_id as $key => $value) {
                
                        $trip_expense = new TripExpense;
                        $trip_expense->user_id = $this->user->id;
                        $trip_expense->trip_id = $trip->id;
                        $expenseId = $this->expense_id[$key] ?? null;
                        $trip_expense->expense_id = (is_null($expenseId) || Expense::where('id', $expenseId)->exists()) ? $expenseId : null;
                        $trip_expense->currency_id = $this->expense_currency_id[$key] ?? null;
                        $trip_expense->category = $this->category[$key] ?? null;
                        $trip_expense->amount = $this->amount[$key] ?? 0;
                        $trip_expense->exchange_rate = $this->expense_exchange_rate[$key] ?? null;
                        $trip_expense->exchange_amount = $this->expense_exchange_amount[$key] ?? 0;
                
                        // Add to totals

                        $category = $this->category[$key] ?? null;
                        $currency_id = $this->expense_currency_id[$key] ?? null;
                        $amount = $this->amount[$key] ?? 0;
                        $exchange_amount = $this->expense_exchange_amount[$key] ?? 0;
                        

                        $this->addToCategoryTotal(
                            $category,
                            $amount = ($currency_id == $this->company->currency_id) ? $amount : $exchange_amount
                        );

                    
                
                        $trip_expense->save();
                    }
                }
                

                if ($this->transporter_agreement) {
                    $expense = Expense::where('name', 'Transporter Payment')->first();
                
                    $trip_expense = new TripExpense;
                    $trip_expense->user_id = $this->user->id;
                    $trip_expense->trip_id = $trip->id;
                    $trip_expense->transporter_id = $trip->transporter->id ?? null;
                    $trip_expense->expense_id = $expense->id ?? null;
                    $trip_expense->currency_id = $trip->currency_id;
                    $trip_expense->category = 'Self';
                    $trip_expense->amount = $this->transporter_freight ?? 0;
                    $trip_expense->exchange_rate = $this->exchange_rate ?? 1;
                    $trip_expense->exchange_amount = $this->exchange_transporter_freight ?? 0;
                
                    // Update total expenses
                

                    $freight_amount = $this->getBaseAmount($this->transporter_freight, $this->exchange_transporter_freight, $this->selectedCurrency);
                
                    if (is_numeric($freight_amount) && is_numeric($this->total_transporter_expenses)) {
                        $this->total_expenses += ($freight_amount - $this->total_transporter_expenses);
                    }
                
                    $trip_expense->save();
                }

                $this->recalculateExpenses($trip->id);


                $notifications = Notification::where('when','before')->where('category','Trip Authorization')->where('status',1)->get();
                
                if ($notifications->isNotEmpty()) {
                    foreach ($notifications as $notification) {
                        if($notification && isset($notification->category)){
                        $email = $notification->email ?? $notification->employee->email ?? null;
                        if($email){
                            Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $trip));
                        }
                        }
                    }
                }

    
   

                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Trip Scheduled Successfully!!"
                ]);
                return redirect()->route('trips.index');

        });
            // end trip creation logic
    }

    
    public function updatedSelectedAllowanceCurrency($id, $key)
    {
        if (!is_null($id) && $this->company->currency_id != $id) {
            $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                ->where('status', 1)
                ->where('expiry', '>', Carbon::today())
                ->first();
            
            if ($predefined_exchange_rate) {   
                $this->allowance_exchange_rate[$key] = $predefined_exchange_rate->exchange_rate;
            }
        }
    }

    public function updatedExpenseCurrencyId($id, $key)
    {
        if (!is_null($id) && $this->company->currency_id != $id) {
            $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                ->where('status', 1)
                ->where('expiry', '>', Carbon::today())
                ->first();
            
            if ($predefined_exchange_rate) {   
                $this->expense_exchange_rate[$key] = $predefined_exchange_rate->exchange_rate;
            }
        }
    }

      public function updatedRate(){
            $this->calculateFreight();
      }
      public function updatedTransporterRate(){
            $this->calculateFreight();
      }
      public function updatedWeight(){

        $this->calculateFreight();
      }
      public function updatedFreightCalculation(){
          $this->calculateFreight();
      }
      public function updatedCalculationMeasurement(){
          $this->calculateFreight();
      }
      public function updatedQuantity(){
          $this->calculateFreight();
      }
      public function updatedLitreageAt20(){
          $this->calculateFreight();
      }

      public function calculateFreight()
    {
        if ($this->freight_calculation == "rate_weight") {
            if ((isset($this->rate) && is_numeric($this->rate))  && ((isset($this->weight) && is_numeric($this->weight)) || (isset($this->litreage_at_20) && is_numeric($this->litreage_at_20)) || (isset($this->litreage) && is_numeric($this->litreage)))) {
                if ($this->cargo_type == "Solid") {
                    $this->freight = $this->rate * $this->weight;
                }elseif($this->cargo_type == "Liquid"){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->freight = $this->rate * $this->litreage_at_20;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->freight = $this->rate * $this->litreage;
                    }
                }
            }
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if ((isset($this->rate)  && is_numeric($this->rate))  && ((isset($this->distance) && is_numeric($this->distance)) )) {
                $this->freight = $this->rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
          
            if ((isset($this->rate) && is_numeric($this->rate)) && ((isset($this->weight) && is_numeric($this->weight)) || (isset($this->litreage_at_20) && is_numeric($this->litreage_at_20)) || (isset($this->litreage) && is_numeric($this->litreage))) && (isset($this->distance) && is_numeric($this->distance))) {
                if ($this->cargo_type == "Solid") {
                    $this->freight = $this->rate * $this->weight * $this->distance;
                }elseif($this->cargo_type == "Liquid"){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->freight = $this->rate * $this->litreage_at_20 * $this->distance ;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->freight = $this->rate * $this->litreage * $this->distance ;
                    }
                   
                }
            }
            
        }
        elseif ($this->freight_calculation == "flat_rate") {
            if ((isset($this->rate)  && is_numeric($this->rate))) {
                if ($this->cargo_type == "Solid") {
                    $this->freight = $this->rate;
                }elseif($this->cargo_type == "Liquid"){
                    $this->freight = $this->rate ;
                }
            }
            
        }

        if ($this->freight_calculation == "rate_weight") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate)) && ((isset($this->weight) && is_numeric($this->weight)) || (isset($this->litreage_at_20)  && is_numeric($this->litreage_at_20)) || (isset($this->litreage)  && is_numeric($this->litreage)))) {
                if ($this->cargo_type == "Solid") {
                    $this->transporter_freight = $this->transporter_rate * $this->weight;
                }elseif($this->cargo_type == "Liquid"){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->transporter_freight = $this->transporter_rate * $this->litreage_at_20;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->transporter_freight = $this->transporter_rate * $this->litreage;
                    }
                 
                } 
            }
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate))  && ((isset($this->distance) && is_numeric($this->distance)) )) {
                $this->transporter_freight = $this->transporter_rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate)) && ((isset($this->weight) && is_numeric($this->weight)) || (isset($this->litreage_at_20) && is_numeric($this->litreage_at_20)) || (isset($this->litreage) && is_numeric($this->litreage))) && (isset($this->distance) && is_numeric($this->distance))) {
                if ($this->cargo_type == "Solid") {
                    $this->transporter_freight = $this->transporter_rate * $this->weight * $this->distance;
                }elseif($this->cargo_type == "Liquid"){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->transporter_freight = $this->transporter_rate * $this->litreage_at_20 * $this->distance;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->transporter_freight = $this->transporter_rate * $this->litreage * $this->distance;
                    }
                   
                } 
            }
            
        }
        elseif ($this->freight_calculation == "flat_rate") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate))) {
                if ($this->cargo_type == "Solid") {
                    $this->transporter_freight = $this->transporter_rate ;
                }elseif($this->cargo_type == "Liquid"){
                    $this->transporter_freight = $this->transporter_rate;
                } 
            }
            
        }

    }


    

    public function calculateDistance($from, $to, $category)
    {
        $from_location = null;
        $to_location = null;
    
        // Determine the locations based on category
        if ($category === "destinations") {
            $from_location = Destination::find($from);
            $to_location = Destination::find($to);
        } elseif ($category === "loading_points") {
            $from_location = LoadingPoint::find($from);
            $to_location = OffloadingPoint::find($to);
        }
    
        // Ensure we have valid locations
        if (!$from_location || !$to_location) {
            return response()->json(['error' => 'Invalid locations provided'], 400);
        }
    
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Google Maps API key is missing'], 500);
        }
    
        // Validate latitude and longitude
        if (!isset($from_location->lat, $from_location->long, $to_location->lat, $to_location->long)) {
            return response()->json(['error' => 'Invalid coordinates'], 400);
        }
    
        $origin = "{$from_location->lat},{$from_location->long}";
        $destination = "{$to_location->lat},{$to_location->long}";
    
        // Make API request
        $response = Http::get("https://maps.googleapis.com/maps/api/distancematrix/json", [
            'units' => 'metric',
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $apiKey,
        ]);
    
        if (!$response->successful()) {
            return response()->json(['error' => 'Error fetching data from Google Maps API'], 500);
        }
    
        $data = $response->json();
        $element = optional($data)['rows'][0]['elements'][0] ?? null;
    
        if (!$element) {
            return response()->json(['error' => 'No distance data available'], 404);
        }
    
        // Extract distance and duration
        $distance_in_meters = optional($element)['distance']['value'] ?? null;
        $duration_text = optional($element)['duration']['text'] ?? '';
    
        if (!is_numeric($distance_in_meters)) {
            return response()->json(['error' => 'Invalid distance data'], 500);
        }
    
        // Convert to kilometers
        $this->distance = ($distance_in_meters >= 1000) ? $distance_in_meters / 1000 : $distance_in_meters;
        $this->duration =  $duration_text;
        return response()->json([
            'distance' => $this->distance,
            'duration' => $duration_text
        ], 200);
    }
    
   

    public function updatedSearchHorse(){
        if ($this->selectedStatus && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
            if ($this->selectedTransporter) {
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$this->selectedTransporter)
                                     ->where('registration_number', 'like', '%'.$this->searchHorse.'%')->where('archive',0)->get();
            }else{
                $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->where('archive',0)->get();
            }
              
            }else{
                if (isset($this->selectedTransporter)) {
                    $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('transporter_id',$this->selectedTransporter)
                                         ->where('registration_number', 'like', '%'.$this->searchHorse.'%')->where('status', 1)->where('archive',0)->where('service',0)->get();
                }else{
                    $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->where('status', 1)->where('archive',0)->where('service',0)->get();
                }
                
            }
    }
    public function updatedSearchVehicle(){
        if ($this->selectedStatus && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
            if ($this->selectedTransporter) {
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$this->selectedTransporter)->where('archive',0)
                                     ->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
            }else{
                $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->where('archive',0)->get();
            }
            }else{
                if (isset($this->selectedTransporter)) {
                    $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('transporter_id',$this->selectedTransporter)->where('status', 1)->where('archive',0)->where('service',0)
                                         ->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
                }else{
                    $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->where('status', 1)->where('archive',0)->where('service',0)->get();
                } 
            }
        }
    public function updatedSearchDriver(){
        if ($this->selectedStatus && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) { 
                if ($this->selectedTransporter) {
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$this->selectedTransporter)
                                    ->whereHas('employee', function ($query) {
                    return $query->where('name', 'like', '%'.$this->searchDriver.'%');
                })->where('archive',0)->get();
                }else {
                    $this->drivers = Driver::query()->with('employee:id,name,surname')->whereHas('employee', function ($query) {
                        return $query->where('name', 'like', '%'.$this->searchDriver.'%');
                    })->where('archive',0)->get();
                }
            }else{
                if ($this->selectedTransporter) {
                    $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$this->selectedTransporter)
                                        ->whereHas('employee', function ($query) {
                        return $query->where('name', 'like', '%'.$this->searchDriver.'%');
                    })->where('status', 1)->where('archive',0)->get();
                }else {
                    $this->drivers = Driver::query()->with('employee:id,name,surname')->whereHas('employee', function ($query) {
                        return $query->where('name', 'like', '%'.$this->searchDriver.'%');
                    })->where('status', 1)->where('archive',0)->get();
                }
             
            }
    }
    public function updatedSearchTrailer(){
        if ($this->selectedStatus && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
            if ($this->selectedTransporter) {
                $this->trailers =  Trailer::where('transporter_id',$this->selectedTransporter)
                                        ->where('registration_number', 'like', '%'.$this->searchTrailer.'%')->where('archive',0)->get();
            }else {
                $this->trailers = Trailer::where('registration_number', 'like', '%'.$this->searchTrailer.'%')->where('archive',0)->get();
            }
           
            }else{
                if ($this->selectedTransporter) {
                    $this->trailers =  Trailer::where('transporter_id',$this->selectedTransporter)
                                            ->where('registration_number', 'like', '%'.$this->searchTrailer.'%')->where('status', 1)->where('service',0)->where('archive',0)->get();
                }else {
                    $this->trailers = Trailer::where('registration_number', 'like', '%'.$this->searchTrailer.'%')->where('status', 1)->where('service',0)->where('archive',0)->get();
                }
            }
    }

    public function updatedSearchTrip(){
            $this->trips = Trip::query()->with([ 'customer:id,name',
            'horse:id,registration_number',
            'loading_point:id,name',
            'offloading_point:id,name'])
            ->where('trip_number', 'like', '%'.$this->searchTrip.'%')
            ->orWhere('trip_ref', 'like', '%'.$this->searchTrip.'%')
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->searchTrip.'%');
            })
            ->orderBy('start_date','desc')->get();
     

    }

    


    public function updatedFuelBalance(){
            $this->calculateFuelTotal();
        }
    public function updatedFuelQuantity(){
        $this->calculateFuelTotal();
        $this->calculateFuelAmount();
    }
    public function updatedTripFuel(){
        $this->calculateFuelTotal();
    }

    public function calculateFuelTotal(){

        if(($this->fuel_balance && is_numeric($this->fuel_balance)) && ($this->fuel_quantity && is_numeric($this->fuel_quantity))){
            if (!is_null($this->selectedHorse)) {
                $this->horse_fuel_total = $this->fuel_balance + $this->fuel_quantity;    
            }elseif(!is_null($this->selectedVehicle)){
                $this->vehicle_fuel_total = $this->fuel_balance + $this->fuel_quantity;    
            }    
        }  
    }

    public function updatedUnitPrice(){
        $this->calculateFuelAmount();
    }
    public function updatedTransporterPrice(){
        $this->calculateFuelAmount();
    }
    public function updatedTransporterTotal(){
        $this->calculateFuelAmount();
    }
  

    public function calculateFuelAmount(){
        if((isset($this->unit_price) && $this->unit_price != null && is_numeric($this->unit_price)) && (isset($this->fuel_quantity) && $this->fuel_quantity != null && is_numeric($this->fuel_quantity) )){
            $this->fuel_amount = $this->unit_price * $this->fuel_quantity;
        }
        if((isset($this->transporter_price) && $this->transporter_price != null && is_numeric($this->transporter_price)) && (isset($this->fuel_quantity) && $this->fuel_quantity != null && is_numeric($this->fuel_quantity))){
            $this->transporter_total = $this->transporter_price * $this->fuel_quantity;
        }
        if((isset($this->transporter_total) && ($this->transporter_total >= 0) && is_numeric($this->transporter_total))  && (isset($this->fuel_amount) && ($this->fuel_amount >= 0) && is_numeric($this->fuel_amount))){
            $this->fuel_profit = $this->transporter_total - $this->fuel_amount;
        }
    }
     
    public function updatedFuelExchangeRate(){
        $this->calculateForeignExchange();
    }
    public function updatedExchangeRate(){
        $this->calculateForeignExchange();
    }
    public function updatedFuelAmount(){
        $this->calculateForeignExchange();
    }
    public function updatedFreight(){
        $this->calculateForeignExchange();
    }
    public function updatedTransporterFreight(){
        $this->calculateForeignExchange();
    }
    
    public function calculateForeignExchange(){
        if ((isset($this->fuel_exchange_rate) && $this->fuel_exchange_rate > 0 && is_numeric($this->fuel_exchange_rate)) && (isset($this->fuel_amount) && $this->fuel_amount > 0 && is_numeric($this->fuel_amount)) ) {
            $this->fuel_exchange_amount = $this->fuel_exchange_rate * $this->fuel_amount;
        }
        if ((isset($this->exchange_rate) && $this->exchange_rate > 0 && is_numeric($this->exchange_rate)) && (isset($this->freight) && $this->freight > 0 && is_numeric($this->freight)) ) {
            $this->exchange_customer_freight = $this->exchange_rate * $this->freight;
        }
        if ((isset($this->exchange_rate) && $this->exchange_rate > 0 && is_numeric($this->exchange_rate)) && (isset($this->transporter_freight) && $this->transporter_freight > 0 && is_numeric($this->transporter_freight))) {
            $this->exchange_transporter_freight = $this->exchange_rate * $this->transporter_freight; 
        }
    }


    public function updatedSelectedTo($id)
    {
        if (!is_null($this->selectedFrom) ) {
            if (!is_null($id)) {
                $this->routes = Route::with('truck_stops:id,name')->where('status',1)->where('from',$this->selectedFrom)
                                    ->where('to',$id)->orderBy('name','asc')->get();
            
            }

            if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
                $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
            }else{
                if(isset($this->selectedFrom) && isset($this->selectedTo)){
                    $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
                }
            }
          
        }
    }

    public function updatedSelectedFrom($id)
    {
        if (!is_null($id) ) {
            if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
                $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
            }else{
                if(isset($this->selectedFrom) && isset($this->selectedTo)){
                    $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
                }
            }
          
        }
    }


    public function updatedLoadingPointId(){
        if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
            $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
        }else{
            if(isset($this->selectedFrom) && isset($this->selectedTo)){
                $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
            }
        }
    }

    

    public function updatedOffloadingPointId(){
        if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
            $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
        }else{
            if(isset($this->selectedFrom) && isset($this->selectedTo)){
                $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
            }
        }
    }


    public function refresh($category){

        if($category == "tracking_groups"){
            $this->trip_groups = TripGroup::where('status',1)->latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Tracking Groups Refreshed Successfully!!."
            ]);
        }
        elseif($category == "shifts"){
            $this->shifts = Shift::where('for','Trips')->where('status',1)->latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Shifts Refreshed Successfully!!."
            ]);
        }
        elseif($category == "borders"){
            $this->borders = Border::with('clearing_agents:id,name')->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Borders Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'clearing_agents'){
            $this->clearing_agents = ClearingAgent::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Clearing Agents Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'transporters'){
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
        elseif($category == 'trailers'){
            if (isset($this->selectedStatus) && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
               
                $this->trailers = Trailer::where('transporter_id',$this->selectedTransporter)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
               
            }else{
               
                $this->trailers = Trailer::where('transporter_id',$this->selectedTransporter)
                ->where('status', 1)
                ->where('service',0)
                ->where('archive',0)
                ->orderBy('registration_number','asc')->get();
                
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trailers Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'drivers'){
            if (isset($this->selectedStatus) && ($this->selectedStatus == "Scheduled" || $this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled") ) {
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$this->selectedTransporter)
                ->withAggregate('employee','name')
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();
            }else{
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$this->selectedTransporter)
                ->withAggregate('employee','name')
                ->where('status', 1)
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Drivers Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'brokers'){
            $this->brokers = Broker::orderBy('name','asc')->latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Brokers Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'allowances'){
            $this->allowances = Allowance::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allowances Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'customers'){
            $this->customers = Customer::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customers Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'consignees'){
            $this->consignees = Consignee::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Consignees Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'agents'){
            $this->agents = Agent::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Agents Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'currencies'){
            $this->currencies = Currency::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Currencies Refreshed Successfully!!."
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
        elseif($category == 'routes'){
            if (isset($this->selectedFrom) && isset($this->selectedTo)) {
                $this->routes = Route::with('truck_stops:id,name')->where('status',1)->where('from',$this->selectedFrom)
                ->where('to',$this->selectedTo)->orderBy('name','asc')->get();
            }else{
                $this->routes = Route::with('truck_stops:id,name')->where('status',1)->orderBy('name','asc')->get();
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Routes Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'truck_stops'){
            if(isset($this->selectedRoute)){
                $this->truck_stops = TruckStop::where('route_id',$this->selectedRoute)->orderBy('name','asc')->get();
            }else{
                $this->truck_stops = TruckStop::orderBy('name','asc')->get();
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Truck Stops Refreshed Successfully!!."
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
        elseif($category == 'rates'){
            $this->defined_customer_rates = Rate::where('category','Customer')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
            $this->defined_transporter_rates = Rate::where('category','Transporter')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Rates Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'expenses'){
            $this->expenses = Expense::whereHas('account', function($q){
                $q->where('name', 'Trip Expense');
             })->orderBy('name','asc')->get();
             $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expenses Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'stations'){
            $this->containers = Container::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fueling Stations Refreshed Successfully!!."
            ]);
        }
        
    }

    public function getDestination($id){
        $destination = Destination::find($id);
        return $destination;
    }

    public function render()
    {

        if($this->with_cargos == True){
            if(isset($this->distance) && isset($this->fuel_consumption_loaded_standard)){
                if (preg_match('/^\d+(\.\d+)?$/', $this->fuel_consumption_loaded_standard)) {
                    $this->trip_fuel = $this->distance * $this->fuel_consumption_loaded_standard;
                }
            }
        }elseif($this->with_cargos == False){
            if(isset($this->distance) && isset($this->fuel_consumption_empty_standard)){
                if (preg_match('/^\d+(\.\d+)?$/', $this->fuel_consumption_empty_standard)) {
                    $this->trip_fuel = $this->distance * $this->fuel_consumption_empty_standard;
                }
              
            }
        }
   
    return view('livewire.trips.create');
    }
}
