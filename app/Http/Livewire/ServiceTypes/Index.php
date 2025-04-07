<?php

namespace App\Http\Livewire\ServiceTypes;

use Livewire\Component;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $service_types;
    public $name;

    public $service_type_id;
    public $user_id;

    public function mount(){
        $this->service_types = ServiceType::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:service_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $service_type = new ServiceType;
        $service_type->user_id = Auth::user()->id;
        $service_type->name = $this->name;
        $service_type->save();
        $this->dispatchBrowserEvent('hide-service_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Service Type Created Successfully!!"
        ]);
        } catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-service_typeModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating service type!!"
            ]);
        }


    }

    public function edit($id){
    $service_type = ServiceType::find($id);
    $this->updateMode = true;
    $this->user_id = $service_type->user_id;
    $this->name = $service_type->name;
    $this->service_type_id = $service_type->id;
    $this->dispatchBrowserEvent('show-service_typeEditModal');

    }



    public function update()
    {
        if ($this->service_type_id) {
            try{
            $service_type = ServiceType::find($this->service_type_id);
            $service_type->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);
            $this->dispatchBrowserEvent('hide-service_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Service Type Updated Successfully!!"
            ]);
        } catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-service_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updatiing service type!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->service_types = ServiceType::latest()->get();
        return view('livewire.service-types.index',[
            'service_types' => $this->service_types
        ]);
    }
}
