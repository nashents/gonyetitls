<?php

namespace App\Http\Livewire\VehicleGroups;

use Livewire\Component;
use App\Models\VehicleGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $vehicle_groups;
    public $name;
    public $updateMode = false;
    public $deleteMode = false;

    public $vehicle_group_id;
    public $user_id;

    public function mount(){
        $this->vehicle_groups = VehicleGroup::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:vehicle_groups,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $vehicle_group = new VehicleGroup;
        $vehicle_group->user_id = Auth::user()->id;
        $vehicle_group->name = $this->name;
        $vehicle_group->save();
        $this->dispatchBrowserEvent('hide-vehicle_groupModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vehicle Group Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-vehicle_groupModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating vehicle group!!"
        ]);
    }

    }

    public function edit($id){
    $vehicle_group = VehicleGroup::find($id);
    $this->updateMode = true;
    $this->user_id = $vehicle_group->user_id;
    $this->name = $vehicle_group->name;
    $this->vehicle_group_id = $vehicle_group->id;
    $this->dispatchBrowserEvent('show-vehicle_groupEditModal');

    }



    public function update()
    {
        if ($this->vehicle_group_id) {
            try{
            $vehicle_group = VehicleGroup::find($this->vehicle_group_id);
            $vehicle_group->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);


            $this->dispatchBrowserEvent('hide-vehicle_groupEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vehicle Group Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-vehicle_groupModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating vehicle group!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->vehicle_groups = VehicleGroup::latest()->get();
        return view('livewire.vehicle-groups.index',[
            'vehicle_groups' => $this->vehicle_groups
        ]);
    }
}
