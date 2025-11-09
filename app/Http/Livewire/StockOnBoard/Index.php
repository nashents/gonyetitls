<?php

namespace App\Http\Livewire\StockOnBoard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ChecklistItem;
use Illuminate\Validation\Rule;
use App\Models\CategoryChecklist;
use App\Models\ChecklistCategory;
use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
     use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $category_checklists;
    public $checklist_category;
    public $equipment;
    public $horse_id;
    public $vehicle_id;
    public $trailer_id;
    public $checklist_items;
    public $checklist_item_id;
    public $checklist_sub_categories;
    public $checklist_sub_category_id;
    public $category_checklist_id;
    public $checklist_category_id;
    public $condition;


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

    public function mount($id, $equipment){
        $this->checklist_category = ChecklistCategory::where('name','Stock on board')->first();
        $this->checklist_category_id = $this->checklist_category?->id;
        $this->checklist_sub_categories = ChecklistSubCategory::orderBy('name','asc')->get();
        $this->checklist_items = ChecklistItem::orderBy('name','asc')->get();
        
        $this->equipment = $equipment;
        if ($this->equipment == "horse") {
            $this->horse_id = $id;
        }elseif ($this->equipment == "vehicle") {
            $this->vehicle_id = $id;
        }elseif ($this->equipment == "trailer") {
             $this->trailer_id = $id;
        }
       
    }

    public function store(){
        
        $typeName = optional(\App\Models\ChecklistCategory::find($this->checklist_category_id))->name ?? 'this checklist';
        
        $this->validate([
            // the array itself
            'checklist_item_id' => ['required', 'array'],
            // prevent duplicates within the submitted array too
            'checklist_item_id.*' => [
                'required',
                'distinct',
               Rule::unique('category_checklists', 'checklist_item_id')
                ->where(function ($q) {
                    $q->where('checklist_category_id', $this->checklist_category_id)
                    ->whereNull('deleted_at')
                    ->when($this->equipment === 'horse', function ($qq) {
                        $qq->where('horse_id', $this->horse_id)
                            ->whereNull('vehicle_id')->whereNull('trailer_id');
                    })
                    ->when($this->equipment === 'vehicle', function ($qq) {
                        $qq->where('vehicle_id', $this->vehicle_id)
                            ->whereNull('horse_id')->whereNull('trailer_id');
                    })
                    ->when($this->equipment === 'trailer', function ($qq) {
                        $qq->where('trailer_id', $this->trailer_id)
                            ->whereNull('horse_id')->whereNull('vehicle_id');
                    });
                }),
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
                if ($this->equipment == "horse") {
                    $category_checklist->horse_id = $this->horse_id;
                }elseif ($this->equipment == "vehicle") {
                    $category_checklist->vehicle_id = $this->vehicle_id;
                }elseif ($this->equipment == "trailer") {
                    $category_checklist->trailer_id = $this->trailer_id;
                }
                if (isset($this->checklist_sub_category_id[$key])) {
                    $category_checklist->checklist_sub_category_id = $this->checklist_sub_category_id[$key];
                }
                if (isset($this->checklist_item_id[$key])) {
                    $category_checklist->checklist_item_id = $this->checklist_item_id[$key];
                }
                if (isset($this->condition[$key])) {
                    $category_checklist->condition = $this->condition[$key];
                }

                $category_checklist->save();
            }

            $this->checklist_item_id = [];

            $this->dispatchBrowserEvent('hide-category_checklistModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Item Added Successfully!!"
            ]);
        }
    }

    public function edit($id){
        $this->checklist_item_id = Null;
        $category_checklist = CategoryChecklist::find($id);
        $this->checklist_category_id = $category_checklist->checklist_category_id;
        $this->checklist_sub_category_id = $category_checklist->checklist_sub_category_id;
        $this->checklist_item_id = $category_checklist->checklist_item_id;
        $this->condition = $category_checklist->condition;
        $this->category_checklist_id = $category_checklist->id;
        $this->dispatchBrowserEvent('show-category_checklistEditModal');
    }
    public function showDelete($id){
       
        $category_checklist = CategoryChecklist::find($id);
        $this->category_checklist_id = $category_checklist->id;
        $this->dispatchBrowserEvent('show-category_checklistDeleteModal');
       
    }
    public function delete(){
       
        $category_checklist = CategoryChecklist::find($this->category_checklist_id );
        $category_checklist->delete();
        $this->dispatchBrowserEvent('hide-category_checklistDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Removed Successfully!!"
        ]);
    }

    public function update(){
        if (isset($this->category_checklist_id)) {
            $category_checklist = CategoryChecklist::find($this->category_checklist_id);
            $category_checklist->checklist_category_id = $this->checklist_category_id;
            $category_checklist->checklist_sub_category_id = $this->checklist_sub_category_id;
            $category_checklist->checklist_item_id = $this->checklist_item_id;
            $category_checklist->condition = $this->condition;
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

        if ($this->equipment == "horse") {
           $base->where('horse_id',$this->horse_id);
        }elseif ($this->equipment == "vehicle") {
            $base->where('vehicle_id',$this->vehicle_id);
        }elseif ($this->equipment == "trailer") {
             $base->where('trailer_id',$this->trailer_id);
        }

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

        return view('livewire.stock-on-board.index', [
            'category_checklists' => $base->paginate(10),
        ]);
        
    }
}
