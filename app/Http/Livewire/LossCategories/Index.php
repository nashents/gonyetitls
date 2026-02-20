<?php

namespace App\Http\Livewire\LossCategories;

use App\Models\LossCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
   
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    protected $loss_categories;
    public $loss_category_id;
    public $name;
    public $user_id;


    public function mount(){
       
      
    }

   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:loss_categories,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->name = '';
    }

    public function store(){
        $loss_category = new LossCategory;
        $loss_category->user_id = Auth::user()->id;
        $loss_category->name = $this->name;
        $loss_category->save();
      
        $this->dispatchBrowserEvent('hide-loss_categoryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loss category Added Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

    public function edit($id){
    $loss_category = LossCategory::find($id);
    $this->user_id = $loss_category->user_id;
    $this->name = $loss_category->name;
    $this->loss_category_id = $loss_category->id;
    $this->dispatchBrowserEvent('show-loss_categoryEditModal');

    }

    public function update()
    {
        if ($this->loss_category_id) {

            $loss_category = LossCategory::find($this->loss_category_id);
            $loss_category->name = $this->name;
            $loss_category->update();

        $this->dispatchBrowserEvent('hide-loss_categoryEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loss Cause Category Updated Successfully!!"
        ]);

        }
    }
    public function render()
    {
        $term = trim((string) $this->search);

        $query = LossCategory::query()
            ->when(filled($term), function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            })->orderBy('name', 'asc');

        return view('livewire.loss-categories.index',[
            'loss_categories' => $query->paginate(10)
        ]);
    }
}
