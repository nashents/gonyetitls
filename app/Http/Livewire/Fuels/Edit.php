<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Container;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithFileUploads;

    public $fuels;
    public $fuel_id;
    public $assets;
    public $categories;
    public $category_values;
    public $asset_id;
    public $trips;
    public $vehicle;
    public $currencies;
    public $fuel_tank_capacity;
    public $currency_id;
    public $selectedVehicle;
    public $vehicles;
    public $selected_horse;
    public $employees;
    public $employee;
    public $employee_id;
    public $horse;
    public $horses;
    public $selectedHorse;
    public $drivers;
    public $driver;
    public $driver_id;
    public $containers;
    public $container;
    public $type;
    public $horse_id;
    public $unit_price = 0;
    public $amount = 0;
    public $quantity = 0 ;
    public $mileage;
    public $date;
    public $fillup;
    public $invoice_number;
    public $fuel_consumption;
    public $distance;
    public $file;
    public $comments;

    public $user_id;
    public $selectedContainer;
    public $container_balance;
    public $selectedCategory;
    public $selectedCategoryValue;
    public $selectedTrip;


    public function mount($id){

        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $period = Auth::user()->employee->company->period;
            if (isset( $period)) {
                if ($period != "all") {
                    $this->fuels = Fuel::whereYear('created_at',$period)->latest()->get();
                }else {
                    $this->fuels = Fuel::latest()->get();
                }
            }
        } else {
            $period = Auth::user()->employee->company->period;
            if (isset( $period)) {
                if ($period != "all") {
                    $this->fuels = Fuel::where('user_id', Auth::user()->id)->whereYear('created_at',$period)->latest()->get();
                }else {
                    $this->fuels = Fuel::where('user_id', Auth::user()->id)->latest()->get();
                }
            }

        }

        $this->horses = Horse::where('service',0)->orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::where('service',0)->orderBy('registration_number','asc')->get();
        $this->assets = Asset::latest()->get();
        $this->categories = Category::latest()->get();
        $this->category_values = collect();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->trips = Trip::where('authorization','approved')->orderBy('trip_number','desc')->latest()->get();
        $this->containers = Container::where('balance','>',0)->orderBy('name','asc')->latest()->get();
        $this->drivers = Driver::latest()->get();

        
        $fuel = Fuel::find($id);
        $this->user_id = $fuel->user_id;
        $this->selectedHorse = $fuel->horse_id;
        $this->selectedVehicle = $fuel->vehicle_id;
        $this->selectedTrip = $fuel->trip_id;
        $this->asset_id = $fuel->asset_id;
        $this->selectedContainer = $fuel->container_id;
        $this->driver_id = $fuel->driver_id;
        $this->date = $fuel->date;
        $this->fillup = $fuel->fillup;
        $this->container = Container::find($this->selectedContainer);
        $this->container_balance = $this->container->balance;
        $this->type = $fuel->type;
        $this->mileage = $fuel->odometer;
        $this->comments = $fuel->comments;
        $this->amount = $fuel->amount;
        $this->unit_price = $fuel->unit_price;
        $this->currency_id = $fuel->currency_id;
        $this->quantity = $fuel->quantity;
        $this->fuel_id = $fuel->id;
        if ($fuel->asset_id) {
            $this->selectedCategory = Asset::find($fuel->asset_id)->category_id;
        }
    }

    public function updatedSelectedHorse($horse)
    {
        if (!is_null($horse) ) {
            $this->horse = Horse::find($horse);
            $this->selected_horse = Horse::find($horse);
            $this->horse_id =$horse;
            $this->mileage = $this->horse->mileage;
            $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
            }
    }
    public function updatedSelectedContainer($container)
    {
        if (!is_null($container) ) {
            $this->container = Container::find($container);
            $top_ups = TopUp::where('container_id',$this->container->id)->where('rate','!=', NULL)->where('currency_id',$this->container->currency_id)->get();
            $topups_price_total = TopUp::where('container_id',$this->container->id)->where('rate','!=', NULL)->where('currency_id',$this->container->currency_id)->get()->sum('rate');
            $topups_count = $top_ups->count();
            if ((isset($topups_count) && $topups_count > 0) && (isset($topups_price_total) && $topups_price_total > 0)) {
                $this->unit_price = number_format($topups_price_total/$topups_count,2);
            }
        
            $this->container_balance = $this->container->balance;
            $this->currency_id = $this->container->currency_id;
            }
    }

    public function updatedSelectedVehicle($vehicle)
    {
        if (!is_null($vehicle) ) {
        $this->vehicle = Vehicle::find($vehicle);
        $this->mileage = $this->vehicle->mileage;
        }
    }
 
    public function updatedSelectedCategory($category)
    {
        if (!is_null($category) ) {
        $this->category_values = CategoryValue::where('category_id', $category)->get();
        }
    }
    public function updatedSelectedCategoryValue($category_value)
    {
        if (!is_null($category_value) ) {
        $this->assets = Asset::where('category_value_id', $category_value)->get();
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedVehicle' => 'required',
        'selectedHorse' => 'required',
        // 'driver_id' => 'required',
        'selectedContainer' => 'required',
        // 'selectedTrip' => 'required',
        'selectedCategory' => 'required',
        'asset_id' => 'required',
        'date' => 'required',
        'mileage' => 'required',
        'quantity' => 'required',
        'amount' => 'required',
        'fillup' => 'required',
        'type' => 'required',
        'invoice_number' => 'nullable',
        'file' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
        'comments' => 'nullable',
    ];


    public function update()
    {
        // try{
 
        if ($this->fuel_id) {

            $trip = Trip::find($this->selectedTrip);

            $fuel = Fuel::find($this->fuel_id);
            $fuel->vehicle_id = $this->selectedVehicle;
            $fuel->employee_id = $this->employee_id;
            $fuel->horse_id = $this->selectedHorse;
            $fuel->asset_id = $this->asset_id;
            if (isset($trip)) {
                $fuel->driver_id = $trip->driver_id;
            }
            $fuel->container_id = $this->selectedContainer;
            $fuel->date = $this->date;
            $fuel->unit_price = $this->unit_price;
            $fuel->quantity = $this->quantity;
            $fuel->currency_id = $this->currency_id;
            $fuel->amount = $this->amount;
            $fuel->odometer = $this->mileage;
            $fuel->fillup = $this->fillup;
            $fuel->type = $this->type;
            $fuel->comments = $this->comments;

            $fuel->update();
          
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Order Updated Successfully!!"
            ]);
            return redirect()->route('fuels.manage');

        }
    // }
    //     catch(\Exception $e){
    //     return redirect()->back();
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something went wrong while updating fuel order!!"
    //     ]);
    // }
    }
    
    public function render()
    {
        $container = Container::find($this->selectedContainer);
        $this->containers = Container::where('balance','>',0)->orderBy('name','asc')->latest()->get();
      
        if(($this->unit_price != null) && ($this->quantity != null)){
            $this->amount = $this->unit_price * $this->quantity;
        }
        $this->selected_horse = Horse::find($this->horse_id);
        if ( $this->selected_horse) {
            $this->fuel_tank_capacity = $this->selected_horse->fuel_tank_capacity;
            // $this->mileage = $this->selected_horse->mileage;
        }
        return view('livewire.fuels.edit',[
            'amount' => $this->amount,
            'fuels' => $this->fuels,
            'unit_price' => $this->unit_price,
            'type' => $this->type,
            'containers' => $this->containers,
            'fuel_tank_capacity'=>$this->fuel_tank_capacity,
            'mileage'=>$this->mileage,
            'selected_horse'=>$this->selected_horse,
        ]);
    }
}
