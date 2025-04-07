<?php

namespace App\Http\Livewire\InventoryRequisitions;

use Livewire\Component;
use App\Models\InventoryRequisition;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $inventories;
    public $selectedInventory;
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
    public $inventory_requisitions;
    public $inventory_requisition_id;

    public function mount(){
        $this->inventories = Inventory::where('qty','>',0)->latest()->get();
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
            $this->inventory_requisitions = Inventoryrequisition::latest()->get();
        } else {
            $this->inventory_requisitions = Inventoryrequisition::where('user_id',Auth::user()->id)->latest()->get();
        }

    }

    public function updatedSelectedInventory($inventory){
        if (!is_null($inventory)) {
        $this->inventory_qty = Inventory::find($inventory)->qty;
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedInventory' => 'required',
        'horse_id' => 'required',
        'employee_id' => 'required',
        'vehicle_id' => 'required',
        'trailer_id' => 'required',
        'date' => 'required',
        'qty' => 'required',
        'specifications' => 'required',
    ];

    private function resetInputFields(){
        $this->inventory_id = '';
        $this->vehicle_id = '';
        $this->horse_id = '';
        $this->employee_id = '';
        $this->date = '';
        $this->qty = '';
        $this->specifications = '';
    }

    public function store(){
        try{
        $requisition = new Inventoryrequisition;
        $requisition->user_id = Auth::user()->id;
        $requisition->inventory_id = $this->selectedInventory;
        $requisition->vehicle_id = $this->vehicle_id;
        $requisition->horse_id = $this->horse_id;
        $requisition->trailer_id = $this->trailer_id;
        $requisition->qty = $this->qty;
        $requisition->employee_id = $this->employee_id;
        $requisition->date = $this->date;
        $requisition->specifications = $this->specifications;
        $requisition->status = 1;
        $requisition->save();

        $inventory = Inventory::find($selectedInventory);
        $inventory->qty = $this->qty;
        $inventory->update();



        $this->dispatchBrowserEvent('hide-inventory_requisitionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inventory Assigned Successfully!!"
        ]);

        // return redirect()->route('cargos.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating cargo!!"
        ]);
    }
    }

    public function edit($id){
        $requisition = InventoryRequisition::find($id);
        $this->inventory_requisition_id = $requisition->id;
        $this->selectedInventory = $requisition->inventory_id;
        $this->vehicle_id = $requisition->vehicle_id;
        $this->horse_id = $requisition->horse_id;
        $this->employee_id = $requisition->employee_id;
        $this->date = $requisition->date;
        $this->specifications = $requisition->specifications;
        $this->status = $requisition->status;
        $this->user_id = $requisition->user_id;
    }
    public function update(){
        try{
        $requisition =  InventoryRequisition::find($this->requisition_id);
        $requisition->user_id = Auth::user()->id;
        $requisition->inventory_id = $this->selectedInventory;
        $requisition->vehicle_id = $this->vehicle_id;
        $requisition->horse_id = $this->horse_id;
        $requisition->trailer_id = $this->trailer_id;
        $requisition->employee_id = $this->employee_id;
        $requisition->date = $this->date;
        $requisition->specifications = $this->specifications;
        $requisition->status = 1;
        $requisition->update();

        $this->dispatchBrowserEvent('hide-inventory_requisitionEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"inventory requisition Updated Successfully!!"
        ]);

        // return redirect()->route('cargos.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating Inventory requisitions!!"
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
            $this->inventory_requisitions = InventoryRequisition::latest()->get();
        } else {
            $this->inventory_requisitions = InventoryRequisition::where('user_id',Auth::user()->id)->latest()->get();
        }

        return view('livewire.inventory-requisitions.index',[
            'inventory_requisitions' => $this->inventory_requisitions
        ]);
    }
}
