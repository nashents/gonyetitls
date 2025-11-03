<?php

namespace App\Http\Livewire\ChecklistCategories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ChecklistItem;
use App\Models\CategoryChecklist;
use App\Models\ChecklistCategory;
use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    public $checklist_items;
    public $checklist_item_id;
    public $checklist_sub_categories;
    public $checklist_sub_category_id;
    private $category_checklists;
    public $category_checklist_id;
    public $checklist_category;
    public $checklist_category_id;


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

    private function resetInputFields(){
        $this->checklist_sub_category_id = "" ;
        $this->checklist_item_id = "" ;
    }


      public function refresh($category){

        if($category == "checklist_items"){
            $this->checklist_items = ChecklistItem::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Items Refreshed Successfully!!."
            ]);
        }
        elseif($category == "checklist_sub_categories"){
            $this->checklist_sub_categories = ChecklistSubCategory::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item Groups Refreshed Successfully!!."
            ]);
        }
    }

   


    public function mount($id){
        $this->checklist_category_id = $id;
        $this->checklist_category = ChecklistCategory::find($id);
        $this->checklist_sub_categories = ChecklistSubCategory::orderBy('name','asc')->get();
        $this->checklist_items = ChecklistItem::orderBy('name','asc')->get();
    }

    public function store(){
        if (isset($this->checklist_item_id)) {
            foreach ($this->checklist_item_id as $key => $value) {
                $category_checklist = new CategoryChecklist;
                $category_checklist->user_id = Auth::user()->id;
                $category_checklist->checklist_category_id = $this->checklist_category_id;
                if (isset($this->checklist_sub_category_id[$key])) {
                    $category_checklist->checklist_sub_category_id = $this->checklist_sub_category_id[$key];
                }
                if (isset($this->checklist_item_id[$key])) {
                    $category_checklist->checklist_item_id = $this->checklist_item_id[$key];
                }

                $category_checklist->save();
            }

            $this->dispatchBrowserEvent('hide-category_checklistModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Item Added Successfully!!"
            ]);
        }
    }

    public function edit($id){
        $category_checklist = CategoryChecklist::find($id);
        $this->checklist_category_id = $category_checklist->checklist_category_id;
        $this->checklist_sub_category_id = $category_checklist->checklist_sub_category_id;
        $this->checklist_item_id = $category_checklist->checklist_item_id;
        $this->category_checklist_id = $category_checklist->id;
        $this->dispatchBrowserEvent('show-category_checklistEditModal');
    }

    public function update(){
        if (isset($this->category_checklist_id)) {
            $category_checklist = CategoryChecklist::find($this->category_checklist_id);
            $category_checklist->checklist_category_id = $this->checklist_category_id;
            $category_checklist->checklist_sub_category_id = $this->checklist_sub_category_id;
            $category_checklist->checklist_item_id = $this->checklist_item_id;
            $category_checklist->update();

            $this->dispatchBrowserEvent('hide-category_checklistEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>" Checklist Item Updated Successfully!!"
            ]);
        }
    }

    public function render()
    {
        $base = CategoryChecklist::query()
            ->with(['checklist_category', 'checklist_item', 'checklist_sub_category'])
            ->where('category_checklists.checklist_category_id', $this->checklist_category_id)
            ->leftJoin(
                'checklist_sub_categories',
                'category_checklists.checklist_sub_category_id',
                '=',
                'checklist_sub_categories.id'
            )
            ->select('category_checklists.*')
            ->orderByRaw('checklist_sub_categories.name IS NULL') // nulls last
            ->orderBy('checklist_sub_categories.name', 'asc');

        if (filled($this->search)) {
            $term = '%' . $this->search . '%';

            $base->where(function ($q) use ($term) {
                $q->whereHas('checklist_item', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                })->orWhereHas('checklist_sub_category', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                });
            });
        }

        return view('livewire.checklist-categories.show', [
            'category_checklists' => $base->paginate(10),
        ]);
        
    }
}
