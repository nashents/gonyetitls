<?php

namespace App\Http\Livewire\Allocations;

use App\Models\TopUp;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Container;
use App\Models\Allocation;
use Illuminate\Support\Str;
use App\Http\Livewire\Employees;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $allocations;
    public $allocation_number;
    public $allocation_type;
    public $allocation_id;
    public $selectedVehicle;
    public $vehicles;
    public $selected_vehicle;
    public $employee_id;
    public $employees;
    public $selectedContainer = NULL;
    public $containers;
 
    public $quantity = 0;
    public $rate;
    public $amount = 0;
    public $status;
    public $fuel_type;
    public $fuel_tank_capacity;
    public $container_balance;
    public $balance;
    public $expiry;

    public $user_id;


    private function resetInputFields(){
        $this->selectedVehicle = '';
        $this->employee_id = '';
        $this->allocation_number = '';
        $this->allocation_type = '';
        $this->rate = '';
        $this->quantity = '';
        $this->selectedContainer = '';
        $this->balance = '';
        $this->expiry = '';
        $this->fuel_type = '';
        $this->amount = '';
    }

    public function mount(){
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
            $this->allocations = Allocation::whereDate('created_at', \Carbon\Carbon::today())->latest()->take(5)->get();
        } else {
            $this->allocations = Allocation::where('user_id',Auth::user()->id)
        ->whereDate('created_at', \Carbon\Carbon::today())->latest()->take(5)->get();
        }
        $this->employees = Employee::all()->sortBy('name');
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->containers = Container::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedContainer' => 'required',
        'allocation_type' => 'required',
        'allocation_number' => 'required',
        'fuel_type' => 'required',
        'employee_id' => 'required',
        'selectedVehicle' => 'required',
        'quantity' => 'required',
        'rate' => 'required',
        'expiry' => 'required',
        'amount' => 'required',
        'balance' => 'required',
    ];
    public function allocationNumber(){
       
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

            $allocation = Allocation::orderBy('id', 'desc')->first();

        if (!$allocation) {
            $allocation_number =  $initials .'FA'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $allocation->id + 1;
            $allocation_number =  $initials .'FA'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $allocation_number;


    }

    public function updatedSelectedContainer($container)
    {
        if (!is_null($container) ) {
            $this->container = Container::find($container);
            $top_ups = TopUp::where('container_id',$container)->where('rate','!=', NULL)->where('currency_id',$this->container->currency_id)->get();
            $topups_price_total = TopUp::where('container_id',$this->container->id)->where('rate','!=', NULL)->where('currency_id',$this->container->currency_id)->get()->sum('rate');
            $topups_count = $top_ups->count();
            if ((isset($topups_count) && $topups_count > 0) && (isset($topups_price_total) && $topups_price_total > 0)) {
                $this->rate = number_format($topups_price_total/$topups_count,2);
            }
            $this->container_balance = $this->container->balance;
            $this->currency_id = $this->container->currency_id;
            $this->amount = $this->rate * $this->quantity;
        }

   
    }
    public function updatedSelectedVehicle($id)
    {
        if (!is_null($id) ) {
        $this->fuel_type = Vehicle::find($id)->fuel_type;
        $this->fuel_tank_capacity = Vehicle::find($id)->fuel_tank_capacity;
        $this->selected_vehicle = Vehicle::find($id);
        $this->selectedVehicle = $id;
        if ($this->fuel_type) {
            $this->containers = Container::where('fuel_type', $this->fuel_type)->orderBy('name','asc')->get();
        }else {
            $this->containers = Container::orderBy('name','asc')->get();
        }
       
        }
    }

    public function store(){

        try {
           
        $previous_allocation = Allocation::where('employee_id',$this->employee_id)->latest()->first();
        if ($previous_allocation) {
            $previous_allocation->status = '0';
            $previous_allocation->update();
        }


        $allocation = new Allocation;
        $allocation->user_id = Auth::user()->id;
        $allocation->allocation_number = $this->allocationNumber();
        $allocation->allocation_type = $this->allocation_type;
        $allocation->fuel_type = $this->fuel_type;
        $allocation->vehicle_id = $this->selectedVehicle;
        $allocation->employee_id = $this->employee_id;
        $allocation->container_id = $this->selectedContainer;
        $allocation->expiry_date = $this->expiry;
        $allocation->quantity = $this->quantity;
        $allocation->rate = $this->rate;
        $allocation->amount = $this->amount;
        $allocation->balance = $this->quantity;
        $allocation->status = '1';

        $allocation->save();



        $this->dispatchBrowserEvent('hide-allocationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Allocation Created Successfully!!"
        ]);
    }
    catch(\Exception $e){
    // Set Flash Message
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while creating agent!!"
    ]);
}

    }

    public function edit($id){
    $allocation = Allocation::find($id);
    $this->user_id = $allocation->user_id;
    $this->selectedVehicle = $allocation->vehicle_id;
    $this->employee_id = $allocation->employee_id;
    $this->selectedContainer = $allocation->container_id;
    $this->allocation_type = $allocation->allocation_type;
    $this->fuel_type = $allocation->fuel_type;
    $this->expiry = $allocation->expiry_date;
    $this->quantity = $allocation->quantity;
    $this->rate = $allocation->rate;
    $this->amount = $allocation->amount;
    $this->status = $allocation->status;
    $this->balance = $allocation->balance;
    $this->allocation_id = $allocation->id;
    $this->dispatchBrowserEvent('show-allocationEditModal');

    }


    public function update()
    {
        if ($this->allocation_id) {
            $allocation = Allocation::find($this->allocation_id);
            $allocation = Allocation::find($this->allocation_id);
            $allocation->user_id = Auth::user()->id;
            $allocation->vehicle_id = $this->selectedVehicle;
            $allocation->employee_id = $this->employee_id;
            $allocation->container_id = $this->selectedContainer;
            $allocation->fuel_type = $this->fuel_type;
            $allocation->allocation_type = $this->allocation_type;
            $allocation->quantity = $this->quantity;
            $allocation->rate = $this->rate;
            $allocation->amount = $this->amount;
            $allocation->balance = $this->balance;
            $allocation->update();

            $this->dispatchBrowserEvent('hide-allocationEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allocation Updated Successfully!!"
            ]);


            // return redirect()->route('allocations.index');

        }
    }

    public function render()
    {
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
            $this->allocations = Allocation::whereDate('created_at', \Carbon\Carbon::today())->latest()->take(5)->get();
        } else {
            $this->allocations = Allocation::where('user_id',Auth::user()->id)
        ->whereDate('created_at', \Carbon\Carbon::today())->latest()->take(5)->get();
        }

        if ($this->quantity != null && $this->rate != null) {
            $this->amount = $this->quantity * $this->rate;
        }
        $this->balance = $this->quantity;
        if (!is_null($this->selectedVehicle)) {
            $this->selected_vehicle = Vehicle::find($this->selectedVehicle);
        }
      
        return view('livewire.allocations.index',[
            'balance' => $this->quantity,
            'allocations' => $this->allocations,
            'selected_vehicle' => $this->selected_vehicle,
        ]);
    }
}
