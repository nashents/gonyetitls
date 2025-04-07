<?php

namespace App\Http\Livewire\ReminderItems;

use Livewire\Component;
use App\Models\ReminderItem;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    
    public $countries;
    public $reminder_items;
    public $country_a;
    public $country_b;
    public $name;
    public $description;

    public $reminder_item_id;
    public $user_id;

    public function mount(){
        $this->reminder_items = ReminderItem::latest()->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:reminder_items,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->country_a = '';
        $this->country_b = '';
        $this->name = '';
    }

    public function store(){
        try{
        $reminder_item = new ReminderItem;
        $reminder_item->user_id = Auth::user()->id;
        $reminder_item->name = $this->name;
        $reminder_item->save();

        $this->dispatchBrowserEvent('hide-reminder_itemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Reminder Item Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating reminder_item!!"
        ]);
    }
    }

    public function edit($id){
    $reminder_item = ReminderItem::find($id);
    $this->user_id = $reminder_item->user_id;
    $this->name = $reminder_item->name;
    $this->reminder_item_id = $reminder_item->id;
    $this->dispatchBrowserEvent('show-reminder_itemEditModal');

    }


    public function update()
    {
        if ($this->reminder_item_id) {
            try{
            $reminder_item = ReminderItem::find($this->reminder_item_id);
            $reminder_item->user_id = Auth::user()->id;
            $reminder_item->name = $this->name;
            $reminder_item->update();
            
            $this->dispatchBrowserEvent('hide-reminder_itemEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Reminder Item Updated Successfully!!"
            ]);


            // return redirect()->reminder_item('reminder_item.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-reminder_itemEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating reminder item!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->reminder_items = ReminderItem::latest()->get();
        return view('livewire.reminder-items.index',[
            'reminder_items'=>   $this->reminder_items
        ]);
    }
}
