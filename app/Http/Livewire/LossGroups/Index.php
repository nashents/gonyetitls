<?php

namespace App\Http\Livewire\LossGroups;

use Livewire\Component;
use App\Models\LossGroup;
use App\Models\LossCategory;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $loss_groups;
    public $loss_group_id;
    public $loss_categories;
    public $loss_category_id;
    public $name;
    public $user_id;


    public function mount(){
        $this->loss_groups = LossGroup::latest()->get();
        $this->loss_categories = LossCategory::latest()->get();
    }

   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:loss_groups,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->name = '';
    }

    public function store(){
        $loss_group = new LossGroup;
        $loss_group->user_id = Auth::user()->id;
        $loss_group->name = $this->name;
        $loss_group->loss_category_id = $this->loss_category_id;
        $loss_group->save();
      
        $this->dispatchBrowserEvent('hide-loss_groupModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loss Group Added Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

    public function edit($id){
    $loss_group = LossGroup::find($id);
    $this->user_id = $loss_group->user_id;
    $this->name = $loss_group->name;
    $this->loss_category_id = $loss_group->loss_category_id;
    $this->loss_group_id = $loss_group->id;
    $this->dispatchBrowserEvent('show-loss_groupEditModal');

    }

    public function update()
    {
        if ($this->loss_group_id) {

            $loss_group = LossGroup::find($this->loss_group_id);
            $loss_group->name = $this->name;
            $loss_group->loss_category_id = $this->loss_category_id;
            $loss_group->update();

        $this->dispatchBrowserEvent('hide-loss_groupEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loss Cause Category Updated Successfully!!"
        ]);

        }
    }
    public function render()
    {
        $this->loss_groups = LossGroup::latest()->get();
        return view('livewire.loss-groups.index',[
            'loss_groups' => $this->loss_groups
        ]);
    }
}
