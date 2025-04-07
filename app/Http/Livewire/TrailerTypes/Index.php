<?php

namespace App\Http\Livewire\TrailerTypes;

use Livewire\Component;
use App\Models\TrailerType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $trailer_types;
    public $name;
    public $updateMode = false;
    public $deleteMode = false;

    public $trailer_type_id;
    public $user_id;

    public function mount(){
        $this->trailer_types = TrailerType::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:trailer_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $trailer_type = new TrailerType;
        $trailer_type->user_id = Auth::user()->id;
        $trailer_type->name = $this->name;
        $trailer_type->save();
        $this->dispatchBrowserEvent('hide-trailer_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trailer Type Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-trailer_typeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating trailer type!!"
        ]);
    }

    }

    public function edit($id){
    $trailer_type = TrailerType::find($id);
    $this->updateMode = true;
    $this->user_id = $trailer_type->user_id;
    $this->name = $trailer_type->name;
    $this->trailer_type_id = $trailer_type->id;
    $this->dispatchBrowserEvent('show-trailer_typeEditModal');

    }


    public function update()
    {
        if ($this->trailer_type_id) {
            try{
            $trailer_type = TrailerType::find($this->trailer_type_id);
            $trailer_type->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);

            $this->dispatchBrowserEvent('hide-trailer_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trailer Type Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-trailer_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating trailer type!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->trailer_types = TrailerType::latest()->get();
        return view('livewire.trailer-types.index',[
            'trailer_types' => $this->trailer_types
        ]);
    }
}
