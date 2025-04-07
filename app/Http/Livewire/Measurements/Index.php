<?php

namespace App\Http\Livewire\Measurements;

use Livewire\Component;
use App\Models\Measurement;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $name;
    public $cargo_type;
    public $measurements;
    public $measurement_id;

    public function mount(){
        $this->measurements = Measurement::orderBy('name','asc')->get();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
       
        'cargo_type' => 'required',
        'name' => 'required|unique:measurements,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->name = '';
        $this->cargo_type = '';
    }

    public function store(){
        $measurement = new Measurement;
        $measurement->user_id = Auth::user()->id;
        $measurement->name = $this->name;
        $measurement->cargo_type = $this->cargo_type;
        $measurement->save();
        $this->dispatchBrowserEvent('hide-measurementModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Measurement Created Successfully!!"
        ]);
    }
    public function edit($id){
        $this->measurement = Measurement::find($id);
        $this->measurement_id = $id;
        $this->name = $this->measurement->name;
        $this->cargo_type = $this->measurement->cargo_type;
        $this->dispatchBrowserEvent('show-measurementEditModal');
    }
    public function update(){
        $measurement = Measurement::find($this->measurement_id);
        $measurement->user_id = Auth::user()->id;
        $measurement->name = $this->name;
        $measurement->cargo_type = $this->cargo_type;
        $measurement->update();
        $this->dispatchBrowserEvent('hide-measurementEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Measurement Updated Successfully!!"
        ]);
    }
    public function render()
    {
        $this->measurements = Measurement::orderBy('name','asc')->get();
        return view('livewire.measurements.index',[
            'measurements' =>  $this->measurements,
        ]);
    }
}
