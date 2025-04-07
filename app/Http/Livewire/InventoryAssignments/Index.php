<?php

namespace App\Http\Livewire\InventoryAssignments;

use App\Models\Horse;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Department;
use App\Models\InventoryDispatch;
use App\Models\InventoryAssignment;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{


    public $inventories;
    public $products;
    public $selectedProduct;
    public $horses;
    public $horse_id;
    public $employees;
    public $employee_id;
    public $vehicles;
    public $vehicle_id;
    public $trailers;
    public $trailer_id;
    public $specifications;
    public $status;
    public $qty;
    public $inventory_qty;
    public $date;
    public $inventory_assignments;
    public $inventory_assignment_id;

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
        $this->inventories = Inventory::where('qty','>',0)->latest()->get();
        $this->products = Product::orderBy('product_number','asc')->where('department','inventory')->get();
        $this->horses = Horse::latest()->get();
        $this->vehicles = Vehicle::latest()->get();
        $department = Department::where('name','like', '%'.'Workshop'.'%')->first();
        $this->employees =   $department->employees;
        $this->trailers = Trailer::latest()->get();
        $this->qty = 1;

        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->inventory_assignments = InventoryAssignment::latest()->get();
        } else {
            $this->inventory_assignments = InventoryAssignment::where('user_id',Auth::user()->id)->latest()->get();
        }

    }

    public function updatedselectedProduct($inventory){
        if (!is_null($inventory)) {
        $this->inventory_qty = Inventory::find($inventory)->qty;
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedProduct' => 'required',
        'horse_id' => 'required',
        'employee_id' => 'required',
        'vehicle_id' => 'required',
        'trailer_id' => 'required',
        'date' => 'required',
        'qty' => 'required',
        'specifications' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedProduct = '';
        $this->vehicle_id = '';
        $this->horse_id = '';
        $this->employee_id = '';
        $this->date = '';
        $this->qty = '';
        $this->specifications = '';
    }

    public function store(){
        // try{

        foreach ($this->selectedProduct as $key => $value) {

            $assignment = new InventoryAssignment;
            $assignment->user_id = Auth::user()->id;
            $assignment->inventory_id = $this->selectedProduct[$key];
            $assignment->vehicle_id = $this->vehicle_id;
            $assignment->horse_id = $this->horse_id;
            $assignment->trailer_id = $this->trailer_id;
            $assignment->qty = $this->qty[$key];
            $assignment->employee_id = $this->employee_id;
            $assignment->date = $this->date;
            $assignment->specifications = $this->specifications;
            $assignment->status = 1;
            $assignment->save();

            $inventory = Inventory::find($this->selectedProduct[$key]);
            $inventory->qty = $inventory->qty - $this->qty[$key];
            $inventory->update();

            for ($k = 0 ; $k < $this->qty[$key]; $k++){
                $dispatch = new InventoryDispatch;
                $dispatch->user_id = Auth::user()->id;
                $dispatch->inventory_assignment_id = $assignment->id;
                $dispatch->inventory_id = $this->selectedProduct[$key];
                $dispatch->employee_id = $this->employee_id;
                $dispatch->horse_id = $this->horse_id;
                $dispatch->vehicle_id = $this->vehicle_id;
                $dispatch->trailer_id = $this->trailer_id;
                $dispatch->issue_date = $this->date;
                $dispatch->part_number = $inventory->part_number;
                $dispatch->save();

                }

        }


        $this->dispatchBrowserEvent('hide-inventory_assignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inventory Assigned Successfully!!"
        ]);

        // return redirect()->route('cargos.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating cargo!!"
    //     ]);
    // }
    }

    public function edit($id){
        $assignment = InventoryAssignment::find($id);
        $this->inventory_assignment_id = $assignment->id;
        $this->selectedProduct = $assignment->inventory_id;
        $this->vehicle_id = $assignment->vehicle_id;
        $this->horse_id = $assignment->horse_id;
        $this->employee_id = $assignment->employee_id;
        $this->date = $assignment->date;
        $this->specifications = $assignment->specifications;
        $this->status = $assignment->status;
        $this->user_id = $assignment->user_id;
    }
    public function update(){
        try{
        $assignment =  InventoryAssignment::find($this->assignment_id);
        $assignment->user_id = Auth::user()->id;
        $assignment->inventory_id = $this->selectedProduct;
        $assignment->vehicle_id = $this->vehicle_id;
        $assignment->horse_id = $this->horse_id;
        $assignment->trailer_id = $this->trailer_id;
        $assignment->employee_id = $this->employee_id;
        $assignment->date = $this->date;
        $assignment->specifications = $this->specifications;
        $assignment->status = 1;
        $assignment->update();

        $this->dispatchBrowserEvent('hide-inventory_assignmentEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"inventory Assignment Updated Successfully!!"
        ]);

        // return redirect()->route('cargos.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating Inventory Assignments!!"
        ]);
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
            $this->inventory_assignments = InventoryAssignment::latest()->get();
        } else {
            $this->inventory_assignments = InventoryAssignment::where('user_id',Auth::user()->id)->latest()->get();
        }

        return view('livewire.inventory-assignments.index',[
            'inventory_assignments' => $this->inventory_assignments
        ]);
    }
}
