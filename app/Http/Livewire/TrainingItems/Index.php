<?php

namespace App\Http\Livewire\TrainingItems;

use App\Models\TrainingItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
      use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search','search_from','search_to'];
    public $search_from;
    public $search_to;
    protected $training_items;
    public $training_item_id;
    public $training_item;
    public $name;
  

  

    public function mount(){
        
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:training_items,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
      
        $this->name = '';
     
    }

   

    public function store(){
        try{

        $training_item = new TrainingItem;
        $training_item->user_id = Auth::user()->id;
        $training_item->name = $this->name;
        $training_item->save();

        $this->dispatchBrowserEvent('hide-training_itemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Training Item Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating training item!!"
        ]);
    }
    }

    public function edit($id){
    $training_item = TrainingItem::find($id);
    $this->name = $training_item->name;
    $this->training_item_id = $training_item->id;
    $this->dispatchBrowserEvent('show-training_itemEditModal');

    }


    public function update()
    {
        if ($this->training_item_id) {
            try{
            $training_item = TrainingItem::find($this->training_item_id);
            $training_item->name = $this->name;
            $training_item->update();

            $this->dispatchBrowserEvent('hide-training_itemEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Training Item Updated Successfully!!"
            ]);


            // return redirect()->route('training_items.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-training_itemEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating training item!!"
            ]);
          }
        }
    }


    public function render()
    {
        $training_items = TrainingItem::orderBy('name','asc')->paginate(10);
        return view('livewire.training-items.index',[
            'training_items' =>   $training_items
        ]);
    }
}
