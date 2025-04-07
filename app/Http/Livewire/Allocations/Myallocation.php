<?php

namespace App\Http\Livewire\Allocations;

use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Container;
use App\Models\Allocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Myallocation extends Component
{

    public $allocations;
    public $allocation_number;
    public $allocation_type;
    public $allocation_id;
    public $vehicle_id;
    public $vehicles;
    public $employee_id;
    public $employees;
    public $container_id;
    public $containers;
    public $quantity;
    public $rate;
    public $amount;
    public $status;
    public $fuel_type;
    public $balance;
    public $expiry;

    public $user_id;

    public function mount(){
        $this->allocations = Allocation::where('employee_id', Auth::user()->employee->id)->latest()->get();
        $this->employees = Employee::all();
        $this->vehicles = Vehicle::all();
        $this->containers = Container::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'container_id' => 'required',
        'allocation_type' => 'required',
        'fuel_type' => 'required',
        'employee_id' => 'required',
        'vehicle_id' => 'required',
        'quantity' => 'required',
        'rate' => 'required',
        'expiry' => 'required',
    ];

    public function edit($id){
    $allocation = Allocation::find($id);
    $this->user_id = $allocation->user_id;
    $this->vehicle_id = $allocation->vehicle_id;
    $this->employee_id = $allocation->employee_id;
    $this->container_id = $allocation->container_id;
    $this->allocation_type = $allocation->allocation_type;
    $this->fuel_type = $allocation->fuel_type;
    $this->expiry = $allocation->expiry_date;
    $this->quantity = $allocation->quantity;
    $this->rate = $allocation->rate;
    $this->amount = $allocation->amount;
    $this->balance = $allocation->balance;
    $this->allocation_id = $allocation->id;
    $this->dispatchBrowserEvent('show-allocationEditModal');

    }


    public function update()
    {
        if ($this->allocation_id) {
            $allocation = Allocation::find($this->allocation_id);
            $allocation->update([
                'user_id' => Auth::user()->id,
                'vehicle_id' => $this->vehicle_id,
                'employee_id' => $this->employee_id,
                'container_id' => $this->container_id,
                'fuel_type' => $this->fuel_type,
                'allocation_type' => $this->allocation_type,
                'quantity' => $this->quantity,
                'rate' => $this->rate,
                'amount' => $this->amount,
                'balance' => $this->balance,
            ]);
            $this->updateMode = false;
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allocation Updated Successfully!!"
            ]);

            $this->dispatchBrowserEvent('hide-allocationEditModal',['message',"allocation successfully updated"]);
            return redirect()->route('allocations.index');

        }
    }

    public function render()
    {
        if ($this->quantity != null && $this->rate != null) {
            $this->amount = $this->quantity * $this->rate;
        }
        $this->balance = $this->quantity;
        return view('livewire.allocations.myallocation',[
            'balance' => $this->quantity,
        ]);
    }
}
