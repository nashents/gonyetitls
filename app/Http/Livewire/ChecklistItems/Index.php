<?php

namespace App\Http\Livewire\ChecklistItems;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ChecklistItem;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    private $checklist_items;
    public $checklist_item_id;
    public $name;
    public $notes;

    public function mount(){
        
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:checklist_items,name,NULL,id,deleted_at,NULL|string',
    ];

    private function resetInputFields(){
        $this->notes = '';
        $this->name = '';
    }

    public function store(){
        // try{
        $checklist_item = new ChecklistItem;
        $checklist_item->user_id = Auth::user()->id;
        $checklist_item->name = $this->name;
        $checklist_item->notes = $this->notes;
        $checklist_item->save();

        $this->dispatchBrowserEvent('hide-checklist_itemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inspection Item Created Successfully!!"
        ]);


    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating checklist item!!"
    //     ]);
    // }
    }

    public function edit($id){
    $checklist_item = ChecklistItem::find($id);
    $this->user_id = $checklist_item->user_id;
    $this->name = $checklist_item->name;
    $this->notes = $checklist_item->notes;
    $this->checklist_item_id = $checklist_item->id;
    $this->dispatchBrowserEvent('show-checklist_itemEditModal');

    }


    public function update()
    {
        if ($this->checklist_item_id) {
            try{
           $checklist_item = ChecklistItem::find($this->checklist_item_id);
           $checklist_item->name = $this->name;
           $checklist_item->notes = $this->notes;
           $checklist_item->update();

            $this->dispatchBrowserEvent('hide-checklist_itemEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Inspection Item Updated Successfully!!"
            ]);

            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-checklist_itemEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating inspection item!!"
            ]);
          }
        }
    }

    public function render()
    {
      
        return view('livewire.checklist-items.index',[
            'checklist_items'=> ChecklistItem::orderBy('created_at','desc')->paginate(10)
        ]);
    }
}
