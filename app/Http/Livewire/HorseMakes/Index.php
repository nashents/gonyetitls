<?php

namespace App\Http\Livewire\HorseMakes;

use Livewire\Component;
use App\Models\HorseMake;
use App\Models\HorseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $horse_makes;
    public $make;
    public $model;
    public $status;
    public $horse_make_id;
    public $horse_model_id;
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
        $this->horse_makes = HorseMake::all();
    }
    private function resetInputFields(){
        $this->make_id = '';
        $this->make = '';
        $this->model = '';
        $this->status = '';
    }
    public function updated($model){
        $this->validateOnly($model);
    }
    protected $rules = [
        'make' => 'unique:horse_makes,name,NULL,id,deleted_at,NULL|string|min:2',
        'model' => 'unique:horse_models,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){

        if(isset($this->horse_make_id) && $this->horse_make_id != "" ){
            foreach ($this->model as $key => $model) {
                $model = new HorseModel;
                $model->user_id = Auth::user()->id;
                $model->horse_make_id =$this->horse_make_id;
                $model->name = $this->model[$key];
                $model->save();
              }
        }else{
            $horse_make = new HorseMake;
            $horse_make->user_id = Auth::user()->id;
            $horse_make->name = $this->make;
            $horse_make->status = '1';
            $horse_make->save();

            foreach ($this->model as $key => $model) {
                $model = new HorseModel;
                $model->user_id = Auth::user()->id;
                $model->horse_make_id = $horse_make->id;
                $model->name = $this->model[$key];
                $model->save();
              }

        }


          $this->dispatchBrowserEvent('hide-horse_makeModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Horse Make & Model(s) Created Successfully!!"
            ]);
            return redirect(request()->header('Referer'));
    }

    public function edit($id){
    $horse_make = HorseMake::find($id);
    $this->user_id = $horse_make->user_id;
    $this->make = $horse_make->name;
    $this->horse_make_id = $horse_make->id;
    $this->dispatchBrowserEvent('show-horse_makeEditModal');

    }

    public function editModel($id){
    $model = HorseModel::find($id);
    $this->user_id = $model->user_id;
    $this->horse_make_id = $model->horse_make_id;
    $this->model = $model->name;
    $this->horse_model_id = $model->id;
    $this->dispatchBrowserEvent('show-horse_modelEditModal');
    }

    public function update()
    {
        if ($this->horse_make_id) {
            $horse_make = HorseMake::find($this->horse_make_id);
            $horse_make->update([
                'user_id' => $this->user_id,
                'name' => $this->make,
            ]);
            $this->dispatchBrowserEvent('hide-horse_makeEditModal');
            // $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Horse Make Updated Successfully!!"
            ]);

        }
    }
    public function updateModel()
    {
        if ($this->horse_model_id) {
            $horse_model = HorseModel::find($this->horse_model_id);
            $horse_model->update([
                'user_id' => $this->user_id,
                'name' => $this->model,
            ]);
            $this->dispatchBrowserEvent('hide-horse_modelEditModal');
            // $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Horse Model Updated Successfully!!"
            ]);

        }
    }


    public function render()
    {
        $this->horse_makes = HorseMake::latest()->get();
        return view('livewire.horse-makes.index',[
            'horse_makes' => $this->horse_makes
        ]);
    }
}
