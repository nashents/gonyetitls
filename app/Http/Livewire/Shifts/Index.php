<?php

namespace App\Http\Livewire\Shifts;

use App\Models\Fuel;
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
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\ShiftsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

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
    public $selectedCurrency ;
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

    public $cargos;
    public $cargo_id;

    public $works;
    public $work_id;
    public $locations;
    public $location_id;
    public $start_time;
    public $open_hours;
    public $open_mileage;
    public $stop_time;
    public $close_hours;
    public $close_mileage;
    public $weight;
    public $freight;
    public $currency_id;

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


    private function resetInputFields(){
        $this->name = '';
        $this->shift_start_time = '';
        $this->shift_end_time = '';
        $this->type = '';
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

   

    public function mount(){
        $this->resetPage();
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->company = Company::with('currency')->find( $this->employee->company_id);
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->drivers = Driver::all();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->transporters = Transporter::with('vehicles:id,registration_number','vehicles.vehicle_make:id,name','vehicles.vehicle_model:id,name','horses:id,registration_number','horses.horse_make:id,name','horses.horse_model:id,name','cargos:id,name','trailers:id,registration_number,make,model','drivers:id','drivers.employee:id,name,surname')->where('authorization','approved')->orderBy('name','asc')->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->containers = Container::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->works = Work::orderBy('description','asc')->get();
        $this->locations = Location::orderBy('name','asc')->get();
    }


       public function refresh($category){

        if($category == "works"){
            $this->works = Work::where('status',1)->orderBy('description','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Work Descriptions Refreshed Successfully!!."
            ]);
        }elseif($category == "locations"){
            $this->locations = Location::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Locations Refreshed Successfully!!."
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

             public function updatedSelectedFuelCurrency($id){
                    if(!is_null($id)){
                        $this->selected_fuel_currency = Currency::find($id);
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

        return $excel->download(new ShiftsExport, 'shifts.csv', Excel::CSV);
    }
    public function exportShiftsPDF(Excel $excel){

        return $excel->download(new ShiftsExport, 'shifts.pdf', Excel::DOMPDF);
    }
    public function exportShiftsExcel(Excel $excel){

        return $excel->download(new ShiftsExport, 'shifts.xlsx');
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
    

    public function store(){
        // try{
        $shift = new Shift;
        $shift->user_id = Auth::user()->id;
        $shift->shift_number = $this->shiftNumber();
        $shift->for = $this->for;
        $shift->type = $this->type;
        $shift->shift_start_time = $this->shift_start_time;
        $shift->shift_end_time = $this->shift_end_time;
        $shift->customer_id = $this->customer_id;
        $shift->driver_id = $this->driver_id;
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
        $shift->date = $this->date;
        $shift->status = '1';
        $shift->save();

        if ($this->work_id) {
            foreach ($this->work_id as $key => $value) {
                $rehandling = new Rehandling;
                $rehandling->user_id = Auth::user()->id;
                $rehandling->shift_id = $shift->id;
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
                if (isset($this->currency_id[$key])){
                    $rehandling->currency_id = $this->currency_id[$key];
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
    }



    public function edit($id){

    $shift = Shift::find($id);
    $this->user_id = $shift->user_id;
    $this->name = $shift->name;
    $this->shift_start_time = $shift->shift_start_time;
    $this->shift_end_time = $shift->shift_end_time;
    $this->date = $shift->date;
    $this->type = $shift->type;
    $this->location = $shift->location;
    $this->long = $shift->long;
    $this->status = $shift->status;
    $this->expiry_date = $shift->expiry_date;
    $this->description = $shift->description;
    $this->shift_id = $shift->id;
    $this->fuel_order = $shift->fuel_order;
    $this->fuel_id = $shift->fuel->id ?? null;


    $this->dispatchBrowserEvent('show-shiftEditModal');

    }

    public function update()
    {
        if ($this->shift_id) {
            try{
            $shift = Shift::find($this->shift_id);
             $shift->name = $this->name;
            $shift->shift_start_time = $this->shift_start_time;
            $shift->shift_end_time = $this->shift_end_time;
            $shift->customer_id = $this->customer_id;
            $shift->driver_id = $this->driver_id;
            $shift->open_employee_id = $this->open_employee_id;
            $shift->closing_employee_id = $this->closing_employee_id;
            $shift->type = $this->type;
            $shift->depart_workshop_time = $this->depart_workshop_time;
            $shift->arrive_location_time = $this->arrive_location_time;
            $shift->depart_location_time = $this->depart_location_time;
            $shift->arrive_workshop_time = $this->arrive_workshop_time;
            $shift->date = $this->date;
            $shift->status = $this->status;
            $shift->update();

                if ($this->fuel_order) {
                $container = Container::find($this->selectedContainer);
                $fuel = Fuel::find($this->fuel_id);
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
            
            }

            $this->dispatchBrowserEvent('hide-shiftEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Shift Updated Successfully!!"
            ]);

        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-shiftEditModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating shift!!"
        ]);
    }
        }
    }
    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.shifts.index',[
                'shifts' => Shift::where('name','like', '%'.$this->search.'%')
                ->orWhere('shift_start_time','like', '%'.$this->search.'%')
                ->orWhere('expiry_date','like', '%'.$this->search.'%')
                ->orWhere('shift_end_time','like', '%'.$this->search.'%')
                ->orWhere('type','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orderBy('created_at','desc')->paginate(10),
               
            ]);
        }else {
            return view('livewire.shifts.index',[
                'shifts' => Shift::orderBy('created_at','desc')->paginate(10)
            ]);
        }
       
    }
}
