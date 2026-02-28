<?php

namespace App\Http\Livewire\ChecklistCategories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ChecklistItem;
use Illuminate\Validation\Rule;
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
    public $category_checklist;
    public $category_checklist_id;
    public $checklist_category;
    public $checklist_category_id;
    public $updated = False;


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
        $this->updated = False;
        $this->checklist_sub_category_id = $this->updated == False ?  []  : "" ;
        $this->checklist_item_id = $this->updated == False ?  []  : "";
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

   


    public function mount($id, $equipment_id = Null, $category = Null){

        $this->checklist_category_id = $id;
        $this->checklist_category = ChecklistCategory::find($id);
        $this->checklist_sub_categories = ChecklistSubCategory::orderBy('name','asc')->get();
        $this->checklist_items = ChecklistItem::orderBy('name','asc')->get();
        
    }

      public function removeShow($id){
        $this->category_checklist_id = $id;
        $this->category_checklist = CategoryChecklist::find($id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeItem(){
         
        $category_checklist = CategoryChecklist::find($this->category_checklist_id);
        $category_checklist->delete();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Removed Successfully!!"
        ]);
       
    }

    public function store(){
        $this->updated = False;
        $typeName = optional(\App\Models\ChecklistCategory::find($this->checklist_category_id))->name ?? 'this checklist';
        
        $this->validate([
            // the array itself
            'checklist_item_id' => ['required', 'array'],
            // prevent duplicates within the submitted array too
            'checklist_item_id.*' => [
                'required',
                'distinct',
                Rule::unique('category_checklists', 'checklist_item_id') // 👈 explicit column
                    ->where(fn ($q) => $q->where('checklist_category_id', $this->checklist_category_id)
                                        ->whereNull('deleted_at') // add if the table is soft-deleting
                                        // ->where('company_id', $this->company_id) // add if multi-tenant
                    ),
            ],

            'checklist_sub_category_id'   => ['nullable', 'array'],
            'checklist_sub_category_id.*' => ['nullable'],
        ],
    
            // Custom messages
        [
            'checklist_item_id.required'     => 'Add at least one checklist item.',
            'checklist_item_id.*.required'   => 'Select a checklist item.',
            'checklist_item_id.*.distinct'   => 'You have duplicate checklist items in the list.',
            'checklist_item_id.*.unique'     => "This checklist item is already linked to {$typeName}.",

            'checklist_sub_category_id.nullable'    => 'Add at least one item group.',
            'checklist_sub_category_id.*.nullable'  => 'Select an checklist item group.',
        ],

        // (Optional) Nicely formatted attribute names
        [
            'checklist_item_id.*'  => 'checklist item',
            'checklist_sub_category_id.*' => 'item group',
        ]

    );
    
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
                'message'=>"Item(s) Added To Checklist Successfully!!"
            ]);
        }
    }

    public function edit($id){
        $this->checklist_item_id = Null;
        $this->checklist_sub_category_id = Null;
        $category_checklist = CategoryChecklist::find($id);
        $this->checklist_category_id = $category_checklist->checklist_category_id;
        $this->checklist_sub_category_id = $category_checklist->checklist_sub_category_id;
        $this->checklist_item_id = $category_checklist->checklist_item_id;
        $this->category_checklist_id = $category_checklist->id;
        $this->updated = True;
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
