<?php

namespace App\Http\Livewire\Rehandlings;

use App\Models\Fuel;
use App\Models\Work;
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
use App\Exports\RehandlingsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    private $rehandlings;
    public $shifts;
    public $shift_id;
    public $works;
    public $work_id;
    public $locations;
    public $location_id;
    public $status;
    public $name;
   

    private function resetInputFields(){
        $this->name = '';
    }

   

    public function mount(){
        $this->resetPage();
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->works = Work::orderBy('description','asc')->get();
        $this->locations = Location::orderBy('name','asc')->get();
        $this->company = Company::with('currency')->find( $this->employee->company_id);
        $this->shifts = Shift::all();
       
       
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'shift_id' => 'required',
    ];

  

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


    public function store(){
        try{

        $shift = new Rehandling;
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
        $shift->fue_order = $this->fue_order;
        $shift->open_employee_id = $this->open_employee_id;
        $shift->closing_employee_id = $this->closing_employee_id;
     
        $shift->depart_workshop_time = $this->depart_workshop_time;
        $shift->arrive_location_time = $this->arrive_location_time;
        $shift->depart_location_time = $this->depart_location_time;
        $shift->arrive_workshop_time = $this->arrive_workshop_time;
        $shift->date = $this->date;
        $shift->loads = $this->loads;
        $shift->status = '1';
        $shift->save();

        $this->dispatchBrowserEvent('hide-shiftModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rehandling Created Successfully!!"
        ]);
    }
    catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-shiftModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something goes wrong while creating shift!!"
    ]);
}
    }



    public function edit($id){

    $shift = Rehandling::find($id);
    $this->user_id = $shift->user_id;
    $this->name = $shift->name;
    $this->shift_start_time = $shift->shift_start_time;
    $this->shift_end_time = $shift->shift_end_time;
    $this->date = $shift->date;
    $this->type = $shift->type;
    $this->location = $shift->location;
    $this->loads = $shift->loads;
    $this->long = $shift->long;
    $this->status = $shift->status;
    $this->expiry_date = $shift->expiry_date;
    $this->description = $shift->description;
    $this->shift_id = $shift->id;
    $this->fuel_order = $shift->fuel_order;
    $this->fuel_id = $shift->fuel->id ?? null;

    $this->dispatchBrowserEvent('initializeAutocomplete', [
        'name' => $this->name,
        'loads' => $this->loads,
        'long' => $this->long,
        'location' => $this->location
    ]);

    $this->dispatchBrowserEvent('show-shiftEditModal');

    }

    public function update()
    {
        if ($this->shift_id) {
            try{
            $shift = Rehandling::find($this->shift_id);
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
            $shift->loads = $this->loads;
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
                'message'=>"Rehandling Updated Successfully!!"
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
            return view('livewire.rehandlings.index',[
                'rehandlings' => Rehandling::where('name','like', '%'.$this->search.'%')
                ->orWhere('loads','like', '%'.$this->search.'%')
                ->orWhere('long','like', '%'.$this->search.'%')
                ->orWhere('shift_start_time','like', '%'.$this->search.'%')
                ->orWhere('expiry_date','like', '%'.$this->search.'%')
                ->orWhere('shift_end_time','like', '%'.$this->search.'%')
                ->orWhere('type','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orderBy('created_at','desc')->paginate(10),
               
            ]);
        }else {
            return view('livewire.rehandlings.index',[
                'rehandlings' => Rehandling::orderBy('created_at','desc')->paginate(10)
            ]);
        }
       
    }
}
