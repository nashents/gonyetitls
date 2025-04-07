<?php

namespace App\Http\Livewire\Visitors;

use App\Models\Group;
use App\Models\Visitor;
use Livewire\Component;

class Index extends Component
{

    public $visitors;
    public $visitor_id;
    public $groups;
    public $group_id;
    public $name;
    public $surname;
    public $idnumber;
    public $phonenumber;
    public $gender;

    public function mount(){
        $this->groups = Group::with('visitors')->latest()->get();
        $this->visitors = Visitor::latest()->get();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required',
    ];

    private function resetInputFields(){
        $this->name = '';
        $this->surname = '';
        $this->group_id = '';
        $this->idnumber = '';
        $this->phonenumber = '';
        $this->gender = '';
    }

    public function store(){

        try{

        $visitor = new Visitor;
        $visitor->group_id = $this->group_id;
        $visitor->name = $this->name;
        $visitor->surname = $this->surname;
        $visitor->idnumber = $this->idnumber;
        $visitor->gender = $this->gender;
        $visitor->phonenumber = $this->phonenumber;
        $visitor->save();

        $this->dispatchBrowserEvent('hide-visitorModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Visitor Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating visitor!!"
        ]);
    }
    }

    public function edit($id){
        $this->visitor_id = $id;
        $visitor = Visitor::find($id);
        $this->group_id = $visitor->group_id;
        $this->name = $visitor->name;
        $this->surname = $visitor->surname;
        $this->gender = $visitor->gender;
        $this->idnumber = $visitor->idnumber;
        $this->phonenumber = $visitor->phonenumber;
        $this->dispatchBrowserEvent('show-visitorEditModal');
    }

    public function update(){

        try{

        $visitor = Visitor::find($this->visitor_id);
        $visitor->group_id = $this->group_id;
        $visitor->name = $this->name;
        $visitor->surname = $this->surname;
        $visitor->gender = $this->gender;
        $visitor->idnumber = $this->idnumber;
        $visitor->phonenumber = $this->phonenumber;
        $visitor->update();

        $this->dispatchBrowserEvent('hide-visitorEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Visitor Updated Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating Visitor!!"
        ]);
    }
    }


    public function render()
    {
        $this->visitors = Visitor::latest()->get();
        return view('livewire.visitors.index',[
            'visitors' => $this->visitors
        ]);
    }
}
