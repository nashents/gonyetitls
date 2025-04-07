<?php

namespace App\Http\Livewire\VehicleModels;

use Livewire\Component;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    public $vehicle_models;
    public $name;
    public $model_name;
    public $status;
    public $vehicle_model_id;
    public $user_id;



    public function mount(){
        $this->vehicle_models = VehicleModel::all();
    }

    public function updated($model){
        $this->validateOnly($model);
    }
    protected $rules = [
        'name' => 'required|unique:vehicle_models,name,NULL,id,deleted_at,NULL|string|min:2',
    ];


    public function edit($id){
    $vehicle_model = VehicleModel::find($id);
    $this->updateMode = true;
    $this->user_id = $vehicle_model->user_id;
    $this->name = $vehicle_model->name;
    $this->vehicle_model_id = $vehicle_model->id;
    $this->dispatchBrowserEvent('show-vehicleModelEditModal');

    }


    public function update()
    {
        if ($this->vehicle_model_id) {
            $vehicle_model = VehicleModel::find($this->vehicle_model_id);
            $vehicle_model->update([
                'vehicle_model_id' => $this->user_id,
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);

            Session::flash('success','Vehicle model updated successfully');

            $this->dispatchBrowserEvent('hide-vehicleModelEditModal',['message',"Vehicle model successfully updated"]);
            return redirect()->route('vehicle_models.index');

        }
    }


    public function render()
    {
        return view('livewire.vehicle-models.index');
    }
}
