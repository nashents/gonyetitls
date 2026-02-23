<?php

namespace App\Http\Livewire\Losses;

use App\Models\Loss;
use App\Models\LossCategory;
use App\Models\LossGroup;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $loss_categories;
    public $selectedLossCategory;
    public $loss_groups;
    public $loss_group_id;
    protected $losses;
    public $loss_id;
    public $name;
    public $user_id;


    public function mount(){
        $this->loss_categories = LossCategory::orderBy('name','asc')->get();
        $this->loss_groups = collect();
    }


    public function updatedSelectedLossCategory($id){
        if (!is_null($id)) {
            $loss_category= LossCategory::find($id);
            $this->loss_groups = LossGroup::where('loss_category_id',$id)->orderBy('name','asc')->get();
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

     public function refresh($category){

        if($category == "loss_categories"){
            $this->loss_categories = LossCategory::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loss Categories Refreshed Successfully!!."
            ]);
        }
       
        elseif($category == "loss_groups"){
            $this->loss_groups = LossGroup::with('loss_category')->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loss Groups Refreshed Successfully!!."
            ]);
        }
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
      
        if ($this->loss_id) {

            $loss = Loss::find($this->loss_id);
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
        $baseQuery = Loss::query()
            ->with(['loss_category', 'loss_group']);

        if ($this->search) {
            $search = trim($this->search);

            $baseQuery->where(function ($q) use ($search) {
                // Search on losses.name
                $q->where('name', 'like', "%{$search}%")
                // Search on related loss_category.name
                ->orWhereHas('loss_category', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%");
                })
                // Search on related loss_group.name
                ->orWhereHas('loss_group', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $this->losses = $baseQuery->orderBy('name','asc')->paginate(10);

        return view('livewire.losses.index', [
            'losses' => $this->losses,
        ]);
    }
}
