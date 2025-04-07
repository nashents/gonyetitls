<?php

namespace App\Http\Livewire\Losses;

use App\Models\Loss;
use Livewire\Component;
use App\Models\LossGroup;
use App\Models\LossCategory;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $loss_categories;
    public $selectedLossCategory;
    public $loss_groups;
    public $loss_group_id;
    public $losses;
    public $loss_id;
    public $name;
    public $user_id;


    public function mount(){
        $this->losses = Loss::latest()->get();
        $this->loss_categories = LossCategory::latest()->get();
        $this->loss_groups = collect();
    }


    public function updatedSelectedLossCategory($id){
        if (!is_null($id)) {
            $loss_category= LossCategory::find($id);
            $this->loss_groups = $loss_category->loss_groups;
        }
    }
   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:losses,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->name = '';
    }

    public function store(){
        $loss = new Loss;
        $loss->user_id = Auth::user()->id;
        $loss->name = $this->name;
        $loss->loss_category_id = $this->selectedLossCategory;
        $loss->loss_group_id = $this->loss_group_id;
        $loss->save();
      
        $this->dispatchBrowserEvent('hide-lossModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loss Cause Created Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

    public function edit($id){
    $loss = Loss::find($id);
    $this->loss_groups = LossGroup::orderBy('name','asc')->get();
    $this->user_id = $loss->user_id;
    $this->name = $loss->name;
    $this->selectedLossCategory = $loss->loss_category_id;
    $this->loss_group_id = $loss->loss_group_id;
    $this->loss_id = $loss->id;
    $this->dispatchBrowserEvent('show-lossEditModal');

    }

    public function update()
    {
        if ($this->loss_group_id) {

            $loss = Loss::find($this->loss_group_id);
            $loss->name = $this->name;
            $loss->loss_category_id = $this->selectedLossCategory;
            $loss->loss_group_id = $this->loss_group_id;
            $loss->update();

        $this->dispatchBrowserEvent('hide-lossEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loss Cause Updated Successfully!!"
        ]);

        }
    }
    public function render()
    {
        $this->losses = Loss::latest()->get();
        $this->loss_categories = LossCategory::latest()->get();
        return view('livewire.losses.index',[
            'losses' => $this->losses,
            'loss_categories' => $this->loss_categories
        ]);
    }
}
