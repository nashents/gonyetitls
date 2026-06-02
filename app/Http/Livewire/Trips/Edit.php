<?php

namespace App\Http\Livewire\Trips;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Allowance;
use App\Models\Assignment;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Border;
use App\Models\Broker;
use App\Models\Cargo;
use App\Models\ClearingAgent;
use App\Models\Company;
use App\Models\Consignee;
use App\Models\Container;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\EmptyRun;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\Hour;
use App\Models\LoadingPoint;
use App\Models\Mileage;
use App\Models\OffloadingPoint;
use App\Models\Quotation;
use App\Models\Rate;
use App\Models\Route;
use App\Models\Shift;
use App\Models\TopUp;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Models\TripDestination;
use App\Models\TripExpense;
use App\Models\TripGroup;
use App\Models\TripOrigin;
use App\Models\TripTransportOrder;
use App\Models\TripType;
use App\Models\TruckStop;
use App\Models\UnitsOfMeasure;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $trip;
    public $trip_id;
    public $trip_number;
    public $with_quotation;
    public $selectedQuotation;
    public $quotations;
    public $shifts;
    public $shift;
    public $selectedShift;
    public $routes;
    public $selectedRoute;
    public $transporters;
    public $selectedTransporter;
    public $trip_groups;
    public $trip_group;
    public $starting_mileage;
    public $ending_mileage;
    public $starting_hours;
    public $ending_hours;
    public $hours;
    public $exchange_rate;
    public $exchange_customer_freight;
    public $exchange_transporter_freight;
    public $exchange_customer_turnover;
    public $exchange_transporter_cost_of_sales;
    public $trip_group_id;
    public $horse_fuel_total;
    public $vehicle_fuel_total;
    public $cd3_number;
    public $cd1_number;
    public $bill_of_entry;
    public $container_number;
    public $manifest_number;
    public $trip_types;
    public $trip_type_name;
    public $horse_selected;
    public $vehicle_selected;
    public $freight_calculation;
    public $calculation_measurement;
    public $selectedFuelCurrency;
    public $selectedTripType;
    public $haulage_type;
    public $customer_updates = 0;
    public $clearing_agents;
    public $clearing_agent_id;
    public $consignees;
    public $consignee_id;
    public $agents;
    public $agent_id;
    public $brokers;
    public $quantity;
    public $selectedBroker;
    public $multiple_destinations;
    public $attach_transport_order;
    public $customers;
    public $customer_id;
    public $containers;
    public $units_of_measures;
    public $units_of_measure_id;
    public $container;
    public $container_id;
    public $trip_ref;
    public $with_trailer;
    public $cargo_details;
    public $horses;
    public $horse;
    public $vehicles;
    public $vehicle;
    public $trip_fuel;
    public $fuel_balance;
    public $fuel_tank_capacity;
    public $selectedHorse;
    public $selectedVehicle;
    public $trailers;
    public $trailer_id;
    public $drivers;
    public $driver_id;

    public $all_drivers = False;
    public $all_trailers = False;
    public $all_vehicles = False;
    public $all_horses = False;

    public $emptyrun_origin;
    public $emptyrun_origin_starting_mileage;
    public $emptyrun_origin_ending_mileage;
    public $emptyrun_origin_distance;
    public $emptyrun_origin_fuel_quantity;
    public $emptyrun_origin_fuel_amount;
    public $emptyrun_origin_currency_id;

    public $emptyrun_destination;
    public $emptyrun_destination_currency_id;
    public $emptyrun_destination_starting_mileage;
    public $emptyrun_destination_ending_mileage;
    public $emptyrun_destination_distance;
    public $emptyrun_destination_fuel_quantity;
    public $emptyrun_destination_fuel_amount;
  
    public $stops;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $selected_fuel_currency;
    public $amount = [];
    public $selectedCargo;
    public $cost_of_sales;
    public $turnover;

    public $lat1;
    public $long1;
    public $lat2;
    public $long2;

    public $borders;
    public $selectedBorder;
    public $cargos;
    public $cargo;
    public $cargo_type;
    public $from_destinations;
    public $to_destinations;
    public $destination_id;
    public $selectedFrom;
    public $selectedTo;
    public $searchFrom;
    public $searchTo;
    public $searchLoadingPoint;
    public $searchOffloadingPoint;
    public $offloading_points;
    public $offloading_point_id;
    public $loading_points;
    public $loading_point_id;
    public $start_date;
    public $end_date;
    public $weight;
    public $litreage;
    public $litreage_at_20;
    public $rate;
    public $transporter_rate;
    public $mode_of_transport;
    public $with_customer_rates;
    public $with_transporter_rates;
    public $defined_customer_rates;
    public $selectedDefinedCustomerRate;
    public $defined_transporter_rates;
    public $selectedDefinedTransporterRate;
    public $comments;
    public $freight;
    public $transporter_freight;
    public $profit;
    public $notes;

    public $trip_destinations;
    public $destinations_selectedTo = [];
    public $destinations_offloading_point_id = [];
    public $offloaded_weight = [];
    public $offloaded_rate = [];
    public $offloaded_freight = [];
    public $offloaded_quantity = [];
    public $offloaded_litreage = [];
    public $offloaded_litreage_at_20 = [];
   
    public $trip_origins;
    public $destinations_selectedFrom = [];
    public $destinations_loading_point_id = [];
    public $loaded_weight = [];
    public $loaded_rate = [];
    public $loaded_freight = [];
    public $loaded_quantity = [];
    public $loaded_litreage = [];
    public $loaded_litreage_at_20 = [];

    public $destinations_inputs = [];
    public $d = 1;
    public $e = 1;
    public function destaintionsAdd($d)
    {
        $d = $d + 1;
        $this->d = $d;
        array_push($this->destinations_inputs ,$d);
    }
    public function destinationsRemove($d)
    {
        unset($this->destinations_inputs[$d]);
    }

    public $origins_inputs = [];
    public $or = 1;
    public $r = 1;

    public function addOrigin($or)
    {
        $or = $or + 1;
        $this->or = $or;
        array_push($this->origins_inputs ,$or);
    }
    public function removeOrigin($or)
    {
        unset($this->origins_inputs[$or]);
    }

     
    public $boe = [];
    public $current_allocated_currency_id = [];
    public $current_allocated_exchange_rate = [];
    public $current_allocated_exchange_amount = [];
    public $current_allocated_rate = [];
    public $current_allocated_freight = [];
    public $current_allocated_weight = [];
    public $current_allocated_quantity = [];
    public $current_allocated_litreage = [];
    public $current_allocated_units_of_measure_id = [];
    public $current_selectedTransportOrder = [];
    public $current_cargo_type = [];

    public $allocated_currency_id = [];
    public $allocated_exchange_rate = [];
    public $allocated_exchange_amount = [];
    public $allocated_rate = [];
    public $allocated_freight = [];
    public $allocated_weight = [];
    public $allocated_quantity = [];
    public $allocated_litreage = [];
    public $allocated_units_of_measure_id = [];
    public $selectedTransportOrder = [];
    public $transport_orders;
    public $trip_transport_orders;
    
    public $to_inputs = [];
    public $toOrder = 1;
    public $o = 1;

    public function toAdd($toOrder)
    {
        $toOrder = $toOrder + 1;
        $this->toOrder = $toOrder;
        array_push($this->to_inputs ,$toOrder);
    }

    public function toRemove($toOrder)
    {
       unset($this->to_inputs[$toOrder]);
        unset($this->selectedTranportOrder[$toOrder]);
        unset($this->allocated_units_of_measure_id[$toOrder]);
        unset($this->allocated_weight[$toOrder]);
        unset($this->allocated_freight[$toOrder]);
        unset($this->allocated_rate[$toOrder]);
        unset($this->allocated_exchange_rate[$toOrder]);
        unset($this->allocated_exchange_amount[$toOrder]);
        unset($this->allocated_currency_id[$toOrder]);
        unset($this->allocated_litreage[$toOrder]);
        unset($this->allocated_quantity[$toOrder]);
    }

    public $manifest_comments;
    public $volume;
    public $temparature;
    public $net_weight;
    public $seal_number;
  
    public $net_profit;
    public $truck_stops;
    public $truck_stop_id;
    public $trip_truck_stop_ids;

    public $expense_currency_id = [];
    public $expense_exchange_rate;
    public $expense_exchange_amount;
    public $expenses;
    public $expense_id;
    public $expense_name;
    public $category;
    public $total_transporter_expenses;
    public $total_customer_expenses;
    public $total_expenses;
    public $commission_id;

    public $trip_fuel_locked = false;

    public $distance;
    public $duration;
    public $selectedStatus;
    public $return_trip;

// Agent & Commission Variables
    public $commission;
    public $commission_amount;
//fuel order variables

//driver allowances 
    public $searchHorse;
    public $searchVehicle;
    public $searchTrailer;
    public $searchDriver;
    public $searchTrip;

    protected $queryString = ['searchTrip','searchVehicle','searchHorse','searchTrailer','searchDriver', 'searchFrom','searchTo','searchLoadingPoint','searchOffloadingPoint'];

    public $selectedTrip;
    public $trips;
    public $allowance_id;
    public $allowance;
    public $allowance_title;
    public $selectedAllowanceCurrency;
    public $allowance_amount;

   
    public $fuel_exchange_rate;
    public $fuel_exchange_amount;
    public $fuel_category;
    public $unit_price = 0;
    public $fuel_amount;
    public $fuel_quantity = 0 ;
    public $odometer;
    public $container_balance;
    public $date;
    public $fillup = 1;
    public $invoice_number;
    public $fuel_consumption_loaded_standard;
    public $fuel_consumption_empty_standard;
    public $fuel_distance;
    public $file;
    public $fuel_comments;
    public $selected_container;
    public $fuel;
    public $from;
    public $to;
    public $fuel_id;

     //trip timelines
    public $arrive_lp;
    public $depart_lp;
    public $arrive_op;
    public $depart_op;
    public $timelines;

    public $allowances;

 
    public $selectedContainer;
    public $selectedCategory;
    public $with_cargos;
    public $fuel_order;
    public $trip_expenses;
    public $transporter_agreement;
    public $rank_names;
    public $role_names;
    public $department_names;

    public $company;
    public $employee;
    public $user;

    public $to_exists = False;



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

    public function updatedSelectedQuotation($id){
            if (!is_null($id)) {
              $quotation = Quotation::find($id);
              if (isset($quotation)) {
                $this->customer_id = $quotation->customer ? $quotation->customer->id : "";
                $this->selectedCurrency = $quotation->currency_id;
              }
              
            }
    }

      public function updatedAttachTransportOrder($value){
       
        if(!is_null($value)){
            if($value == True){
                $this->transport_orders = TransportOrder::where('authorization','approved')->latest()->get();
                $this->cargo_type = [];
            }
        }
    }

    public function updatedSelectedTransportOrder($id, $key){
       
        if(is_null($id) && is_null($key) ){
           return ;
        }

        $transport_order = TransportOrder::find($id);
        $cargo = Cargo::find($transport_order->cargo_id);
        $this->cargo_type[$key] = $cargo->type;
        $this->allocated_freight[$key] = $transport_order->freight;
        $this->allocated_rate[$key] = $transport_order->rate;
        $this->allocated_exchange_rate[$key] = $transport_order->exchange_rate;
        $this->allocated_exchange_amount[$key] = $transport_order->exchange_customer_freight;
        $this->allocated_currency_id[$key] = $transport_order->currency_id;
        $this->allocated_weight[$key] = $transport_order->weight;
        $this->allocated_litreage[$key] = $transport_order->litreage;
        $this->allocated_quantity[$key] = $transport_order->quantity;
        $this->allocated_units_of_measure_id[$key] = $transport_order->units_of_measure_id ?: Null;
        
    }
    
    public function updatedSelectedCurrentTransportOrder($id, $key){
       
        if(is_null($id) && is_null($key) ){
           return ;
        }

        $transport_order = TransportOrder::find($id);
        $cargo = Cargo::find($transport_order->cargo_id);
        $this->current_cargo_type[$key] = $cargo->type;
        $this->current_allocated_freight[$key] = $transport_order->freight;
        $this->current_allocated_rate[$key] = $transport_order->rate;
        $this->current_allocated_exchange_rate[$key] = $transport_order->exchange_rate;
        $this->current_allocated_exchange_amount[$key] = $transport_order->exchange_customer_freight;
        $this->current_allocated_currency_id[$key] = $transport_order->currency_id;
        $this->current_allocated_weight[$key] = $transport_order->weight;
        $this->current_allocated_litreage[$key] = $transport_order->litreage;
        $this->current_allocated_quantity[$key] = $transport_order->quantity;
        $this->current_allocated_units_of_measure_id[$key] = $transport_order->units_of_measure_id ?: Null;
    }


    public function updatedSelectedTrip($id){
        if(!is_null($id)){
            $initial_trip = Trip::find($id);
            if(isset($initial_trip)){
                $this->selectedTransporter = $initial_trip->transporter_id;
                $transporter = Transporter::find($initial_trip->transporter_id);
              
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
                    $this->trailer_id = [];
                    foreach($initial_trip_trailers as $trailer){
                        $this->trailer_id[] = $trailer->id;
                    }
                }
                $this->driver_id = $initial_trip->driver_id;
            }
        }
    }

      public function updatedFuelOrder(){
        $this->fuel_quantity = $this->trip_fuel;
    }

    public function calculateFuelConsumption($id)
    {
        $trip = Trip::find($id);
        if (!$trip) return;

        $fuels = $trip->fuels;

        $distance = null;
        if (is_numeric($this->starting_mileage) && is_numeric($this->ending_mileage)) {
            $distance = (float) $this->ending_mileage - (float) $this->starting_mileage;
        } else {
            $distance = (float) ($this->distance ?? 0);
        }

        // Protect against negative mileage
        if ($distance < 0) {
            $distance = 0;
        }

        $hours_distance = null;
        if (is_numeric($this->starting_hours) && is_numeric($this->ending_hours)) {
            $hours_distance = (float) $this->ending_hours - (float) $this->starting_hours;
            if ($hours_distance < 0) {
                $hours_distance = 0;
            }
        }

       // 3) Total fuel used (L)
        $total_fuel = $fuels && $fuels->count() > 0
            ? (float) $fuels->sum(fn($fuel) => (float) $fuel->quantity)
            : (float) ($this->trip_fuel ?? 0);

        // 4) km per litre
        if ($distance > 0 && $total_fuel > 0) {
            $trip->fuel_consumption_mileage = $distance / $total_fuel;   // km/L
        }

        // 5) hours per litre (if that’s what you want)
        if (!is_null($hours_distance) && $hours_distance > 0 && $total_fuel > 0) {
            $trip->fuel_consumption_hours = $hours_distance / $total_fuel; // h/L
        }

        $trip->save();
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



    public function updatedSelectedRoute($route)
    {
        if (!is_null($route)) {
            $this->truck_stops = TruckStop::where('route_id',$route)->latest()->get();
        }
    }
    public function updatedSelectedCargo($id)
    {
            if (!is_null($id)) {
                $this->cargo = Cargo::find($id);
                $this->cargo_type = $this->cargo ? $this->cargo->type : "";
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
    // public function updatedSelectedBorder($id)
    // {
    //         if (!is_null($id)) {
    //             $border = Border::find($id);
    //             if (!is_null($border)) {
    //                 $this->clearing_agents = $border->clearing_agents;
    //             }
               
    //         }
    // }
    public function updatedSelectedContainer($id)
    {
        if (!is_null($id) ) {
            $this->container = Container::find($id);
            $this->selected_container = Container::find($id);
            $top_ups = TopUp::where('container_id',$id)->where('rate','!=', NULL)->where('rate','!=',"")->where('currency_id',$this->container->currency_id)->get();
            
            $topups_price_total = TopUp::where('container_id',$this->container->id)->where('rate','!=', NULL)->where('rate','!=',"")->where('rate', 'REGEXP', '^[0-9]+$')->where('currency_id',$this->container->currency_id)->get()->sum('rate');
            $topups_count = $top_ups->count();
            if ((is_numeric($topups_count) && $topups_count > 0) && (is_numeric($topups_price_total) && $topups_price_total > 0)) {
                $this->unit_price = number_format($topups_price_total/$topups_count,2);
            }
        
            $this->container_balance = $this->container->balance;
            $this->selectedFuelCurrency = $this->container->currency_id;
            }
    }

 
    public function updatedSelectedHorse($id)
    {
        if (!is_null($id) ) {
            $this->horse = Horse::find($id);
            $this->horse_selected = Horse::find($id);
            $this->selectedHorse = $id;
           
            $assignment = Assignment::where('horse_id',$id)
                                    ->where('status', 1)->first();

            $trailer_assignments = $this->horse->trailer_assignments->where('status',1);
                                    
           
            $this->hours = $this->horse->hours;
            $this->fuel_consumption_loaded_standard = $this->horse->fuel_consumption_loaded_standard;
            $this->fuel_consumption_empty_standard = $this->horse->fuel_consumption_empty_standard;
            $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
            $this->starting_mileage = $this->horse->mileage;
            $this->starting_hours = $this->horse->hours;
            $this->fuel_balance = $this->horse->fuel_balance;
            if (isset( $assignment)) {
                $driver = $assignment->driver;
                $this->driver_id = $driver->id;
            }                        
            if (isset( $trailer_assignments)) {
                foreach ($trailer_assignments as $trailer_assignment) {
                    $this->trailer_id[] = $trailer_assignment->trailer_id;
                }
                
            }                        
           
        }
    }
    public function updatedSelectedVehicle($vehicle)
    {
        if (!is_null($vehicle) ) {
            $this->vehicle = Vehicle::find($vehicle);
            $this->vehicle_selected = Vehicle::find($vehicle);
            $this->selectedVehicle = $vehicle;
           
            $assignment = VehicleAssignment::where('vehicle_id',$vehicle)
                                    ->where('status', 1)->first();
                                    
           
            $this->hours = $this->vehicle->hours;
            $this->fuel_tank_capacity = $this->vehicle->fuel_tank_capacity;
            $this->starting_mileage = $this->vehicle->mileage;
            $this->starting_hours = $this->vehicle->hours;
            $this->fuel_consumption_loaded_standard = $this->vehicle->fuel_consumption_loaded_standard;
            $this->fuel_consumption_empty_standard = $this->vehicle->fuel_consumption_empty_standard;
            $this->fuel_balance = $this->vehicle->fuel_balance;
            if (isset( $assignment)) {
                $driver = $assignment->driver;
                $this->driver_id = $driver->id;
            }                        
           
        }
    }



    public function updatedSelectedTransporter($id)
    {
        if (!is_null($id) ) {
            
          
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
    public function updatedSelectedBroker($id)
    {
        if (!isset($this->selectedTransporter)) {
            if (!is_null($id) ) {
                $broker = Broker::find($id);
                if(isset($broker)){
                  
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

    public function mount($id){

       
        $this->trip_id = $id;
        $this->trip = Trip::withTrashed()->with(['fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','vehicle','vehicle.vehicle_make','vehicle.vehicle_model','horse','horse.horse_make','horse.horse_model',
        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->find($id);
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->quotations = Quotation::with('customer')
                                    ->latest()
                                    ->get();
        $this->shifts = Shift::where('for','Trips')->where('status','1')->latest()->get();
        $this->company = Company::with('currency')->find( $this->employee->company_id);
        $this->defined_customer_rates = Rate::where('category','Customer')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
        $this->defined_transporter_rates = Rate::where('category','Transporter')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
        $this->containers = Container::orderBy('name','asc')->latest()->get();
        $this->transporters = Transporter::with('vehicles:id,registration_number','vehicles.vehicle_make:id,name','vehicles.vehicle_model:id,name','horses:id,registration_number','horses.horse_make:id,name','horses.horse_model:id,name','cargos:id,name','trailers:id,registration_number,make,model','drivers:id','drivers.employee:id,name,surname')->where('authorization','approved')->orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->trip_groups = TripGroup::where('status',1)->latest()->get();
        $this->transport_orders = TransportOrder::where('authorization','approved')->latest()->get();
        $this->routes = Route::with('truck_stops:id,name')->orderBy('name','asc')->get();
        $this->agents = Agent::orderBy('name','asc')->get();
        $this->truck_stops = TruckStop::orderBy('name','asc')->get();
        $trip_truck_stops = $this->trip->truck_stops;
        foreach ($trip_truck_stops as $trip_truck_stop) {
            $this->trip_truck_stop_ids[] = $trip_truck_stop->id;
        }
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->emptyrun_destination = $this->trip->emptyrun_destination;
        $this->emptyrun_origin = $this->trip->emptyrun_origin;

        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $this->department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $this->role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $this->rank_names[] = $rank->name;
        }

        $emptyrun_origin = EmptyRun::where('trip_id', $this->trip->id)->where('emptyrun_origin',True)->first();
        if(isset($emptyrun_origin)){
            $this->emptyrun_origin = $emptyrun_origin->emptyrun_origin;
            $this->emptyrun_origin_distance = $emptyrun_origin->distance;
            $this->emptyrun_origin_starting_mileage = $emptyrun_origin->starting_mileage;
            $this->emptyrun_origin_ending_mileage = $emptyrun_origin->ending_mileage;
            $this->emptyrun_origin_currency_id = $emptyrun_origin->currency_id;
            $this->emptyrun_origin_fuel_quantity = $emptyrun_origin->fuel_quantity;
            $this->emptyrun_origin_fuel_amount = $emptyrun_origin->fuel_amount;
        }
        $emptyrun_destination = EmptyRun::where('trip_id', $this->trip->id)->where('emptyrun_destination',True)->first();
        if(isset($emptyrun_destination)){
            $this->emptyrun_destination = $emptyrun_destination->emptyrun_destination;
            $this->emptyrun_destination_distance = $emptyrun_destination->distance;
            $this->emptyrun_destination_starting_mileage = $emptyrun_destination->starting_mileage;
            $this->emptyrun_destination_ending_mileage = $emptyrun_destination->ending_mileage;
            $this->emptyrun_destination_currency_id = $emptyrun_destination->currency_id;
            $this->emptyrun_destination_fuel_quantity = $emptyrun_destination->fuel_quantity;
            $this->emptyrun_destination_fuel_amount = $emptyrun_destination->fuel_amount;
        }
        if($this->trip->horse){
            $this->mode_of_transport = "Horse";
        }elseif($this->trip->vehicle){
            $this->mode_of_transport = "Vehicle";
        }
       
        if($this->trip->with_customer_rates){
            $this->with_customer_rates = $this->trip->with_customer_rates;
        }else{
            $this->with_customer_rates = "custom";
        }
        if($this->trip->with_transporter_rates){
            $this->with_transporter_rates = $this->trip->with_transporter_rates;
        }else{
            $this->with_transporter_rates = "custom";
        }
        $this->trip_transport_orders = $this->trip->trip_transport_orders;

        if($this->trip_transport_orders){
            foreach($this->trip_transport_orders as $key => $trip_transport_order){

                $this->current_selectedTransportOrder[$key] = $trip_transport_order->transport_order_id;
                $transport_order = TransportOrder::find($trip_transport_order->transport_order_id);
                $cargo = Cargo::find($transport_order->cargo_id);
                $this->current_cargo_type[$key] = $cargo->type;
                $this->current_allocated_quantity[$key] = $trip_transport_order->allocated_quantity ?: $transport_order->quantity;
                $this->current_allocated_weight[$key] = $trip_transport_order->allocated_weight ?: $transport_order->weight;
                $this->current_allocated_litreage[$key] = $trip_transport_order->allocated_litreage ?: $transport_order->litreage;
                $this->current_allocated_units_of_measure_id[$key] = $trip_transport_order->units_of_measure_id ?: $transport_order->units_of_measure_id;
                $this->current_allocated_currency_id[$key] = $trip_transport_order->currency_id ?: $transport_order->currency_id;
                $this->current_allocated_freight[$key] = $trip_transport_order->allocated_freight ?: $transport_order->freight;
                $this->current_allocated_rate[$key] = $trip_transport_order->allocated_rate ?: $transport_order->rate ;
                $this->boe[$key] = $trip_transport_order->bill_of_entry;
                $this->current_allocated_exchange_rate[$key] = $trip_transport_order->exchange_rate ?: $transport_order->exchange_rate;
                $this->current_allocated_exchange_amount[$key] = $trip_transport_order->exchange_amount ?: $transport_order->exchange_customer_freight;
            }
        }

        $this->trip_destinations = TripDestination::where('trip_id', $this->trip->id)->get();

        if($this->trip_destinations){
            foreach($this->trip_destinations as $trip_destination){
                $this->offloaded_weight[] = $trip_destination->weight;
                $this->offloaded_quantity[] = $trip_destination->quantity;
                $this->offloaded_litreage[] = $trip_destination->litreage;
                $this->offloaded_litreage_at_20[] = $trip_destination->litreage_at_20;
                $this->destinations_selectedTo[] = $trip_destination->destination_id;
                $this->destinations_offloading_point_id[] = $trip_destination->offloading_point_id;
                $this->offloaded_rate[] = $trip_destination->rate;
                $this->offloaded_freight[] = $trip_destination->freight;
            }
        }
        
       
        $this->trip_origins = TripOrigin::where('trip_id', $this->trip->id)->get();

        if($this->trip_origins){
            foreach($this->trip_origins as $trip_origin){
                $this->loaded_weight[] = $trip_origin->weight;
                $this->loaded_quantity[] = $trip_origin->quantity;
                $this->loaded_litreage[] = $trip_origin->litreage;
                $this->loaded_litreage_at_20[] = $trip_origin->litreage_at_20;
                $this->destinations_selectedFrom[] = $trip_origin->destination_id;
                $this->destinations_loading_point_id[] = $trip_origin->loading_point_id;
                $this->loaded_rate[] = $trip_origin->rate;
                $this->loaded_freight[] = $trip_origin->freight;
            }
        }
        
        $this->horses = Horse::where('status', 1)
        ->where('service',0)
        ->where('archive',0)
        ->orWhere('id',$this->trip->horse_id)
        ->orderBy('registration_number','asc')->get();

        $this->vehicles = Vehicle::where('status', 1)
        ->where('service',0)
        ->where('archive',0)
        ->orWhere('id',$this->trip->vehicle_id)
        ->orderBy('registration_number','asc')->get();

        foreach ($this->trip->truck_stops as $truck_stop) {
            $this->truck_stop_id[] = $truck_stop->id;
        }
        foreach ($this->trip->borders as $border) {
            $this->selectedBorder[] = $border->id;
        }
        foreach ($this->trip->clearing_agents as $clearing_agent) {
            $this->clearing_agent_id[] = $clearing_agent->id;
        }
        foreach ($this->trip->trailers as $trailer) {
            $this->trailer_id[] = $trailer->id;
        }
        if (isset($this->trailer_id)) {
            $this->trailers = Trailer::where('service',0)
            ->where('status', 1)
            ->where('archive',0)
            ->orWhereIn('id',$this->trailer_id)
            ->orderBy('registration_number','asc')->get();
        }else{
            $this->trailers = Trailer::where('service',0)
            ->orderBy('registration_number','asc')->get();
        }
       

        $this->drivers = Driver::withAggregate('employee','name')
        ->where('status', 1)
        ->where('archive',0)
        ->orWhere('id',$this->trip->driver_id)
        ->orderBy('employee_name','asc')->get();

        $this->trip_types = TripType::orderBy('name','asc')->get();

        $commission = $this->trip->commission;

        if ( $commission) {
            $this->commission = $commission->commission;
            $this->commission_amount = $commission->amount;
            $this->commission_id = $commission->id ;
        }
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->consignees = Consignee::orderBy('name','asc')->get();
        $this->brokers = Broker::orderBy('name','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->from_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->to_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->borders = Border::with('clearing_agents:id,name')->orderBy('name','asc')->get();
        $this->clearing_agents = ClearingAgent::orderBy('name','asc')->get();
        
      
       
        
         $this->fuel_order = $this->trip->fuel_order;
         $this->fuel = $this->trip->fuels->where('fillup',1)->first();

         if ($this->fuel) {
            $this->fuel_id = $this->fuel->id;
            $this->unit_price = $this->fuel->unit_price;
            $this->selectedFuelCurrency = $this->fuel->currency_id;
            $this->selectedContainer = $this->fuel->container_id;
            $this->selected_container = Container::find($this->fuel->container_id);
            $this->fuel_amount = $this->fuel->amount;
            $this->fuel_quantity = $this->fuel->quantity;
            $this->odometer = $this->fuel->odometer;
            $this->hours = $this->fuel->hours;
            $this->date = $this->fuel->date;
            $this->fillup = $this->fuel->fillup;
            $this->fuel_comments = $this->fuel->comments;
            $this->fuel_exchange_rate = $this->fuel->exchange_rate;
            $this->fuel_exchange_amount = $this->fuel->exchange_amount;
            $this->fuel_category = $this->fuel->category;
            
         }
        

         $this->with_trailer = $this->trip->with_trailer;
         $this->trip_number = $this->trip->trip_number;
         $this->trip_ref = $this->trip->trip_ref;
         $this->freight_calculation = $this->trip->freight_calculation;
        
       
         $this->selectedTripType = $this->trip->trip_type_id;
         $this->haulage_type = $this->trip->haulage_type;
         $this->trip_type_name = TripType::find($this->trip->trip_type_id) ? TripType::find($this->trip->trip_type_id)->name : "";
           
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
          $this->trip_group_id = $this->trip->trip_group_id;
         $this->selectedBroker = $this->trip->broker_id;
         $this->consignee_id = $this->trip->consignee_id;
         $this->customer_id = $this->trip->customer_id;
         $this->timelines = $this->trip->timelines;
         $this->arrive_lp = $this->trip->arrive_loading_point;
         $this->depart_lp = $this->trip->depart_loading_point;
         $this->attach_transport_order = $this->trip->attach_transport_order;
         $this->multiple_destinations = $this->trip->multiple_destinations ?? False;
         $this->arrive_op = $this->trip->arrive_offloading_point;
         $this->depart_op = $this->trip->depart_offloading_point;
         $this->selectedHorse = $this->trip->horse_id;
         $this->selectedVehicle = $this->trip->vehicle_id;
         $this->horse_selected = Horse::find($this->selectedHorse);
         $this->vehicle_selected = Vehicle::find($this->selectedVehicle);
         $this->cargo_details = $this->trip->cargo_details;
         $this->selectedTransporter = $this->trip->transporter_id;
         $this->stops = $this->trip->stops;
         $this->volume =  $this->trip->volume;
         $this->temparature =  $this->trip->temparature;
         $this->selectedQuotation =  $this->trip->quotation_id;
         if(isset( $this->selectedQuotation)){
            $this->with_quotation = True;
         }
         $this->net_weight =  $this->trip->net_weight;
         $this->seal_number =  $this->trip->seal_number;
         $this->agent_id = $this->trip->agent_id;
         $this->selectedTrip = $this->trip->initial_trip_id;
         $this->driver_id = $this->trip->driver_id;
         $this->selectedCurrency = $this->trip->currency_id;
         $this->selectedCargo = $this->trip->cargo_id;
         $cargo = Cargo::find($this->trip->cargo_id);
         $this->cargo_type = $cargo ? $cargo->type : Null;

         if(isset($this->trip->calculation_measurement)){
            $this->calculation_measurement = $this->trip->calculation_measurement;
         }else{
            if($this->cargo_type && $this->cargo_type == "Solid"){
                $this->calculation_measurement = "weight";
            }else{
                $this->calculation_measurement = "litreage_at_20";
            }
            
         }

         $this->selectedRoute = $this->trip->route_id;
         $this->trip_fuel = $this->trip->trip_fuel;
         $this->cd3_number = $this->trip->cd3_number;
         $this->cd1_number = $this->trip->cd1_number;
         $this->bill_of_entry = $this->trip->bill_of_entry;
         $this->container_number = $this->trip->container_number;
         $this->manifest_number = $this->trip->manifest_number;
         $this->units_of_measure_id = $this->trip->units_of_measure_id;
         $this->notes = $this->trip->notes;
         $this->quantity = $this->trip->quantity;
         $this->customer_updates = $this->trip->customer_updates;
         $this->selectedShift = $this->trip->shift_id;
         $this->shift = $this->trip->shift;
         $this->selectedFrom = $this->trip->from;
         $this->starting_mileage = $this->trip->starting_mileage;
         $this->ending_mileage = $this->trip->ending_mileage;
         $this->starting_hours = $this->trip->starting_hours;
         $this->ending_hours = $this->trip->ending_hours;
         $this->exchange_rate = $this->trip->exchange_rate;
         $this->exchange_customer_freight = $this->trip->exchange_customer_freight;
         $this->exchange_transporter_freight = $this->trip->exchange_transporter_freight;
         $this->exchange_rate = $this->trip->exchange_rate;
         $this->exchange_transporter_cost_of_sales = $this->trip->exchange_transporter_cost_of_sales;
         $this->loading_point_id = $this->trip->loading_point_id;
         $this->offloading_point_id = $this->trip->offloading_point_id;
         $this->selectedTo = $this->trip->to;
         $this->start_date = $this->trip->start_date;
         $this->end_date = $this->trip->end_date;
         $this->weight = $this->trip->weight;
         $this->litreage = $this->trip->litreage;
         $this->litreage_at_20 = $this->trip->litreage_at_20;
         $this->with_cargos = $this->trip->with_cargos;
         $this->transporter_agreement = $this->trip->transporter_agreement;
         $this->fuel_order = $this->trip->fuel_order;
         $this->trip_expenses = $this->trip->trip_expenses;
         $this->rate = $this->trip->rate;
         $this->freight = $this->trip->freight;
         $this->transporter_rate = $this->trip->transporter_rate;
         $this->transporter_freight = $this->trip->transporter_freight;
         $this->cost_of_sales = $this->trip->cost_of_sales;
         $this->turnover = $this->trip->turnover;
         $this->profit = $this->trip->profit;
         $this->distance = $this->trip->distance;
         $this->selectedStatus = $this->trip->trip_status;
         $this->comments = $this->trip->comments;

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

    public function addDestinations($trip_transport_order)
    {
       
        // Remove old destinations linked to this trip transport order
        TripDestination::where('trip_id', $trip_transport_order->trip_id)->delete();
       

        if ($this->multiple_destinations == true) {

            if (!empty($this->destinations_selectedTo)) {

        
                foreach ($this->destinations_selectedTo as $key => $destinationId) {

                    $offloadingPointId = $this->destinations_offloading_point_id[$key] ?? null;

                    $trip_destination = new TripDestination();
                    $trip_destination->user_id = Auth::id();
                    $trip_destination->trip_id = $trip_transport_order->trip_id;
                    $trip_destination->transport_order_id = $trip_transport_order->transport_order_id;
                    $trip_destination->trip_transport_order_id = $trip_transport_order->id;
                    $trip_destination->destination_id = $destinationId;
                    $trip_destination->offloading_point_id = $offloadingPointId;
                    $trip_destination->units_of_measure_id = $this->units_of_measure_id ?: null;
                    $trip_destination->weight = $this->offloaded_weight[$key] ?? null;
                    $trip_destination->quantity = $this->offloaded_quantity[$key] ?? null;
                    $trip_destination->litreage = $this->offloaded_litreage[$key] ?? null;
                    $trip_destination->litreage_at_20 = $this->offloaded_litreage_at_20[$key] ?? null;
                    $trip_destination->rate = $this->offloaded_rate[$key] ?? null;
                    $trip_destination->freight = $this->offloaded_freight[$key] ?? null;
                    $trip_destination->save();
                }
            }

        } else {


            $trip_destination = new TripDestination();
            $trip_destination->user_id = Auth::id();
            $trip_destination->trip_id = $trip_transport_order->trip_id;
            $trip_destination->transport_order_id = $trip_transport_order->transport_order_id;
            $trip_destination->trip_transport_order_id = $trip_transport_order->id;
            $trip_destination->destination_id = $this->selectedTo;
            $trip_destination->offloading_point_id = $this->offloading_point_id;
            $trip_destination->weight = $this->weight;
            $trip_destination->quantity = $this->quantity;
            $trip_destination->units_of_measure_id = $this->units_of_measure_id ?: null;
            $trip_destination->litreage = $this->litreage;
            $trip_destination->litreage_at_20 = $this->litreage_at_20;
            $trip_destination->rate = $this->rate;
            $trip_destination->freight = $this->freight;
            $trip_destination->save();
        }
    }
    
    public function addOrigins($trip_transport_order)
    {
       
        // Remove old destinations linked to this trip transport order
        TripOrigin::where('trip_id', $trip_transport_order->trip_id)->delete();
       

        if ($this->multiple_destinations == true) {

            if (!empty($this->destinations_selectedFrom)) {

                foreach ($this->destinations_selectedFrom as $key => $destinationId) {

                    $loadingPointId = $this->destinations_loading_point_id[$key] ?? null;

                    $trip_origin = new TripOrigin();
                    $trip_origin->user_id = Auth::id();
                    $trip_origin->trip_id = $trip_transport_order->trip_id;
                    $trip_origin->transport_order_id = $trip_transport_order->transport_order_id;
                    $trip_origin->trip_transport_order_id = $trip_transport_order->id;
                    $trip_origin->destination_id = $destinationId;
                    $trip_origin->loading_point_id = $loadingPointId;
                    $trip_origin->units_of_measure_id = $this->units_of_measure_id ?: null;
                    $trip_origin->weight = $this->loaded_weight[$key] ?? null;
                    $trip_origin->quantity = $this->loaded_quantity[$key] ?? null;
                    $trip_origin->litreage = $this->loaded_litreage[$key] ?? null;
                    $trip_origin->litreage_at_20 = $this->loaded_litreage_at_20[$key] ?? null;
                    $trip_origin->rate = $this->loaded_rate[$key] ?? null;
                    $trip_origin->freight = $this->loaded_freight[$key] ?? null;
                    $trip_origin->save();
                }
            }

        } else {

            $trip_origin = new TripOrigin();
            $trip_origin->user_id = Auth::id();
            $trip_origin->trip_id = $trip_transport_order->trip_id;
            $trip_origin->transport_order_id = $trip_transport_order->transport_order_id;
            $trip_origin->trip_transport_order_id = $trip_transport_order->id;
            $trip_origin->destination_id = $this->selectedFrom;
            $trip_origin->loading_point_id = $this->loading_point_id;
            $trip_origin->weight = $this->weight;
            $trip_origin->quantity = $this->quantity;
            $trip_origin->units_of_measure_id = $this->units_of_measure_id ?: null;
            $trip_origin->litreage = $this->litreage;
            $trip_origin->litreage_at_20 = $this->litreage_at_20;
            $trip_origin->rate = $this->rate;
            $trip_origin->freight = $this->freight;
            $trip_origin->save();
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
                $this->cargos = Cargo::orderBy('name','asc')->get();
                $this->selectedStatus = "Offloaded";
                $trip_type = TripType::where('name','Local')->first();
                $this->selectedTripType = $trip_type ? $trip_type->id : Null;
                $this->trip_type_name =  $trip_type->name;
                $this->with_trailer = True;
                $this->timelines = True;
                $this->selectedTransporter = $shift->transporter_id;
                if($shift->horse_id){
                    $horse = $shift->horse;
                    $cluster = $horse?->cluster;
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


      public function updated($value){
          $this->validateOnly($value);
      }
      protected $messages =[
        'customer_id.required' => 'Please select a customer',
        'selectedHorse.required' => 'Please select a horse',
        'selectedVehicle.required' => 'Please select a vehicle',
        'driver_id.required' => 'Please select a driver',
        'selectedCargo.required' => 'Please select your cargo',
        'selectedTo.required' => 'Please select a starting point ',
        'selectedFrom.required' => 'Please select your destination ',

    ];
      protected $rules = [
          'customer_id' => 'required',
          'selectedHorse' => 'required',
          'selectedVehicle' => 'required',
          'selectedTransporter' => 'required',
          'trailer_id' => 'required',
          'driver_id' => 'required',
          'selectedTripType' => 'required',
          'selectedCargo' => 'required',
          'selectedCurrency' => 'required',
          'selectedFrom' => 'required',
          'selectedTo' => 'required',
          'litreage_at_20' => 'required',
          'weight' => 'required',
          'freight' => 'required',
          'start_date' => 'required',
          'manifest_number' => 'nullable|unique:trips,manifest_number,NULL,id,deleted_at,NULL|string',
          'cd3_number' => 'nullable|unique:trips,cd3_number,NULL,id,deleted_at,NULL|string',
          'cd1_number' => 'nullable|unique:trips,cd1_number,NULL,id,deleted_at,NULL|string',
          'bill_of_entry' => 'nullable|unique:trips,bill_of_entry,NULL,id,deleted_at,NULL|string',
          'selectedStatus' => 'required',
          'selectedContainer' => 'required',
          'selectedCategory' => 'required',
          'date' => 'required',
          'odometer' => 'required',
          'fuel_quantity' => 'required',
      ];



      public function updatedSelectedDefinedCustomerRate($id){
            if(!is_null($id)){
                $defined_customer_rate = Rate::find($id);
                $this->rate = $defined_customer_rate->rate;
                $this->freight = $defined_customer_rate->freight;
                $this->weight = $defined_customer_rate->weight;
                $this->litreage = $defined_customer_rate->litreage;
                $this->litreage_at_20 = $defined_customer_rate->litreage_at_20;
                $this->distance = $defined_customer_rate->distance;
                $this->from = $defined_customer_rate->from;
                $this->to = $defined_customer_rate->to;
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



    private function saveEmptyRun($trip, $isOrigin = true)
    {
        if ($isOrigin) {
            $emptyrun = EmptyRun::where('trip_id',$trip->id)->where('emptyrun_origin', True)->first();
            if($emptyrun){
                $emptyrun->trip_id = $trip->id;
                $emptyrun->emptyrun_origin = true;
                $emptyrun->distance = $this->emptyrun_origin_distance;
                $emptyrun->starting_mileage = $this->emptyrun_origin_starting_mileage;
                $emptyrun->ending_mileage = $this->emptyrun_origin_ending_mileage;
                $emptyrun->currency_id = $this->emptyrun_origin_currency_id;
                $emptyrun->fuel_quantity = $this->emptyrun_origin_fuel_quantity;
                $emptyrun->fuel_amount = $this->emptyrun_origin_fuel_amount;
                $emptyrun->update();
            }else{
                $emptyrun = new EmptyRun;
                $emptyrun->trip_id = $trip->id;
                $emptyrun->emptyrun_origin = true;
                $emptyrun->distance = $this->emptyrun_origin_distance;
                $emptyrun->starting_mileage = $this->emptyrun_origin_starting_mileage;
                $emptyrun->ending_mileage = $this->emptyrun_origin_ending_mileage;
                $emptyrun->currency_id = $this->emptyrun_origin_currency_id;
                $emptyrun->fuel_quantity = $this->emptyrun_origin_fuel_quantity;
                $emptyrun->fuel_amount = $this->emptyrun_origin_fuel_amount;
                $emptyrun->save();
            }
           
        } else {
            $emptyrun = EmptyRun::where('trip_id',$trip->id)->where('emptyrun_destination', True)->first();
            if($emptyrun){
             
                $emptyrun->trip_id = $trip->id;
                $emptyrun->emptyrun_destination = true;
                $emptyrun->distance = $this->emptyrun_destination_distance;
                $emptyrun->starting_mileage = $this->emptyrun_destination_starting_mileage;
                $emptyrun->ending_mileage = $this->emptyrun_destination_ending_mileage;
                $emptyrun->currency_id = $this->emptyrun_destination_currency_id;
                $emptyrun->fuel_quantity = $this->emptyrun_destination_fuel_quantity;
                $emptyrun->fuel_amount = $this->emptyrun_destination_fuel_amount;
                $emptyrun->update();
            }else{
                $emptyrun = new EmptyRun;
                $emptyrun->trip_id = $trip->id;
                $emptyrun->emptyrun_destination = true;
                $emptyrun->distance = $this->emptyrun_destination_distance;
                $emptyrun->starting_mileage = $this->emptyrun_destination_starting_mileage;
                $emptyrun->ending_mileage = $this->emptyrun_destination_ending_mileage;
                $emptyrun->currency_id = $this->emptyrun_destination_currency_id;
                $emptyrun->fuel_quantity = $this->emptyrun_destination_fuel_quantity;
                $emptyrun->fuel_amount = $this->emptyrun_destination_fuel_amount;
                $emptyrun->save();
            }
           
        }

       
    }
    

    private function syncRelations($trip)
    {
        if ($this->with_trailer && !empty($this->trailer_id)) {
            $trip->trailers()->detach();
            $trip->trailers()->sync($this->trailer_id);
        }
        if (!empty($this->selectedBorder)) {
            $trip->borders()->detach();
            $trip->borders()->sync($this->selectedBorder);
        }
        if (!empty($this->clearing_agent_id)) {
            $trip->clearing_agents()->detach();
            $trip->clearing_agents()->sync($this->clearing_agent_id);
        }
        if (!empty($this->truck_stop_id)) {
            $trip->truck_stops()->detach();
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
        $trip_expenses = TripExpense::where('trip_id', $trip_id)->get();
        
        $this->total_transporter_expenses = 0;
        $this->total_customer_expenses = 0;
        $this->total_expenses = 0;
        
        if ($trip_expenses->isNotEmpty()) {
            foreach ($trip_expenses as $expense) {
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

         public function billNumber(){

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

        $bill = Bill::latest()->orderBy('id','desc')->first();

        if (!$bill) {
            $bill_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $bill->id + 1;
            $bill_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $bill_number;


    }

      public function updatedDistance($distance){
        if (!is_null($distance)) {
            if (is_numeric($distance)) {
                $consumption = $this->with_cargos 
                    ? $this->fuel_consumption_loaded_standard 
                    : $this->fuel_consumption_empty_standard;

                if ($consumption && is_numeric($consumption)) {
                    $this->trip_fuel = $distance / $consumption;
                }
            }
            $this->calculateFreight();
        }
    }
    
    public function updatedSelectedStatus($status){
        
        $query = Horse::query()
            ->with('horse_make:id,name', 'horse_model:id,name')
            ->where('archive', 0);

            // Optional transporter filter
            if (!empty($this->selectedTransporter)) {
                $query->where('transporter_id', $this->selectedTransporter);
            }

        // Only enforce active/service filters when not in the special statuses
        $specialStatuses = ['Scheduled', 'Offloaded', 'Cancelled'];
        if (!in_array($status, $specialStatuses, true)) {
            $query->where('status', 1)
                ->where('service', 0);
        }

        // Order by registration number
        $this->horses = $query
            ->orderBy('registration_number', 'asc')
            ->get();
        
        $query = Vehicle::query()
            ->with('vehicle_make:id,name', 'vehicle_model:id,name')
            ->where('archive', 0);

            // Optional transporter filter
            if (!empty($this->selectedTransporter)) {
                $query->where('transporter_id', $this->selectedTransporter);
            }

        // Only enforce active/service filters when not in the special statuses
        $specialStatuses = ['Scheduled', 'Offloaded', 'Cancelled'];
        if (!in_array($status, $specialStatuses, true)) {
            $query->where('status', 1)
                ->where('service', 0);
        }

        // Order by registration number
        $this->vehicles = $query
            ->orderBy('registration_number', 'asc')
            ->get();

    }

      public function updatedSearchFrom(){
            $this->from_destinations = Destination::query()->with('country')
            ->where('city', 'like', '%'.$this->searchFrom.'%')
            ->orWhereHas('country', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchFrom.'%');
            })
            ->get()->sortBy('city')->sortBy('country.name');
    }
    public function updatedSearchTo(){
            $this->to_destinations = Destination::query()->with('country')
            ->where('city', 'like', '%'.$this->searchTo.'%')
            ->orWhereHas('country', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchTo.'%');
            })
            ->get()->sortBy('city')->sortBy('country.name');
    }
    public function updatedSearchLoadingPoint(){
            $this->loading_points = LoadingPoint::query()
            ->where('name', 'like', '%'.$this->searchLoadingPoint.'%')->get();
    }
    public function updatedSearchOffloadingPoint(){
            $this->offloading_points = OffloadingPoint::query()
            ->where('name', 'like', '%'.$this->searchOffloadingPoint.'%')->get();
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

    public function transportOrderNumber(){

        if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $transport_order = TransportOrder::orderBy('id','desc')->first();

        if (!$transport_order) {
            $transport_order_number =  $initials .'TO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $transport_order->id + 1;
            $transport_order_number =  $initials .'TO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $transport_order_number;

    }
    public function ttoNumber(){

        if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $transport_order = TripTransportOrder::orderBy('id','desc')->first();

        if (!$transport_order) {
            $transport_order_number =  $initials .'TO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $transport_order->id + 1;
            $transport_order_number =  $initials .'TO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $transport_order_number;

    }

    public function createTransportOrder()
    {
       // its safe because only trips with one TO gets to this stage
        $trip_transport_order = TripTransportOrder::where('trip_id', $this->trip_id)->first();

        $this->to_exists = $trip_transport_order ? True : False;

        $transport_order = $trip_transport_order
            ? TransportOrder::findOrNew($trip_transport_order->transport_order_id)
            : new TransportOrder();

        $transport_order->transport_order_number = $this->transportOrderNumber();
        $transport_order->custom_ref = $this->trip_ref;
        $transport_order->bill_of_entry = $this->bill_of_entry;
        $transport_order->user_id = $this->user->id ?? null;
        $transport_order->company_id = $this->company->id ?? null;
        $transport_order->quotation_id = $this->selectedQuotation ?: null;
        $transport_order->with_customer_rates = $this->with_customer_rates;
        $transport_order->with_transporter_rates = $this->with_transporter_rates;
        $transport_order->customer_id = $this->customer_id ?: null;
        $transport_order->consignee_id = $this->consignee_id ?: null;
        $transport_order->freight_calculation = $this->freight_calculation;
        $transport_order->calculation_measurement = $this->calculation_measurement;
        $transport_order->currency_id = $this->selectedCurrency ?: null;
        $transport_order->cargo_id = $this->selectedCargo;
        $transport_order->trip_type_id = $this->selectedTripType;
        $transport_order->defined_customer_rate_id = $this->selectedDefinedCustomerRate;
        $transport_order->defined_transporter_rate_id = $this->selectedDefinedTransporterRate;
        $transport_order->from = $this->selectedFrom;
        $transport_order->to = $this->selectedTo;
        $transport_order->offloading_point_id = $this->offloading_point_id;
        $transport_order->loading_point_id = $this->loading_point_id;
        $transport_order->start_date = $this->start_date;
        $transport_order->cargo_details = $this->cargo_details;
        $transport_order->end_date = $this->end_date;
        $transport_order->multiple_destinations = $this->multiple_destinations;
        $transport_order->quantity = $this->quantity;
        $transport_order->litreage = $this->litreage;
        $transport_order->units_of_measure_id = $this->units_of_measure_id ?: null;
        $transport_order->weight = $this->weight;
        $transport_order->status = 'Pending';
        $transport_order->rate = $this->rate;
        $transport_order->freight = $this->freight;
        $transport_order->transporter_rate = $this->transporter_rate;
        $transport_order->transporter_freight = $this->transporter_freight;
        $transport_order->exchange_rate = $this->exchange_rate;
        $transport_order->exchange_customer_freight = $this->exchange_customer_freight;
        $transport_order->distance = $this->distance;
        $transport_order->save();

        if($this->to_exists == False){
            $this->selectedTransportOrder[] = $transport_order?->id;
        }
 
        
    }

    public function createDeliveryNotes($trip_transport_order){
    
              
                $delivery_note = DeliveryNote::firstOrNew([
                    'trip_id' => $trip_transport_order->trip_id,
                    'transport_order_id' => $trip_transport_order->transport_order_id,
                ]);

                $delivery_note->user_id = $trip_transport_order->created_by;
                $delivery_note->transport_order_id = $trip_transport_order->transport_order_id;
                $delivery_note->units_of_measure_id = $trip_transport_order->units_of_measure_id ?: null;
                $delivery_note->distance = $trip_transport_order->distance;

                if (isset($trip->cargo)) {
                    if ($trip->cargo->type == "Liquid") {
                        $delivery_note->loaded_litreage = $trip->litreage;
                        $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
                    } elseif ($trip->cargo->type == "Solid") {
                        $delivery_note->loaded_quantity = $trip->quantity;
                    }
                }

                $delivery_note->loaded_weight = $trip_transport_order->allocated_weight;
                $delivery_note->loaded_rate = $trip_transport_order->rate;
                $delivery_note->loaded_freight = $trip_transport_order->freight;
                $delivery_note->transporter_loaded_rate = $trip_transport_order->transporter_rate;
                $delivery_note->transporter_loaded_freight = $trip_transport_order->transporter_freight;

                $delivery_note->save();

    }

    public function update(){

        DB::transaction(function () {

        $trip = Trip::find($this->trip_id);

        $hasTransportOrders = $trip && $trip->trip_transport_orders()->exists();

        if (!$this->attach_transport_order || !$hasTransportOrders) {
            $this->createTransportOrder();
        }
       

        $trip->trip_ref = $this->trip_ref;
        $trip->user_id =  $this->user->id ?: null;
        $trip->company_id = $this->company->id ?: null;
        $trip->horse_id = $this->mode_of_transport === "Horse" ? $this->selectedHorse : null;
        $trip->vehicle_id = $this->mode_of_transport === "Vehicle" ? $this->selectedVehicle : null;
        $trip->transporter_id = $this->selectedTransporter;
        $trip->trip_group_id = $this->trip_group_id ?: null;
        $trip->transporter_agreement = $this->transporter_agreement;
        $trip->temparature = $this->temparature;
        $trip->net_weight = $this->net_weight;
        $trip->seal_number = $this->seal_number;
        $trip->customer_updates = $this->customer_updates;
        $trip->fuel_order = $this->fuel_order;
        $trip->driver_id = $this->driver_id ?: null;
        $trip->initial_trip_id = $this->trip_type_name === "Return" ? $this->selectedTrip : null;
        $trip->cd3_number = $this->cd3_number;
        $trip->notes = $this->notes;
        $trip->arrive_loading_point = $this->arrive_lp;
        $trip->depart_loading_point = $this->depart_lp;
        $trip->loading_time = $this->calculateTimeDifference($this->arrive_lp, $this->depart_lp);
        $trip->arrive_offloading_point = $this->arrive_op;
        $trip->depart_offloading_point = $this->depart_op;
        $trip->offloading_time = $this->calculateTimeDifference($this->arrive_op, $this->depart_op);
        $trip->cd1_number = $this->cd1_number;
        $trip->bill_of_entry = $this->bill_of_entry;
        $trip->container_number = $this->container_number;
        $trip->manifest_number = $this->manifest_number;
        $trip->starting_mileage = $this->starting_mileage;
        $trip->starting_hours = $this->starting_hours;
        $trip->ending_mileage = $this->ending_mileage;
        $trip->ending_hours = $this->ending_hours;
        $trip->trip_fuel = $this->trip_fuel;
        $trip->trip_status = $this->selectedStatus;
        $trip->trip_status_date = $this->start_date;
        $trip->route_id = $this->selectedRoute;
        $trip->volume = $this->volume;
        $trip->comments = $this->comments;
        $trip->emptyrun_origin = $this->emptyrun_origin;
        $trip->emptyrun_destination = $this->emptyrun_destination;
        $trip->attach_transport_order = $this->attach_transport_order;
        $trip->start_date = $this->start_date;
        $trip->end_date = $this->end_date;
        $trip->currency_id = $this->selectedCurrency ?: $this->company->currency_id;
     

        if($this->attach_transport_order == False){
        
            $trip->agent_id = $this->agent_id ?: null;
            $trip->quotation_id = $this->selectedQuotation ?: null;
            $trip->with_customer_rates = $this->with_customer_rates;
            $trip->with_transporter_rates = $this->with_transporter_rates;
           
            $trip->broker_id = $this->selectedBroker ?: null;
            $trip->customer_id = $this->customer_id ?: null;
            $trip->consignee_id = $this->consignee_id ?: null;
            $trip->freight_calculation = $this->freight_calculation;
            $trip->calculation_measurement = $this->calculation_measurement;
            $trip->currency_id = $this->selectedCurrency ?: null;
            $trip->cargo_id = $this->selectedCargo;
            $trip->trip_type_id = $this->selectedTripType;
            $trip->haulage_type = $this->haulage_type;
            $trip->defined_customer_rate_id = $this->selectedDefinedCustomerRate;
            $trip->defined_transporter_rate_id = $this->selectedDefinedTransporterRate;
            $trip->from = $this->selectedFrom;
            $trip->to = $this->selectedTo;
            $trip->offloading_point_id = $this->offloading_point_id;
            $trip->loading_point_id = $this->loading_point_id;
           
            $trip->cargo_details = $this->cargo_details;
            $trip->with_cargos = $this->with_cargos;
            
            $trip->rate = $this->rate;
            $trip->multiple_destinations = $this->multiple_destinations;
            $trip->transporter_rate = $this->transporter_rate;
            $trip->quantity = $this->quantity;
            $trip->units_of_measure_id = $this->units_of_measure_id ?: Null;
            $trip->litreage = $this->litreage;
            $trip->litreage_at_20 = $this->litreage_at_20;
            $trip->weight = $this->weight;
          
            $trip->freight = $this->freight;
           
            $trip->transporter_freight = $this->transporter_freight;
            $trip->exchange_rate = $this->exchange_rate;
            $trip->exchange_customer_freight = $this->exchange_customer_freight;
            $trip->exchange_transporter_freight = $this->exchange_transporter_freight;
            $this->turnover = $this->company->currency_id ==  $this->selectedCurrency ? $this->freight : $this->exchange_customer_freight;
            $trip->turnover = $this->turnover;
            $trip->distance = $this->distance;

        }

        $trip->update();
       

        $this->trip = $trip;

        $totalFreight = 0;
        $totalWeight = 0;
        $totalLitreage = 0;
        
        

        if (!empty($this->current_selectedTransportOrder)) {
          
                foreach ($this->current_selectedTransportOrder as $key => $id) {
                   

                    $transport_order = TransportOrder::find($id);
                   
        
                    $trip_transport_order = TripTransportOrder::firstOrNew([
                        'trip_id' => $trip->id,
                        'transport_order_id' => $id,
                    ]);

                    if($this->attach_transport_order == True){
                          
                        $quantity = $this->current_allocated_quantity[$key] ?: Null;
                        $weight = $this->current_allocated_weight[$key] ?: Null;
                        $litreage = $this->current_allocated_litreage[$key] ?: Null;
                        $rate = $this->current_allocated_rate[$key] ?: Null;
                        $freight = $this->current_allocated_freight[$key] ?: Null;
                        $units_of_measure_id = $this->current_allocated_units_of_measure_id[$key] ?: Null;
                        $currency_id = $this->current_allocated_currency_id[$key] ?: Null;
                        $exchange_rate = $this->current_allocated_exchange_rate[$key] ?: Null;
                        $exchange_amount = $this->current_allocated_exchange_amount[$key] ?: Null;
                          $boe = $this->boe[$key] ?: Null;

                     
                        
                        if(is_numeric($freight) && is_numeric($weight) && is_numeric($transport_order->weight)){
                            $freight = $freight * $weight / $transport_order->weight;
                        }
                        

                        if(is_numeric($exchange_amount) && is_numeric($weight) && is_numeric($transport_order->weight)){
                            $exchange_amount = $exchange_amount *   $weight / $transport_order->weight;
                        } 
                        
                        $trip_transport_order->tto_number  = $trip_transport_order->tto_number ?: $this->ttoNumber();
                        $trip_transport_order->allocated_quantity  = $quantity;
                        $trip_transport_order->allocated_weight    = $weight;
                        $trip_transport_order->allocated_litreage  = $litreage;
                        $trip_transport_order->allocated_freight  = $freight;
                        $trip_transport_order->allocated_rate  = $rate;
                        $trip_transport_order->units_of_measure_id = $units_of_measure_id ?: Null;
                        $trip_transport_order->exchange_rate = $exchange_rate;
                        $trip_transport_order->exchange_amount = $exchange_amount;
                        $trip_transport_order->currency_id = $currency_id;
                         $trip_transport_order->bill_of_entry = $boe;

                    }else{
                              
                        if ($transport_order) {

                           
                            $trip_transport_order->tto_number  = $trip_transport_order->tto_number ?: $this->ttoNumber();
                            $trip_transport_order->allocated_quantity  = $transport_order->quantity;
                            $trip_transport_order->allocated_weight    = $transport_order->weight;
                            $trip_transport_order->allocated_litreage  = $transport_order->litreage;
                            $trip_transport_order->allocated_freight  = $transport_order->freight;
                            $trip_transport_order->allocated_rate  = $transport_order->rate;
                            $trip_transport_order->units_of_measure_id = $transport_order->units_of_measure_id ?: Null;
                            $trip_transport_order->currency_id = $transport_order->currency_id;
                            $trip_transport_order->exchange_rate = $transport_order->exchange_rate;
                            $trip_transport_order->exchange_amount = $transport_order->exchange_customer_freight;
                            $trip_transport_order->bill_of_entry = $this->bill_of_entry;

                        }

                    }

                    

                    $trip_transport_order->save();


                    $allSameCurrency = $trip->trip_transport_orders
                        ->pluck('currency_id')
                        ->unique()
                        ->count() === 1;

                    $useExchange = $trip->trip_transport_orders && $trip->trip_transport_orders->count() > 1 && !$allSameCurrency;

                    $freight = $useExchange
                        ? (float) ($trip_transport_order->exchange_amount ?? 0)
                        : (float) ($trip_transport_order->allocated_freight ?? 0);

                    $freight = is_numeric($freight) ? (float) $freight : 0;

                    $allocatedWeight = is_numeric($trip_transport_order->allocated_weight)
                        ? (float) $trip_transport_order->allocated_weight
                        : 0;

                    $allocatedLitreage = is_numeric($trip_transport_order->allocated_litreage)
                        ? (float) $trip_transport_order->allocated_litreage
                        : 0;

                    $totalFreight += $freight;
                    $totalWeight += $allocatedWeight;
                    $totalLitreage += $allocatedLitreage;
                    

                    $this->createDeliveryNotes($trip_transport_order);
                    
                }

                $trip->litreage = $totalLitreage;
                $trip->weight = $totalWeight;
                $trip->freight = $totalFreight;
                $this->turnover = $totalFreight;
                $trip->turnover = $this->turnover;
                $trip->update();
        }

        if (!empty($this->selectedTransportOrder)) {

           
                foreach ($this->selectedTransportOrder as $key => $id) {

                    $transport_order = TransportOrder::find($id);
        
                    $trip_transport_order = TripTransportOrder::firstOrNew([
                        'trip_id' => $trip->id,
                        'transport_order_id' => $id,
                    ]);

                    if($this->attach_transport_order == True){

                        $quantity = $this->allocated_quantity[$key] ?: Null;
                        $weight = $this->allocated_weight[$key] ?: Null;
                        $litreage = $this->allocated_litreage[$key] ?: Null;
                        $rate = $this->allocated_rate[$key] ?: Null;
                        $freight = $this->allocated_freight[$key] ?: Null;
                        $units_of_measure_id = $this->allocated_units_of_measure_id[$key] ?: Null;
                        $currency_id = $this->allocated_currency_id[$key] ?: Null;
                        $exchange_rate = $this->allocated_exchange_rate[$key] ?: Null;
                        $exchange_amount = $this->allocated_exchange_amount[$key] ?: Null;
                        
                        if(is_numeric($freight) && is_numeric($weight) && is_numeric($transport_order->weight)){
                            $freight = $freight * $weight / $transport_order->weight;
                        }
                        

                        if(is_numeric($exchange_amount) && is_numeric($weight) && is_numeric($transport_order->weight)){
                            $exchange_amount = $exchange_amount *   $weight / $transport_order->weight;
                        } 
                        
                        $trip_transport_order->tto_number  = $this->ttoNumber();
                        $trip_transport_order->allocated_quantity  = $quantity;
                        $trip_transport_order->allocated_weight    = $weight;
                        $trip_transport_order->allocated_litreage  = $litreage;
                        $trip_transport_order->allocated_freight  = $freight;
                        $trip_transport_order->allocated_rate  = $rate;
                        $trip_transport_order->units_of_measure_id = $units_of_measure_id ?: Null;
                        $trip_transport_order->exchange_rate = $exchange_rate;
                        $trip_transport_order->exchange_amount = $exchange_amount;
                        $trip_transport_order->currency_id = $currency_id;

                    }else{
                    
                        if ($transport_order) {
                        
                            $trip_transport_order->tto_number  = $this->ttoNumber();
                            $trip_transport_order->allocated_quantity  = $transport_order->quantity;
                            $trip_transport_order->allocated_weight    = $transport_order->weight;
                            $trip_transport_order->allocated_litreage  = $transport_order->litreage;
                            $trip_transport_order->allocated_freight  = $transport_order->freight;
                            $trip_transport_order->allocated_rate  = $transport_order->rate;
                            $trip_transport_order->units_of_measure_id = $transport_order->units_of_measure_id ?: Null;
                            $trip_transport_order->currency_id = $transport_order->currency_id;
                            $trip_transport_order->exchange_rate = $transport_order->exchange_rate;
                            $trip_transport_order->exchange_amount = $transport_order->exchange_customer_freight;

                        }

                    }

                    

                    $trip_transport_order->save();

                     $allSameCurrency = $trip->trip_transport_orders
                        ->pluck('currency_id')
                        ->unique()
                        ->count() === 1;

                    $useExchange = $trip->trip_transport_orders && $trip->trip_transport_orders->count() > 1 && !$allSameCurrency;

                    $freight = $useExchange
                        ? (float) ($trip_transport_order->exchange_amount ?? 0)
                        : (float) ($trip_transport_order->allocated_freight ?? 0);

                    $freight = is_numeric($freight) ? (float) $freight : 0;

                    $allocatedWeight = is_numeric($trip_transport_order->allocated_weight)
                        ? (float) $trip_transport_order->allocated_weight
                        : 0;

                    $allocatedLitreage = is_numeric($trip_transport_order->allocated_litreage)
                        ? (float) $trip_transport_order->allocated_litreage
                        : 0;

                    $totalFreight += $freight;
                    $totalWeight += $allocatedWeight;
                    $totalLitreage += $allocatedLitreage;

                    $this->createDeliveryNotes($trip_transport_order);
                 
                }

                    

                $trip->litreage = $totalLitreage;
                $trip->weight = $totalWeight;
                $trip->freight = $totalFreight;
                $this->turnover = $totalFreight;
                $trip->turnover = $this->turnover;
                $trip->update();

                $this->addDestinations($trip_transport_order);
                $this->addOrigins($trip_transport_order);
        }


          $this->calculateFuelConsumption($trip->id);

          if($this->emptyrun_origin) $this->saveEmptyRun($trip, true);
          if($this->emptyrun_destination) $this->saveEmptyRun($trip, false);

          $mileage =  Mileage::where('trip_id', $trip->id)->where('position','starting')->where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->first();
          
          $last_mileage = Mileage::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();

          if(isset($mileage)){
              $mileage->trip_id = $trip->id;
              $mileage->horse_id = $this->selectedHorse ?:  Null;
              $mileage->vehicle_id = $this->selectedVehicle ?: Null;
              if(isset($last_mileage->mileage) && ($last_mileage->mileage < $this->starting_mileage)){
                $mileage->mileage = $this->starting_mileage;
              }
              $mileage->date = $this->start_date;
              $mileage->position = "starting";
              $mileage->category = "Trip";
              $mileage->update();
          }else{
            if(isset($this->starting_mileage)){
                if(isset($last_mileage)){
                    if($last_mileage->mileage < $this->starting_mileage){
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
                    $mileage->mileage = $this->ending_mileage;
                    $mileage->date = $this->end_date;
                    $mileage->category = "Trip";
                    $mileage->position = "ending";
                    $mileage->save();
                }          
            }
          }
       
          $hours =  Hour::where('trip_id', $trip->id)->where('position','starting')->where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->first();
          
          $last_hours = Hour::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();

          if(isset($hours)){
              $hours->trip_id = $trip->id;
              $hours->horse_id = $this->selectedHorse ?:  Null;
              $hours->vehicle_id = $this->selectedVehicle ?: Null;
              if(isset($last_hours->hours) && ($last_hours->hours < $this->starting_hours)){
                $hours->hours = $this->starting_hours;
              }
              $hours->date = $this->start_date;
              $hours->position = "starting";
              $hours->category = "Trip";
              $hours->update();
          }else{
            if(isset($this->starting_hours)){
                if(isset($last_hours)){
                    if($last_hours->hours < $this->starting_hours){
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
                    $hours->hours = $this->ending_hours;
                    $hours->date = $this->end_date;
                    $hours->category = "Trip";
                    $hours->position = "ending";
                    $hours->save();
                }          
            }
          }
       
          $mileage =  Mileage::where('trip_id', $trip->id)->where('position','ending')->where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->first();
          $last_mileage = Mileage::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
          if(isset($mileage)){
              $mileage->trip_id = $trip->id;
              $mileage->horse_id = $this->selectedHorse ?:  Null;
              $mileage->vehicle_id = $this->selectedVehicle ?: Null;
              if(isset($last_mileage->mileage) && ($last_mileage->mileage < $this->ending_mileage)){
                $mileage->mileage = $this->ending_mileage;
              }
              $mileage->date = $this->start_date;
              $mileage->category = "Trip";
              $mileage->position = "ending";
              $mileage->update();
          }else{
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
          }

          $hours =  Hour::where('trip_id', $trip->id)->where('position','ending')->where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->first();
          $last_hours= Hour::where('horse_id',$this->selectedHorse)->orWhere('vehicle_id',$this->selectedVehicle)->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
          if(isset($hours)){
              $hours->trip_id = $trip->id;
              $hours->horse_id = $this->selectedHorse ?:  Null;
              $hours->vehicle_id = $this->selectedVehicle ?: Null;
              if(isset($last_hours->hours) && ($last_hours->hours < $this->ending_hours)){
                $hours->hours = $this->ending_hours;
              }
              $hours->date = $this->start_date;
              $hours->category = "Trip";
              $hours->position = "ending";
              $hours->update();
          }else{
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
          }

     

         
          $this->syncRelations($trip);
    
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
          
          if (isset($this->agent_id)) {

            $commission = $trip->commission()->firstOrNew(['trip_id' => $trip->id]);
            $commission->user_id =  $this->user->id ;
            $commission->trip_id =  $trip->id ;
            $commission->agent_id =  $this->agent_id ;
            $commission->commission =  $this->commission ;
            $commission->amount =  $this->commission_amount ;
            $commission->date =  $this->start_date ;
            $commission->status =  1 ;
            $commission->save();
           
        }

         if($this->shift == False){

              if ($this->fuel_order == True) {   

            $fuel = $trip->fuels->where('fillup',1)->first();

            if (isset($fuel)) {
            
                    $container = Container::find($this->selectedContainer);
                   
                    $fuel->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
                    $fuel->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                    $fuel->currency_id = $this->selectedFuelCurrency;
                    $fuel->trip_id = $trip->id;
                    
                    if (isset($this->selectedVehicle)) {
                        $fuel->type = "Vehicle";
                    }elseif(isset($this->selectedHorse)){
                        $fuel->type = "Horse";
                    }
                    
                    $fuel->driver_id = $this->driver_id ? $this->driver_id : Null;
                    $fuel->container_id = $this->selectedContainer ? $this->selectedContainer : Null;
                    $fuel->date = $this->date;
                    $fuel->unit_price = $this->unit_price;
                    $fuel->quantity = $this->fuel_quantity;
                    $fuel->amount = $this->fuel_amount;
                    $fuel->odometer = $this->odometer;
                    $fuel->hours = $this->hours;
                    $fuel->category = $this->fuel_category;
                    $fuel->exchange_amount = $this->fuel_exchange_amount;
                    $fuel->exchange_rate = $this->fuel_exchange_rate;
                    $fuel->fillup = 1;
                    $fuel->status = 1;
                    $fuel->comments = $this->fuel_comments;
                    $fuel->authorization = $trip->authorization;
                    $fuel->authorized_by_id = $trip->authroized_by_id;
                    $fuel->reason = $trip->reason;
                    $fuel->update();

                    $bill = Bill::where('trip_id',$trip->id)->where('fuel_id',$fuel->id)->first();

                    if(isset($bill)){
                        $bill->bill_date = $fuel->date;
                        $bill->currency_id = $fuel->currency_id;
                        $bill->total = $fuel->amount;
                        $bill->balance = $fuel->amount;
                        if($fuel->container->purchase_type == "Once Off Buy"){
                            $bill->to_be_paid = True;
                        }else{
                            $bill->to_be_paid = False;
                        }
                        $bill->update();

                        $fuel_expense = Expense::where('name','Fuel Topup')->first();
                        if($fuel_expense){
                            $bill_expense = BillExpense::where('bill_id', $bill->id)->where('expense_id', $fuel_expense->id)->first();
                            if($bill_expense){
                                $bill_expense->currency_id = $bill->currency_id;
                                $bill_expense->qty = $fuel->quantity;
                                $bill_expense->amount = $fuel->unit_price;
                                $bill_expense->subtotal = $fuel->amount;
                                $bill_expense->update();
                            }
                        }
                    
                    }
            
                    $trip_expense = TripExpense::where('fuel_id',$fuel->id)->where('trip_id',$trip->id)->first();
                    
                    if (isset($trip_expense)) {
                        $trip_expense->currency_id = $this->selectedFuelCurrency;
                        $trip_expense->category = $this->fuel_category;
                        $trip_expense->amount = $this->fuel_amount;
                        $trip_expense->exchange_rate = $this->fuel_exchange_rate;
                        $trip_expense->exchange_amount = $this->fuel_exchange_amount;
                        $trip_expense->update();

                       
                        $category = $this->fuel_category ?? null;
                        $currency_id = $this->selectedFuelCurrency ?? null;
                        $amount =  $this->fuel_amount ?? 0;
                        $exchange_amount = $this->fuel_exchange_amount ?? 0;
                    
                        // Add fuel to totals
                        $this->addToCategoryTotal(
                            $category,
                            $amount = ($currency_id == $this->company->currency_id) ? $amount : $exchange_amount
                        );
                    }
                    
          }else{

                $fuel = new Fuel;
                $fuel->user_id = $this->user->id;
                $fuel->order_number = $this->orderNumber();
                $fuel->horse_id = Horse::where('id', $this->selectedHorse)->exists() ? $this->selectedHorse : null;
                $fuel->vehicle_id = Horse::where('id', $this->selectedVehicle)->exists() ? $this->selectedVehicle : null;
                $fuel->currency_id = Currency::where('id', $this->selectedFuelCurrency)->exists() ? $this->selectedFuelCurrency : null;
                $fuel->trip_id = $trip->id;
                $fuel->type = isset($this->selectedVehicle) ? "Vehicle" : (isset($this->selectedHorse) ? "Horse" : null);
                $fuel->driver_id = Driver::where('id', $this->driver_id)->exists() ? $this->driver_id : null;
                $fuel->container_id = Container::where('id', $this->selectedContainer)->exists() ? $this->selectedContainer : null;
                $fuel->date = $this->date;
                $fuel->unit_price = $this->unit_price;
                $fuel->quantity = $this->fuel_quantity;
                $fuel->amount = $this->fuel_amount;
                $fuel->odometer = $this->odometer;
                $fuel->hours = $this->hours;
                $fuel->category = $this->fuel_category;
                $fuel->exchange_amount = $this->fuel_exchange_amount;
                $fuel->exchange_rate = $this->fuel_exchange_rate;
                $fuel->fillup = 1;
                $fuel->status = 1;
                $fuel->comments = $this->fuel_comments;

                $fuel->authorization = $trip->authorization;
                $fuel->authorized_by_id = $trip->authroized_by_id;
                $fuel->reason = $trip->reason;

                $fuel->save();

                $trip_expense = new TripExpense;
                $trip_expense->user_id = $this->user->id;
                $trip_expense->trip_id = $trip->id;
                $trip_expense->fuel_id = $fuel->id;
                $fuel_expense = Expense::where('name','Fuel Topup')->first();
                if (isset($fuel_expense)) {
                    $trip_expense->expense_id = $fuel_expense->id;
                }
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
                
                if($fuel->authorization == "approved"){

                     $container = Container::find($fuel->container_id);

                      if ($fuel->horse) {
                        $horse = Horse::find($fuel->horse_id);
                        if((isset($horse->fuel_balance) && is_numeric($horse->fuel_balance)) && (isset($fuel->quantity) && is_numeric($fuel->quantity))){
                            $horse->fuel_balance = $horse->fuel_balance + $fuel->quantity;
                        }
                        $current_mileage = $horse->mileage;
                        if ($fuel->odometer >  $current_mileage) {
                            $horse->mileage = $fuel->odometer;
                        }
                      
                        $horse->update();
                    }
                    if ($fuel->vehicle) {
                        $vehicle = Vehicle::find($fuel->vehicle_id);
                        if((isset($vehicle->fuel_balance) && is_numeric($vehicle->fuel_balance)) && (isset($fuel->quantity) && is_numeric($fuel->quantity))){
                            $vehicle->fuel_balance = $vehicle->fuel_balance + $fuel->quantity;
                        }
                        $current_mileage = $vehicle->mileage;
                        if ($fuel->odometer >  $current_mileage) {
                            $vehicle->mileage = $fuel->odometer;
                        }
                        $vehicle->update();
                    }

                    $last_mileage = Mileage::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
                    
                    if(isset($last_mileage)){
                        if($last_mileage < $fuel->odometer){
                            $mileage = new Mileage;
                            $mileage->user_id = Auth::user()->id;
                            $mileage->trip_id = $fuel->trip_id ? $fuel->trip_id : Null;
                            $mileage->fuel_id = $fuel->id;
                            $mileage->horse_id = $fuel->horse_id;
                            $mileage->vehicle_id = $fuel->vehicle_id;
                            $mileage->mileage = $fuel->odometer;
                            $mileage->date = $fuel->date;
                            $mileage->category = "Fuel Order";
                            $mileage->save();
                        }
                    }
                
                    $last_hours = Hour::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();

                    if(isset($last_hours)){
                        if($last_hours < $fuel->hours){
                            $hours = new Hour;
                            $hours->user_id = Auth::user()->id;
                            $hours->trip_id = $fuel->trip_id ? $fuel->trip_id : Null;
                            $hours->fuel_id = $fuel->id;
                            $hours->horse_id = $fuel->horse_id;
                            $hours->vehicle_id = $fuel->vehicle_id;
                            $hours->hours = $fuel->hours;
                            $hours->date = $fuel->date;
                            $hours->category = "Fuel Order";
                            $hours->save();
                        }
                    }

                    if($container && $container->purchase_type == "Bulk Buy"){
                        if($container->balance && is_numeric($container->balance) && ($fuel->quantity && is_numeric($fuel->quantity)) ){
                            if($container->balance >= $fuel->quantity){
                                $container->balance = $container->balance - $fuel->quantity;
                            } 
                        }
                        if($container->account_balance && is_numeric($container->account_balance) && ($fuel->amount && is_numeric($fuel->amount)) ){
                            if($container->account_balance >= $fuel->amount){
                                $container->account_balance = $container->account_balance - $fuel->amount;
                            }
                        }
                        $container->update();
                    } 

                    $account = Account::where('name','Trip Expense')->get()->first();

                    $bill = new Bill;
                    $bill->user_id = Auth::user()->id;
                    $bill->bill_number = $this->billNumber();
                    $bill->trip_id = $trip->id;
                    $bill->fuel_id = $trip_expense->fuel_id;
                    $bill->trip_expense_id = $trip_expense->id;
                    $bill->horse_id = $trip->horse_id;
                    $bill->vehicle_id = $trip->vehicle_id;
                    if (isset($account)) {
                        $bill->account_id = $account->id;
                        $bill->account_type_id = $account->account_type->id;
                    }
                    if($fuel->container->purchase_type == "Once Off Buy"){
                        $bill->to_be_paid = True;
                    }else{
                        $bill->to_be_paid = False;
                    }
                    $bill->driver_id = $trip->driver_id;
                    $bill->category = "Trip Expense - Fuel Order";
                    $bill->bill_date = date("Y-m-d");
                    $bill->currency_id = $trip_expense->currency_id;
                    $bill->subtotal = $trip_expense->amount;
                    $bill->total = $trip_expense->amount;
                    $bill->exchange_amount = $trip_expense->exchange_amount;
                    $bill->balance = $trip_expense->amount;
                    $bill->authorized_by_id = $fuel->authorized_by_id;
                    $bill->authorization = $fuel->authorization;
                    $bill->comments = $fuel->reason;
                    $bill->save();

                    $bill_expense = new BillExpense;
                    $bill_expense->user_id = Auth::user()->id;
                    $bill_expense->bill_id = $bill->id;
                    if (isset($account)) {
                        $bill_expense->account_id = $account->id;
                        $bill_expense->account_type_id = $account->account_type->id;
                    }
                    $bill_expense->currency_id = $bill->currency_id;
                    $bill_expense->expense_id = $trip_expense->expense_id;
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $trip_expense->amount;
                    $bill_expense->subtotal = $trip_expense->amount;
                    $bill_expense->subtotal_incl = $trip_expense->amount;
                    $bill_expense->save();
                }

            
          
            
            }
    
        }


         }

      
   
       
        if ($this->trip_expenses == True) {
         

            if ($trip->trip_expenses) {
                foreach ($trip->trip_expenses as $expense) {
                
                    if (isset($expense->category)  && (isset($expense->amount) && is_numeric($expense->amount)) && isset($expense->currency_id)) {
                        $category = $expense->category ?? null;
                        $currency_id = $expense->currency_id ?? null;
                        $amount = $expense->amount ?? 0;
                        $exchange_amount = $expense->exchange_amount ?? 0;
                        
                        $this->addToCategoryTotal(
                            $category,
                            $amount = ($currency_id == $this->company->currency_id) ? $amount : $exchange_amount
                        );

                    }      
            
                }
          
            }

        }

            if ($this->transporter_agreement) {

            $expense = Expense::where('name', 'Transporter Payment')->first();
            $trip_expense = TripExpense::where('trip_id',$trip->id)->where('transporter_id',$trip->transporter_id)->first();

            if( $trip_expense){
               
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
            
                $trip_expense->update();

                $bill = Bill::where('transporter_id',$trip->transporter_id)->where('trip_id',$trip->id)->first();

            if($bill){
                $bill->trip_id = $trip->id;
                $bill->category = "Transporter";
                $bill->transporter_id = $trip->transporter_id;
                $bill->bill_date = $trip->start_date;
                $bill->currency_id = $trip->currency_id;
                $bill->total = $freight_amount;
                $bill->balance =  $freight_amount;
                $bill->authorized_by_id = $trip->authorized_by_id;
                $bill->authorization = $trip->authorization;
                $bill->comments = $trip->reason;
                $bill->update();
    
                $bill_expense = $bill->bill_expenses->first();
                if (isset($bill_expense)) {
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->currency_id = $bill->currency_id;
                    $bill_expense->qty = 1;
                    $bill_expense->amount =  $freight_amount;
                    $bill_expense->subtotal =  $freight_amount;
                    $bill_expense->update();
        
                }
            }

            }else{
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

                if ($trip->authorization === "approved") {

                    $expense = Expense::where('name','Transporter Payment')->get()->first();
                    $account = Account::where('name','Trip Expense')->get()->first();

                    $bill = new Bill;
                    $bill->user_id = Auth::user()->id;
                    $bill->bill_number = $this->billNumber();
                    $bill->trip_id = $trip->id;
                    $bill->category = "Trip Expense - Transporter Payment";
                    $bill->transporter_id = $trip_expense->transporter_id;
                    $bill->trip_expense_id = $trip_expense->id;
                    $bill->bill_date = $trip->start_date;
                    if (isset($account)) {
                        $bill->account_id = $account->id;
                        $bill->account_type_id = $account->account_type->id;
                    }
                    $bill->currency_id = $trip_expense->currency_id;
                    $bill->subtotal = $trip_expense->amount;
                    $bill->total = $trip_expense->amount;
                    $bill->balance = $trip_expense->amount;

                    $bill->authorized_by_id = $trip->authorized_by_id;
                    $bill->authorization = $trip->authorization;
                    $bill->comments = $trip->reason;
                    $bill->save();

                   

                    $bill_expense = new BillExpense;
                    $bill_expense->user_id = Auth::user()->id;
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->currency_id = $bill->currency_id;
                    if (isset($expense)) {
                        $bill_expense->expense_id = $expense->id;
                    }
                    if (isset($account)) {
                        $bill_expense->account_id = $account->id;
                        $bill_expense->account_type_id = $account->account_type->id;
                    }
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $trip_expense->amount;
                    $bill_expense->subtotal = $trip_expense->amount;
                    $bill_expense->subtotal_incl = $trip_expense->amount;
                    $bill_expense->save();
                }
            }

            
        
        }

        $this->recalculateExpenses($trip->id);

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Updated Successfully!!"
        ]);

        return redirect()->route('trips.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something went wrong while updating trip!!"
    //     ]);
    // }
    
    });
    }


    public function updatedSelectedCurrency($id){
        if(!is_null($id)){
            $this->selected_currency = Currency::find($id);
            $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                ->where('status', 1)
                ->where('expiry', '>', Carbon::today())
                ->first();
            
            if ($predefined_exchange_rate) {   
                $this->exchange_rate = $predefined_exchange_rate->exchange_rate;
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
        public function updatedRate(){
            $this->calculateFreight();
    }
    public function updatedTransporterRate(){
            $this->calculateFreight();
    }
    public function updatedCalculationMeasurement(){
        $this->calculateFreight();
    }
    public function updatedWeight($value){
         $this->net_weight = $value;
        $this->calculateFreight();
    }
    public function updatedFreightCalculation(){
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
            if (($this->rate && is_numeric($this->rate))  && (($this->weight && is_numeric($this->weight)) || ($this->litreage_at_20 && is_numeric($this->litreage_at_20)) || ($this->litreage && is_numeric($this->litreage)))) {
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
            if (($this->rate  && is_numeric($this->rate))  && (($this->distance && is_numeric($this->distance)) )) {
                $this->freight = $this->rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
          
            if (($this->rate && is_numeric($this->rate)) && ($this->weight && is_numeric($this->weight)) || ($this->litreage_at_20 && is_numeric($this->litreage_at_20)) || ($this->litreage && is_numeric($this->litreage)) && ($this->distance && is_numeric($this->distance))) {
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
            if (($this->rate  && is_numeric($this->rate))) {
                if ($this->cargo_type == "Solid") {
                    $this->freight = $this->rate;
                }elseif($this->cargo_type == "Liquid"){
                    $this->freight = $this->rate ;
                }
            }
            
        }

        if ($this->freight_calculation == "rate_weight") {
            if (($this->transporter_rate && is_numeric($this->transporter_rate)) && (($this->weight && is_numeric($this->weight)) || ($this->litreage_at_20  && is_numeric($this->litreage_at_20)) || ($this->litreage  && is_numeric($this->litreage)))) {
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
            if (($this->transporter_rate && is_numeric($this->transporter_rate))  && (($this->distance && is_numeric($this->distance)) )) {
                $this->transporter_freight = $this->transporter_rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
            if (($this->transporter_rate && is_numeric($this->transporter_rate)) && (($this->weight && is_numeric($this->weight)) || ($this->litreage_at_20 && is_numeric($this->litreage_at_20)) || ($this->litreage && is_numeric($this->litreage))) && ($this->distance && is_numeric($this->distance))) {
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
            if (($this->transporter_rate && is_numeric($this->transporter_rate))) {
                if ($this->cargo_type == "Solid") {
                    $this->transporter_freight = $this->transporter_rate ;
                }elseif($this->cargo_type == "Liquid"){
                    $this->transporter_freight = $this->transporter_rate;
                } 
            }
            
        }

    }

    
        public function updatedSearchHorse()
    {
        $term = trim((string) $this->searchHorse);

        $query = Horse::query()
            ->with('horse_make:id,name', 'horse_model:id,name')
            ->where('registration_number', 'like', "%{$term}%")
            ->where('archive', 0);

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
            ->where('registration_number', 'like', "%{$term}%")
            ->where('archive', 0);

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
        
        public function updatedSearchTrailer()
        {
            $term = trim((string) $this->searchTrailer);

            $query = Trailer::query()
                ->where('registration_number', 'like', "%{$term}%")
                ->where('archive', 0);

            // Optional transporter filter
            if (!empty($this->selectedTransporter)) {
                $query->where('transporter_id', $this->selectedTransporter);
            }

            // Status & service conditions
            $specialStatuses = ['Scheduled', 'Offloaded', 'Cancelled'];
            if (!in_array($this->selectedStatus, $specialStatuses, true)) {
                $query->where('status', 1)
                    ->where('service', 0);
            }

            // Sort by registration number
            $this->trailers = $query
                ->orderBy('registration_number', 'asc')
                ->get();
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



        public function refresh($category){

            if($category == "tracking_groups"){
                $this->trip_groups = TripGroup::where('status',1)->latest()->get();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Trip Tracking Groups Refreshed Successfully!!."
                ]);
            }elseif($category == "borders"){
                $this->borders = Border::with('clearing_agents:id,name')->orderBy('name','asc')->get();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Borders Refreshed Successfully!!."
                ]);
            }
            elseif($category == "transport_orders"){
                $this->transport_orders = TransportOrder::where('authorization','approved')->latest()->get();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Transport Orders Refreshed Successfully!!."
                ]);
            }
             elseif($category == "shifts"){
            $this->shifts = Shift::where('for','Trips')->where('status',1)->latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Shifts Refreshed Successfully!!."
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
                $this->from_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
                $this->to_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
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
                 $this->cargos = Cargo::orderBy('name','asc')->get();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Cargos Refreshed Successfully!!."
                ]);
            }
            elseif($category == 'units_of_measures'){
                $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
                 
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Units of Measures Refreshed Successfully!!."
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

    public function updatedTripFuel($value){
        $this->calculateFuelTotal();
         // If the user touches this field, stop auto-calculation
        if ($value !== null && $value !== '') {
            $this->trip_fuel_locked = true;
        } else {
            // Optional: if they clear it, go back to auto mode
            $this->trip_fuel_locked = false;
        }
    }


      public function render()
      {

       if (!$this->trip_fuel_locked) { // only when NOT manually overridden

            if ($this->distance && is_numeric($this->distance)) {
                $consumption = $this->with_cargos
                    ? $this->fuel_consumption_loaded_standard   // km/L
                    : $this->fuel_consumption_empty_standard;   // km/L

                if ($consumption && is_numeric($consumption) && $consumption > 0) {
                    // km / (km per litre) = litres
                    $this->trip_fuel = $this->distance / $consumption;
                }
            }
        }
    
            return view('livewire.trips.edit');
    }
}
