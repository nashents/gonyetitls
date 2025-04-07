<?php

namespace App\Http\Livewire\HorseModels;

use Livewire\Component;
use App\Models\HorseModel;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    public $horse_models;
    public $name;
    public $model_name;
    public $status;
    public $horse_model_id;
    public $user_id;



    public function mount(){
        $this->horse_models = HorseModel::all();
    }

    public function updated($model){
        $this->validateOnly($model);
    }
    protected $rules = [
        'name' => 'required|unique:horse_models,name,NULL,id,deleted_at,NULL|string|min:2',
    ];


    public function edit($id){
    $horse_model = HorseModel::find($id);
    $this->updateMode = true;
    $this->user_id = $horse_model->user_id;
    $this->name = $horse_model->name;
    $this->horse_model_id = $horse_model->id;
    $this->dispatchBrowserEvent('show-horse_modelEditModal');

    }


    public function update()
    {
        if ($this->horse_model_id) {
            $horse_model = HorseModel::find($this->horse_model_id);
            $horse_model->update([
                'horse_model_id' => $this->user_id,
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);

            Session::flash('success','Horse model updated successfully');

            $this->dispatchBrowserEvent('hide-horse_modelEditModal',['message',"Horse model successfully updated"]);
            return redirect()->route('horse_models.index');

        }
    }


    public function render()
    {
        return view('livewire.horse-models.index');
    }
}
