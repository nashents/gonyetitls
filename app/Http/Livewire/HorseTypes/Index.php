<?php

namespace App\Http\Livewire\HorseTypes;

use Livewire\Component;
use App\Models\HorseType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $horse_types;
    public $name;
    public $updateMode = false;
    public $deleteMode = false;

    public $horse_type_id;
    public $user_id;

    public function mount(){
        $this->horse_types = HorseType::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:horse_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $horse_type = new HorseType;
        $horse_type->user_id = Auth::user()->id;
        $horse_type->name = $this->name;
        $horse_type->save();
        $this->dispatchBrowserEvent('hide-horse_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Horse Type Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-horse_typeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating horse type!!"
        ]);
    }

    }

    public function edit($id){
    $horse_type = HorseType::find($id);
    $this->updateMode = true;
    $this->user_id = $horse_type->user_id;
    $this->name = $horse_type->name;
    $this->horse_type_id = $horse_type->id;
    $this->dispatchBrowserEvent('show-horse_typeEditModal');

    }


    public function update()
    {
        if ($this->horse_type_id) {
            try{
            $horse_type = HorseType::find($this->horse_type_id);
            $horse_type->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);

            $this->dispatchBrowserEvent('hide-horse_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Horse Type Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-horse_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating horse type!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->horse_types = HorseType::latest()->get();
        return view('livewire.horse-types.index',[
            'horse_types' => $this->horse_types
        ]);
    }
}
