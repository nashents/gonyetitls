<?php

namespace App\Http\Livewire\ChecklistCategories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ChecklistCategory;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $checklist_categories;
    public $checklist_category_id;
    public $name;
    public $user_id;


    public function mount(){
        
    }
    

    

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:checklist_categories,name,NULL,id,deleted_at,NULL|string',
    ];

    private function resetInputFields(){
        $this->name = '';
    }

    public function store(){
        // try{
            $this->validate(
                [
                    'name' => 'required|unique:checklist_categories,name,NULL,id,deleted_at,NULL|string|min:2'
                ]
            );
        $checklist_category = new Checklistcategory;
        $checklist_category->user_id = Auth::user()->id;
        $checklist_category->name = $this->name;
        $checklist_category->save();

        $this->dispatchBrowserEvent('hide-checklist_categoryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Checklist Created Successfully!!"
        ]);


    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating checklist !!"
    //     ]);
    // }
    }

    public function edit($id){
    $checklist_category = Checklistcategory::find($id);
    $this->user_id = $checklist_category->user_id;
    $this->name = $checklist_category->name;
    $this->checklist_category_id = $checklist_category->id;
    $this->dispatchBrowserEvent('show-checklist_categoryEditModal');

    }


    public function update()
    {
        if ($this->checklist_category_id) {
            try{
           $checklist_category = ChecklistCategory::find($this->checklist_category_id);
           if ($checklist_category->is_locked && $this->name !== $checklist_category->name) {
               $this->dispatchBrowserEvent('alert',[
                   'type'=>'error',
                   'message'=>"This checklist category is a core system category - its name cannot be changed."
               ]);
               return;
           }
           $checklist_category->name = $this->name;
           $checklist_category->update();

            $this->dispatchBrowserEvent('hide-checklist_categoryEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Updated Successfully!!"
            ]);

            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-checklist_categoryEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating checklist !!"
            ]);
          }
        }
    }


    public function render()
    {

       $base = ChecklistCategory::query()
            ->with([ 'category_checklists.checklist_item']);
        //    ->whereNotIn('name', ['Tyre Inspection', 'Stock on board']);

        $checklist_categories = $base
            ->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                    ->orWhereHas('category_checklists', function ($sub) use ($term) {
                        $sub->whereHas('checklist_item', function ($qsub) use ($term) {
                            $qsub->where('name', 'like', $term);
                        });
                    });
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('livewire.checklist-categories.index', [
            'checklist_categories' => $checklist_categories,
        ]);
    }
}
