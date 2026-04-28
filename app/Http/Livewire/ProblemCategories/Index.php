<?php

namespace App\Http\Livewire\ProblemCategories;

use App\Models\ProblemCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $problem_categories;
    public $name;
    public $description;
    public $problem_category_id;
    public $user_id;

    public function mount(){
        
    }
   
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->description = "";
    }
    protected $rules = [
        'name' => 'required|unique:problem_categories,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        
        $this->validate();
        
        $problem_category = new ProblemCategory;
        $problem_category->user_id = Auth::user()->id;
        $problem_category->name = $this->name;
        $problem_category->description = $this->description;
        $problem_category->save();

        $this->dispatchBrowserEvent('hide-saveModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Problem Category Created Successfully!!"
        ]);

    }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating problem category!!"
        ]);
         }
    }

    public function edit($id){

    $problem_category = ProblemCategory::find($id);
  
    $this->user_id = $problem_category->user_id;
    $this->name = $problem_category->name;
    $this->description = $problem_category->description;
    $this->problem_category_id = $problem_category->id;
    $this->dispatchBrowserEvent('show-updateModal');

    }


    public function update()
    {
        if ($this->problem_category_id) {
            try{

            $problem_category = ProblemCategory::find($this->problem_category_id);
            $problem_category->name = $this->name;
            $problem_category->description = $this->description;
            $problem_category->update();

            $this->dispatchBrowserEvent('hide-updateModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Problem Category Updated Successfully!!"
            ]);
            // return redirect()->route('problem_categorys.index');
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating problem category!!"
        ]);
    }

        }
    }

    public function delete($id){
        $this->problem_category_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy(){
        $problem_category = ProblemCategory::find($this->problem_category_id);
        $problem_category->delete();
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Problem Category Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $search = trim($this->search);

        $query = ProblemCategory::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
        ->orderBy('name', 'asc')
        ->paginate(10);

        return view('livewire.problem-categories.index',[
            'problem_categories' => $query
        ]);
    }
}
