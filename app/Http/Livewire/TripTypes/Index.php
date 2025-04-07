<?php

namespace App\Http\Livewire\TripTypes;

use Livewire\Component;
use App\Models\TripType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $trip_types;
    public $name;

    public $trip_type_id;
    public $user_id;

    public function mount(){
        $this->trip_types = TripType::latest()->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){

        $this->name = '';
    }
    protected $rules = [
        'name' => 'required|unique:trip_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
            $trip_type = new TripType;
            $trip_type->user_id = Auth::user()->id;
            $trip_type->name = $this->name;
            $trip_type->save();
            $this->dispatchBrowserEvent('hide-trip_typeModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Type Created Successfully!!"
            ]);
        } catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-trip_typeModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating trip type!!"
            ]);
          }


    }

    public function edit($id){
    $trip_type = TripType::find($id);
    $this->updateMode = true;
    $this->user_id = $trip_type->user_id;
    $this->name = $trip_type->name;
    $this->trip_type_id = $trip_type->id;
    $this->dispatchBrowserEvent('show-trip_typeEditModal');

    }



    public function update()
    {
        if ($this->trip_type_id) {
            try{
            $trip_type = TripType::find($this->trip_type_id);
            $trip_type->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);
            $this->dispatchBrowserEvent('hide-trip_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Type Updated Successfully!!"
            ]);
            } catch(\Exception $e){
                $this->dispatchBrowserEvent('hide-trip_typeEditModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something goes wrong while updating trip type!!"
                ]);
            }
        }
    }


    public function render()
    {
        $this->trip_types = TripType::latest()->get();
        return view('livewire.trip-types.index',[
            'trip_types' => $this->trip_types
        ]);
    }
}
