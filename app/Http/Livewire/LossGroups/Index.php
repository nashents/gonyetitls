<?php

namespace App\Http\Livewire\LossGroups;

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
    protected $loss_groups;
    public $loss_group_id;
    public $loss_categories;
    public $loss_category_id;
    public $name;
    public $user_id;


    public function mount(){
        $this->loss_categories = LossCategory::orderBy('name','asc')->get();
    }

   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:loss_groups,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->name = '';
        $this->loss_category_id = '';
    }

    public function refresh($category){

        if($category == "loss_categories"){
            $this->loss_categories = LossCategory::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loss Categories Refreshed Successfully!!."
            ]);
        }
       
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
            'message'=>"Cause Group Added Successfully!!"
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
            if ($loss_group->is_locked && $this->name !== $loss_group->name) {
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"This loss group is a core system group - its name cannot be changed."
                ]);
                return;
            }
            $loss_group->name = $this->name;
            $loss_group->loss_category_id = $this->loss_category_id;
            $loss_group->update();

        $this->dispatchBrowserEvent('hide-loss_groupEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Cause Group Updated Successfully!!"
        ]);

        }
    }
    public function render()
    {
        $baseQuery = LossGroup::query()
            ->with(['loss_category']);

        if ($this->search) {
            $search = trim($this->search);

            $baseQuery->where(function ($q) use ($search) {
                // Search on loss_groups.name
                $q->where('name', 'like', "%{$search}%")
                // Search on related loss_category.name
                ->orWhereHas('loss_category', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%");
                });
                
            });
        }

        $this->loss_groups = $baseQuery->paginate(10);

        return view('livewire.loss-groups.index', [
            'loss_groups' => $this->loss_groups,
        ]);
    }
}
