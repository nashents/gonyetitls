<?php

namespace App\Http\Livewire\ChecklistCategories;

use Livewire\Component;
use App\Models\ChecklistItem;
use App\Models\CategoryChecklist;
use App\Models\ChecklistCategory;
use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{

    public $checklist_items;
    public $checklist_item_id;
    public $checklist_sub_categories;
    public $checklist_sub_category_id;
    public $category_checklists;
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



    public function mount($id){
        $this->checklist_category_id = $id;
        $this->checklist_category = ChecklistCategory::find($id);
        $this->category_checklists = CategoryChecklist::where('checklist_category_id', $this->checklist_category_id)->get();
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
                'message'=>"Inspection Item Added to Checklist Successfully!!"
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
                'message'=>"Inspection Item in Checklist Updated Successfully!!"
            ]);
        }
    }

    public function render()
    {
        $this->category_checklists = CategoryChecklist::where('checklist_category_id', $this->checklist_category_id)->get();
        return view('livewire.checklist-categories.show',[
            'category_checklists' => $this->category_checklists
        ]);
    }
}
