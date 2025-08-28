<?php

namespace App\Http\Livewire\Shifts;

use Carbon\Carbon;
use App\Models\Fuel;
use App\Models\Team;
use App\Models\Work;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Shift;
use App\Models\TopUp;
use App\Models\Driver;
use App\Models\Company;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Container;
use App\Models\Rehandling;
use App\Models\Transporter;
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
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;

class Index extends Component
{

    use WithPagination;
    use WithFileUploads;


    protected $paginationTheme = 'bootstrap';
    public $search;
    public $shift_filter;
    protected $queryString = ['search'];
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
    public $type;
    public $for;
    public $date;
    public $user_id;
    public $containers;
    public $selectedContainer;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $customers;
    public $customer_id;
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

    public $teams;
    public $team_id;
    public $cargos;
    public $cargo_id;
    public $loading_points;
    public $loading_point_id;
    public $offloading_points;
    public $offloading_point_id;
    public $total_loads;

    public $works;
    public $work_id;
    public $locations;
    public $location_id;
    public $calculated_mileage;
    public $start_time;
    public $open_hours;
    public $open_mileage;
    public $stop_time;
    public $close_hours;
    public $close_mileage;
    public $weight;
    public $freight;
    public $exchange_rate;
    public $exchange_amount;

     //fuel vars

    public $company;
    public $employee;
    public $user;



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
    public $open_employee_id;
    public $closing_employee_id;
    public $fuel_id;
    public $importFile;
    public $shift_tripsimportFile;


    private function resetInputFields(){
        $this->name = '';
        $this->shift_start_time = '';
        $this->shift_end_time = '';
        $this->type = '';
        $this->team_id = '';
        $this->date = '';
        $this->location = '';
        $this->start_time = [];
        $this->stop_time = [];
        $this->work_id = [];
        $this->location_id = [];
        $this->open_mileage = [];
        $this->open_hours = [];
        $this->close_mileage = [];
        $this->close_hours = [];
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
        $this->dispatchBrowserEvent('hide-closeShiftModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Shift Closed Successfully!!"
        ]);
        
    }

    public function mount(){
        $this->resetPage();
        $this->user = Auth::user();
        $this->equipment = "Horse";
        $this->shift_filter = "created_at";
        $this->employee =  $this->user->employee;
        $this->team = $this->employee->teams->first();
        $this->team_id =  $this->team?->id;
        $this->company = Company::with('currency')->find( $this->employee->company_id);
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->selectedCurrency = 1;
        $this->drivers = Driver::all();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->transporters = Transporter::with('vehicles:id,registration_number','vehicles.vehicle_make:id,name','vehicles.vehicle_model:id,name','horses:id,registration_number','horses.horse_make:id,name','horses.horse_model:id,name','cargos:id,name','trailers:id,registration_number,make,model','drivers:id','drivers.employee:id,name,surname')->where('authorization','approved')->orderBy('name','asc')->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->containers = Container::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->works = Work::orderBy('description','asc')->get();
        $this->locations = Location::orderBy('name','asc')->get();
        $this->teams = Team::orderBy('name','asc')->get();
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
    

       public function refresh($category){

        if($category == "works"){
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
                $this->drivers = Driver::query()->with('employee:id,name,surname')->where('transporter_id',$id)
                ->withAggregate('employee','name')
                ->where('status', 1)
                ->where('archive',0)
                ->orderBy('employee_name','asc')->get();
            }
     
          
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'type' => 'required',
        'date' => 'required',
    ];

    public function exportShiftsCSV(Excel $excel){

        return $excel->download(new ShiftsExport($this->from, $this->to, $this->shift_filter, $this->search), 'shifts_'.time().'.csv', Excel::CSV);
    }
    public function exportShiftsPDF(Excel $excel){

        return $excel->download(new ShiftsExport($this->from, $this->to, $this->shift_filter, $this->search), 'shifts_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportShiftsExcel(Excel $excel){
        return $excel->download(new ShiftsExport($this->from, $this->to, $this->shift_filter, $this->search), 'shifts_'.time().'.xlsx');
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

        if (is_numeric($first->open_mileage) && is_numeric($last->close_mileage)) {
            $distance = $last->close_mileage - $first->open_mileage;
        }

        if (is_numeric($first->open_hours) && is_numeric($last->close_hours)) {
            $hours_distance = $last->close_hours - $first->open_hours;
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


    public function calculatedShiftDuration($shift){

        $start = Carbon::parse($shift->shift_start_time);
        $end = Carbon::parse($shift->shift_end_time);

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
    
    

    public function store(){

         DB::transaction(function () {
        // try{
      
        $shift = new Shift;
        $shift->user_id = Auth::user()->id;
        $shift->shift_number = $this->shiftNumber();
        $shift->for = $this->for;
        $shift->type = $this->type;
        $shift->shift_start_time = $this->shift_start_time;
        $shift->shift_end_time = $this->shift_end_time;
        $shift->customer_id = $this->customer_id;
        $shift->team_id = $this->team_id;
        $shift->driver_id = $this->driver_id;
        $shift->currency_id = $this->selectedCurrency;
        $shift->cargo_id = $this->cargo_id;
        $shift->transporter_id = $this->selectedTransporter;
        $shift->horse_id = $this->equipment === "Horse" ? $this->selectedHorse : null;
        $shift->vehicle_id = $this->equipment === "Vehicle" ? $this->selectedVehicle : null;
        $shift->equipment = $this->equipment;
        $shift->fuel_order = $this->fuel_order;
        $shift->open_employee_id = $this->open_employee_id;
        $shift->closing_employee_id = $this->closing_employee_id;
        $shift->depart_workshop_time = $this->depart_workshop_time;
        $shift->arrive_location_time = $this->arrive_location_time;
        $shift->depart_location_time = $this->depart_location_time;
        $shift->arrive_workshop_time = $this->arrive_workshop_time;
        $shift->total_loads = $this->total_loads;
        $shift->total_fuel = $this->fuel_quantity;
        $shift->open_mileage = $this->open_mileage;
        $shift->close_mileage = $this->close_mileage;

        if ($this->for === "Trips") {
            if (is_numeric($this->open_mileage) && is_numeric($this->close_mileage)) {
                $actual_mileage = $this->close_mileage - $this->open_mileage;
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

        if ($this->work_id) {
            foreach ($this->work_id as $key => $value) {
                $rehandling = new Rehandling;
                $rehandling->user_id = Auth::user()->id;
                $rehandling->shift_id = $shift->id;
                $rehandling->currency_id = $this->selectedCurrency;
                if (isset($this->location_id[$key])){
                    $rehandling->location_id = $this->location_id[$key];
                }
                if (isset($this->work_id[$key])){
                    $rehandling->work_id = $this->work_id[$key];
                }
                if (isset($this->start_time[$key])){
                    $rehandling->start_time = $this->start_time[$key];
                }
                if (isset($this->open_hours[$key])){
                    $rehandling->open_hours = $this->open_hours[$key];
                }
                if (isset($this->open_mileage[$key])){
                    $rehandling->open_mileage = $this->open_mileage[$key];
                }
                if (isset($this->close_mileage[$key])){
                    $rehandling->close_mileage = $this->close_mileage[$key];
                }
                if (isset($this->close_hours[$key])){
                    $rehandling->close_hours = $this->close_hours[$key];
                }
                if (isset($this->stop_time[$key])){
                    $rehandling->stop_time = $this->stop_time[$key];
                }
                if (isset($this->weight[$key])){
                    $rehandling->weight = $this->weight[$key];
                }
                if (isset($this->freight[$key])){
                    $rehandling->freight = $this->freight[$key];
                }
     
                $rehandling->save();
            }
        }
       
       

        if ($this->fuel_order) {
                $container = Container::find($this->selectedContainer);
                $fuel = new Fuel;
                $fuel->user_id = $shift->user->id;
                $fuel->order_number = $this->orderNumber();
                $fuel->horse_id = $this->selectedHorse ?? null;
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
//     }
//     catch(\Exception $e){
//     $this->dispatchBrowserEvent('hide-shiftModal');
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something goes wrong while creating shift!!"
//     ]);
// }
    });
    }



    public function edit($id){

    $shift = Shift::find($id);
    $this->for = $shift->for;
    $this->equipment = $shift->equipment;
    $this->shift_start_time = $shift->shift_start_time;
    $this->shift_end_time = $shift->shift_end_time;
    $this->date = $shift->date;
    $this->type = $shift->type;
    $this->updatedSelectedTransporter($shift->transporter_id);
    $this->selectedTransporter = $shift->transporter_id;
    $this->selectedHorse = $shift->horse_id;
    $this->cargo_id = $shift->cargo_id;
    $this->team_id = $shift->team_id;
    $this->driver_id = $shift->driver_id;
    $this->selectedVehicle = $shift->vehicle_id;
    $this->customer_id = $shift->customer_id;
    $this->depart_workshop_time = $shift->depart_workshop_time;
    $this->arrive_location_time = $shift->arrive_location_time;
    $this->depart_location_time = $shift->depart_location_time;
    $this->depart_location_time = $shift->depart_location_time;
    $this->calculated_mileage = $shift->calculated_mileage;
    $this->open_mileage = $shift->open_mileage;
    $this->close_mileage = $shift->close_mileage;
    $this->shift_id = $shift->id;
    $this->fuel_order = $shift->fuel_order;
    $this->total_loads = $shift->total_loads;
    $this->status = $shift->status;
    $loading_points = $shift->loading_points;
    if($loading_points){
        foreach ($loading_points as $loading_point) {
            $this->loading_point_id[] = $loading_point->id;
        }
    }
    $offloading_points = $shift->offloading_points;
    if($offloading_points){
        foreach ($offloading_points as $offloading_point) {
            $this->offloading_point_id[] = $offloading_point->id;
        }
    }
    
    $fuel = $shift->fuel;
    if($fuel){
    $this->selectedFuelCurrency = $fuel->currency_id;          
    $this->selectedContainer=  $fuel->container_id;
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
         DB::transaction(function () {
        if ($this->shift_id) {

            // try{

            $shift = Shift::find($this->shift_id);
            $shift->for = $this->for;
            $shift->type = $this->type;
            $shift->shift_start_time = $this->shift_start_time;
            $shift->shift_end_time = $this->shift_end_time;
            $shift->customer_id = $this->customer_id;
            $shift->driver_id = $this->driver_id;
            $shift->cargo_id = $this->cargo_id;
            $shift->team_id = $this->team_id;
            $shift->transporter_id = $this->selectedTransporter;
            $shift->horse_id = $this->equipment === "Horse" ? $this->selectedHorse : null;
            $shift->vehicle_id = $this->equipment === "Vehicle" ? $this->selectedVehicle : null;
            $shift->equipment = $this->equipment;
            $shift->fuel_order = $this->fuel_order;
            $shift->open_employee_id = $this->open_employee_id;
            $shift->closing_employee_id = $this->closing_employee_id;
            $shift->depart_workshop_time = $this->depart_workshop_time;
            $shift->arrive_location_time = $this->arrive_location_time;
            $shift->depart_location_time = $this->depart_location_time;
            $shift->arrive_workshop_time = $this->arrive_workshop_time;
            $shift->open_mileage = $this->open_mileage;
            $shift->close_mileage = $this->close_mileage;
            $shift->total_loads = $this->total_loads;
            $shift->total_fuel = $this->fuel_quantity;
            
            if ($this->for === "Trips") {
                if (is_numeric($this->open_mileage) && is_numeric($this->close_mileage)) {
                    $actual_mileage = $this->close_mileage - $this->open_mileage;
                    $shift->actual_mileage = $actual_mileage;

                    if ($actual_mileage > 0 && is_numeric($this->fuel_quantity) && $this->fuel_quantity > 0) {
                        $shift->fuel_consumption_mileage = $actual_mileage / $this->fuel_quantity;
                    }
                }
            }
            $shift->calculated_mileage = $this->calculated_mileage;
            $shift->exchange_amount = $this->exchange_amount;
            $shift->exchange_rate = $this->exchange_rate;
            $shift->date = $this->date;
            $shift->status = $this->status;
            $shift->update();

            $shift->loading_points()->detach();
            $shift->loading_points()->sync($this->loading_point_id);
            $shift->offloading_points()->detach();
            $shift->offloading_points()->sync($this->offloading_point_id);

                if ($this->fuel_order) {
                $fuel = $shift->fuel;
                if($fuel){
                        $container = Container::find($this->selectedContainer);
                        $fuel->horse_id = $this->selectedHorse ?? null;
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
                        $fuel->update();
                }else{
                        $container = Container::find($this->selectedContainer);
                        $fuel = new Fuel;
                        $fuel->user_id = $shift->user->id;
                        $fuel->order_number = $this->orderNumber();
                        $fuel->horse_id = $this->selectedHorse ?? null;
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
             
            
            }

            $this->dispatchBrowserEvent('hide-shiftEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Shift Updated Successfully!!"
            ]);

    //     }
    //     catch(\Exception $e){
    //     $this->dispatchBrowserEvent('hide-shiftEditModal');
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while updating shift!!"
    //     ]);
    // }
        }
    });
    
    }
    public function render()
    {

        if ((isset($this->fuel_exchange_rate) && $this->fuel_exchange_rate > 0 && is_numeric($this->fuel_exchange_rate)) && (isset($this->fuel_amount) && $this->fuel_amount > 0 && is_numeric($this->fuel_amount)) ) {
            $this->fuel_exchange_amount = $this->fuel_exchange_rate * $this->fuel_amount;
        }

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {
            $this->exchange_amount = $this->exchange_rate * $this->total;
        }

            if (filled($this->from) && filled($this->to)) {

                if (filled($this->search)) {
                  
                    return view('livewire.shifts.index', [
                        'shifts' => Shift::query()
                            ->with(['loading_points', 'offloading_points','customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])
                            ->whereDate($this->shift_filter, '>=', $this->from)
                            ->whereDate($this->shift_filter, '<=', $this->to)
                            ->where(function ($query) {
                                $query->where('shift_number','like', '%'.$this->search.'%')
                                    ->orWhere('type','like', '%'.$this->search.'%')
                                    ->orWhere('date','like', '%'.$this->search.'%')
                                    ->orWhere('for','like', '%'.$this->search.'%')
                                    ->orWhereHas('customer', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%');
                                    })
                                    ->orWhereHas('team', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%');
                                    })
                                    ->orWhereHas('horse', function ($q) {
                                        $q->where('registration_number', 'like', '%'.$this->search.'%')
                                        ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                    })
                                    ->orWhereHas('vehicle', function ($q) {
                                        $q->where('registration_number', 'like', '%'.$this->search.'%')
                                        ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                    })
                                    ->orWhereHas('cargo', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%');
                                    })
                                    ->orWhereHas('transporter', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%');
                                    })
                                    ->orWhereHas('driver.employee', function ($q) {
                                        $q->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%")
                                        ->orWhere('name', 'like', '%'.$this->search.'%')
                                        ->orWhere('surname', 'like', '%'.$this->search.'%');
                                    });
                            })
                            ->orderBy($this->shift_filter, 'desc')
                            ->paginate(10),
                    ]);
                }else {
                    return view('livewire.shifts.index',[
                        'shifts' => Shift::query()->with(['loading_points', 'offloading_points','customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])
                        ->whereDate($this->shift_filter, '>=', $this->from)
                        ->whereDate($this->shift_filter, '<=', $this->to)
                        ->orderBy($this->shift_filter,'desc')->paginate(10),

                    ]);
                }
            }
            elseif (filled($this->search)) {
                return view('livewire.shifts.index',[
                    'shifts' => Shift::query()->with(['loading_points', 'offloading_points','customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])
                    ->whereMonth($this->shift_filter, date('m'))
                    ->whereYear($this->shift_filter, date('Y'))
                    ->where(function ($query) {
                        $query->where('shift_number','like', '%'.$this->search.'%')
                            ->orWhere('type','like', '%'.$this->search.'%')
                            ->orWhere('date','like', '%'.$this->search.'%')
                            ->orWhere('for','like', '%'.$this->search.'%')
                            ->orWhereHas('customer', function ($q) {
                                $q->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('team', function ($q) {
                                    $q->where('name', 'like', '%'.$this->search.'%');
                                })
                            ->orWhereHas('horse', function ($q) {
                                $q->where('registration_number', 'like', '%'.$this->search.'%')
                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($q) {
                                $q->where('registration_number', 'like', '%'.$this->search.'%')
                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('cargo', function ($q) {
                                $q->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('transporter', function ($q) {
                                $q->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('driver.employee', function ($q) {
                                $q->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%")
                                ->orWhere('name', 'like', '%'.$this->search.'%')
                                ->orWhere('surname', 'like', '%'.$this->search.'%');
                            });
                    })
                    ->orderBy($this->shift_filter, 'desc')
                    ->paginate(10),
                ]);
            }
            else {
               
                return view('livewire.shifts.index',[
                    'shifts' => Shift::query()->with(['loading_points', 'offloading_points','customer:id,name','driver','horse','vehicle','cargo','transporter','fuel'])
                    ->whereMonth($this->shift_filter, date('m'))
                    ->whereYear($this->shift_filter, date('Y'))
                    ->orderBy($this->shift_filter,'desc')->paginate(10),
                ]);
              
            }
        
       
    }
}
