<?php

namespace App\Http\Livewire\InspectionGroups;

use Livewire\Component;
use App\Models\InspectionGroup;
use App\Models\InspectionType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $inspection_groups;
    public $name;   
    public $inspection_group_id;
    public $user_id;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount(){
        $this->inspection_groups = InspectionGroup::all();
    }
    private function resetInputFields(){
        $this->name = '';
       
    }
    public function updated($model){
        $this->validateOnly($model);
    }
    protected $rules = [
        'name' => 'unique:inspection_groups,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){

            $inspection_group = new InspectionGroup;
            $inspection_group->user_id = Auth::user()->id;
            $inspection_group->name = $this->name;
            $inspection_group->save();
      
          $this->dispatchBrowserEvent('hide-inspection_groupModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Inpection Group Created Successfully!!"
            ]);
    }

    public function edit($id){
    $inspection_group = InspectionGroup::find($id);
    $this->user_id = $inspection_group->user_id;
    $this->name = $inspection_group->name;
    $this->inspection_group_id = $inspection_group->id;
    $this->dispatchBrowserEvent('show-inspection_groupEditModal');

    }



    public function update()
    {

        if ($this->inspection_group_id) {
            $inspection_group = InspectionGroup::find($this->inspection_group_id);
            $inspection_group->user_id = $this->user_id;
            $inspection_group->name = $this->name;
            $inspection_group->update();
            $this->dispatchBrowserEvent('hide-inspection_groupEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Inspection Group Updated Successfully!!"
            ]);
        }
    }

    public function render()
    {
        $this->inspection_groups = InspectionGroup::latest()->get();
        return view('livewire.inspection-groups.index',[
            'inspection_groups' => $this->inspection_groups
        ]);
    }
}
