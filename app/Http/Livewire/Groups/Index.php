<?php

namespace App\Http\Livewire\Groups;

use App\Models\Group;
use Livewire\Component;

class Index extends Component
{

    public $groups;
    public $group_id;
    public $name;

    public function mount(){
        $this->groups = Group::with('visitors')->latest()->get();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required',
    ];

    private function resetInputFields(){
        $this->name = '';
    }

    public function store(){

        try{

        $group = new Group;
        $group->name = $this->name;
        $group->save();

        $this->dispatchBrowserEvent('hide-groupModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Group Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating group!!"
        ]);
    }
    }

    public function edit($id){
        $this->group_id = $id;
        $group = Group::find($id);
        $this->name = $group->name;
        $this->dispatchBrowserEvent('show-groupEditModal');
    }

    public function update(){

        try{

        $group = Group::find($this->group_id);
        $group->name = $this->name;
        $group->update();

        $this->dispatchBrowserEvent('hide-groupEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Group Updated Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating group!!"
        ]);
    }
    }


    public function render()
    {
        $this->groups = Group::with('visitors')->latest()->get();
        return view('livewire.groups.index',[
            'groups' => $this->groups
        ]);
    }
}
