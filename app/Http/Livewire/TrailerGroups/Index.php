<?php

namespace App\Http\Livewire\TrailerGroups;

use Livewire\Component;
use App\Models\TrailerGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $trailer_groups;
    public $name;
    public $updateMode = false;
    public $deleteMode = false;

    public $trailer_group_id;
    public $user_id;

    public function mount(){
        $this->trailer_groups = TrailerGroup::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:trailer_groups,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $trailer_group = new TrailerGroup;
        $trailer_group->user_id = Auth::user()->id;
        $trailer_group->name = $this->name;
        $trailer_group->save();
        $this->dispatchBrowserEvent('hide-trailer_groupModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trailer Group Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-trailer_groupModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating trailer group!!"
        ]);
    }

    }

    public function edit($id){
    $trailer_group = TrailerGroup::find($id);
    $this->updateMode = true;
    $this->user_id = $trailer_group->user_id;
    $this->name = $trailer_group->name;
    $this->trailer_group_id = $trailer_group->id;
    $this->dispatchBrowserEvent('show-trailer_groupEditModal');

    }



    public function update()
    {
        if ($this->trailer_group_id) {
            try{
            $trailer_group = TrailerGroup::find($this->trailer_group_id);
            $trailer_group->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);


            $this->dispatchBrowserEvent('hide-trailer_groupEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trailer Group Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-trailer_groupModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating trailer group!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->trailer_groups = TrailerGroup::latest()->get();
        return view('livewire.trailer-groups.index',[
            'trailer_groups' => $this->trailer_groups
        ]);
    }
}
