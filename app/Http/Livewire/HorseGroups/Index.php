<?php

namespace App\Http\Livewire\HorseGroups;

use Livewire\Component;
use App\Models\HorseGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $horse_groups;
    public $name;
    public $updateMode = false;
    public $deleteMode = false;

    public $horse_group_id;
    public $user_id;

    public function mount(){
        $this->horse_groups = HorseGroup::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:horse_groups,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $horse_group = new HorseGroup;
        $horse_group->user_id = Auth::user()->id;
        $horse_group->name = $this->name;
        $horse_group->save();
        $this->dispatchBrowserEvent('hide-horse_groupModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Horse Group Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-horse_groupModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating horse group!!"
        ]);
    }

    }

    public function edit($id){
    $horse_group = HorseGroup::find($id);
    $this->updateMode = true;
    $this->user_id = $horse_group->user_id;
    $this->name = $horse_group->name;
    $this->horse_group_id = $horse_group->id;
    $this->dispatchBrowserEvent('show-horse_groupEditModal');

    }



    public function update()
    {
        if ($this->horse_group_id) {
            try{
            $horse_group = HorseGroup::find($this->horse_group_id);
            $horse_group->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);


            $this->dispatchBrowserEvent('hide-horse_groupEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Horse Group Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-horse_groupModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating Horse group!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->horse_groups = HorseGroup::latest()->get();
        return view('livewire.horse-groups.index',[
            'horse_groups' => $this->horse_groups
        ]);
    }
}
