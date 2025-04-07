<?php

namespace App\Http\Livewire\VehicleMakes;

use Livewire\Component;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $vehicle_makes;
    public $make;
    public $model;
    public $status;
    public $vehicle_make_id;
    public $vehicle_model_id;
    public $user_id;

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
        $this->vehicle_makes = VehicleMake::all();
    }
    private function resetInputFields(){
        $this->make = '';
        $this->vehicle_make_id = '';
        $this->model = '';
        $this->status = '';
    }
    public function updated($model){
        $this->validateOnly($model);
    }
    protected $rules = [
        'make' => 'unique:vehicle_makes,name,NULL,id,deleted_at,NULL|string|min:2',
        'model' => 'unique:vehicle_models,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        if(isset($this->vehicle_make_id)){
            foreach ($this->model as $key => $model) {
                $model = new VehicleModel;
                $model->user_id = Auth::user()->id;
                $model->vehicle_make_id =$this->vehicle_make_id;
                $model->name = $this->model[$key];
                $model->save();
              }
        }else{
            $vehicle_make = new VehicleMake;
            $vehicle_make->user_id = Auth::user()->id;
            $vehicle_make->name = $this->make;
            $vehicle_make->status = '1';
            $vehicle_make->save();

            foreach ($this->model as $key => $model) {
                $model = new VehicleModel;
                $model->user_id = Auth::user()->id;
                $model->vehicle_make_id = $vehicle_make->id;
                $model->name = $this->model[$key];
                $model->save();
              }

        }
          $this->dispatchBrowserEvent('hide-vehicle_makeModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vehicle Make & Model(s) Created Successfully!!"
            ]);
            return redirect(request()->header('Referer'));
    }

    public function edit($id){
    $vehicle_make = VehicleMake::find($id);
    $this->user_id = $vehicle_make->user_id;
    $this->make = $vehicle_make->name;
    $this->vehicle_make_id = $vehicle_make->id;
    $this->dispatchBrowserEvent('show-vehicle_makeEditModal');

    }

    public function editModel($id){
    $model = VehicleModel::find($id);
    $this->user_id = $model->user_id;
    $this->vehicle_make_id = $model->vehicle_make_id;
    $this->model = $model->name;
    $this->vehicle_model_id = $model->id;
    $this->dispatchBrowserEvent('show-vehicle_modelEditModal');
    }

    public function update()
    {
        if ($this->vehicle_make_id) {
            $vehicle_make = VehicleMake::find($this->vehicle_make_id);
            $vehicle_make->update([
                'user_id' => $this->user_id,
                'name' => $this->make,
            ]);
            $this->dispatchBrowserEvent('hide-vehicle_makeEditModal');
            // $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vehicle Make Updated Successfully!!"
            ]);

        }
    }
    public function updateModel()
    {
        if ($this->vehicle_model_id) {
            $vehicle_model = VehicleModel::find($this->vehicle_model_id);
            $vehicle_model->update([
                'user_id' => $this->user_id,
                'name' => $this->model,
            ]);
            $this->dispatchBrowserEvent('hide-vehicle_modelEditModal');
            // $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vehicle Model Updated Successfully!!"
            ]);

        }
    }


    public function render()
    {
        $this->vehicle_makes = VehicleMake::latest()->get();
        return view('livewire.vehicle-makes.index',[
            'vehicle_makes' => $this->vehicle_makes
        ]);
    }
}
