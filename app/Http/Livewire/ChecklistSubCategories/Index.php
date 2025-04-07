<?php

namespace App\Http\Livewire\ChecklistSubCategories;

use Livewire\Component;
use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $checklist_sub_categories;
    public $checklist_sub_category_id;
    public $name;


    public function mount(){
        $this->checklist_sub_categories = ChecklistSubCategory::all();
    }
    
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:checklist_sub_categories,name,NULL,id,deleted_at,NULL|string',
    ];

    private function resetInputFields(){
        $this->name = '';
    }

    public function store(){
        try{
            
        $checklist_sub_category = new ChecklistSubCategory;
        $checklist_sub_category->user_id = Auth::user()->id;
        $checklist_sub_category->name = $this->name;
        $checklist_sub_category->save();

        $this->dispatchBrowserEvent('hide-checklist_sub_categoryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inspection Group Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating inspection group!!"
        ]);
    }
    }

    public function edit($id){
    $checklist_sub_category = ChecklistSubCategory::find($id);
    $this->user_id = $checklist_sub_category->user_id;
    $this->name = $checklist_sub_category->name;
    $this->checklist_sub_category_id = $checklist_sub_category->id;
    $this->dispatchBrowserEvent('show-checklist_sub_categoryEditModal');

    }


    public function update()
    {
        if ($this->checklist_sub_category_id) {
            try{
           $checklist_sub_category = ChecklistSubCategory::find($this->checklist_sub_category_id);
           $checklist_sub_category->name = $this->name;
           $checklist_sub_category->update();

            $this->dispatchBrowserEvent('hide-checklist_sub_categoryEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Inspection Group Updated Successfully!!"
            ]);

            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-checklist_sub_categoryEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating inspection group!!"
            ]);
          }
        }
    }

    public function render()
    {
        $this->checklist_sub_categories = ChecklistSubCategory::all();
        return view('livewire.checklist-sub-categories.index',[
            'checklist_sub_categories' => $this->checklist_sub_categories,
        ]);
    }
}
