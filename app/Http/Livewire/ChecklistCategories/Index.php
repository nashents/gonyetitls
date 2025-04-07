<?php

namespace App\Http\Livewire\ChecklistCategories;

use Livewire\Component;
use App\Models\ChecklistCategory;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $checklist_categories;
    public $checklist_category_id;
    public $name;


    public function mount(){
        $this->checklist_categories = ChecklistCategory::all();
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
        try{
            
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


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating checklist !!"
        ]);
    }
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
        $this->checklist_categories = ChecklistCategory::all();
        return view('livewire.checklist-categories.index',[
            'checklist_categories' => $this->checklist_categories,
        ]);
    }
}
