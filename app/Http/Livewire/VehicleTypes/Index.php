<?php

namespace App\Http\Livewire\VehicleTypes;

use Livewire\Component;
use App\Models\VehicleType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $vehicle_types;
    public $name;
    public $updateMode = false;
    public $deleteMode = false;

    public $vehicle_type_id;
    public $user_id;

    public function mount(){
        $this->vehicle_types = VehicleType::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:vehicle_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $vehicle_type = new VehicleType;
        $vehicle_type->user_id = Auth::user()->id;
        $vehicle_type->name = $this->name;
        $vehicle_type->save();
        $this->dispatchBrowserEvent('hide-vehicle_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vehicle Type Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-vehicle_typeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating vehicle type!!"
        ]);
    }

    }

    public function edit($id){
    $vehicle_type = VehicleType::find($id);
    $this->updateMode = true;
    $this->user_id = $vehicle_type->user_id;
    $this->name = $vehicle_type->name;
    $this->vehicle_type_id = $vehicle_type->id;
    $this->dispatchBrowserEvent('show-vehicle_typeEditModal');

    }


    public function update()
    {
        if ($this->vehicle_type_id) {
            try{
            $vehicle_type = VehicleType::find($this->vehicle_type_id);
            $vehicle_type->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);

            $this->dispatchBrowserEvent('hide-vehicle_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vehicle Type Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-vehicle_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating vehicle type!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->vehicle_types = VehicleType::latest()->get();
        return view('livewire.vehicle-types.index',[
            'vehicle_types' => $this->vehicle_types
        ]);
    }
}
