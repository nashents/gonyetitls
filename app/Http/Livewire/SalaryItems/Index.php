<?php

namespace App\Http\Livewire\SalaryItems;

use App\Models\salary_item;
use Livewire\Component;
use App\Models\SalaryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $salary_items;
    public $percentage;
    public $description;
    public $amount;
    public $name;
    public $type;
    public $calculate_by = "currency";
    public $calculate_on;

    public $salary_item_id;
    public $user_id;

    public function mount(){
        $this->salary_items = SalaryItem::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'percentage' => 'required',
        'description' => 'required',
        'amount' => 'required',
        'type' => 'required',
        'name' => 'required|unique:salary_items,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->percentage = '';
        $this->description = '';
        $this->name = '';
        $this->amount = '';
        $this->type = '';
    }

    public function store(){
        try{
        $salary_item = new SalaryItem;
        $salary_item->user_id = Auth::user()->id;
        $salary_item->name = $this->name;
        $salary_item->type = $this->type;
        $salary_item->description = $this->description;
        $salary_item->percentage = $this->percentage;
        $salary_item->amount = $this->amount;
        $salary_item->save();

        $this->dispatchBrowserEvent('hide-salary_itemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'percentage'=>'success',
            'message'=>"Salary Item Created Successfully!!"
        ]);

        // return redirect()->route('salary_items.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'percentage'=>'error',
            'message'=>"Something goes wrong while creating salary item!!"
        ]);
    }
    }

    public function edit($id){
    $salary_item = SalaryItem::find($id);

    $this->user_id = $salary_item->user_id;
    $this->name = $salary_item->name;
    $this->percentage = $salary_item->percentage;
    $this->type = $salary_item->type;
    $this->description = $salary_item->description;
    $this->amount = $salary_item->amount;
    $this->salary_item_id = $salary_item->id;
    $this->dispatchBrowserEvent('show-salary_itemEditModal');

    }


    public function update()
    {
        if ($this->salary_item_id) {
            try{
            $salary_item = SalaryItem::find($this->salary_item_id);
            $salary_item->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'type' => $this->type,
                'description' => $this->description,
                'percentage' => $this->percentage,
                'amount' => $this->amount,
            ]);

            $this->dispatchBrowserEvent('hide-salary_itemEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'percentage'=>'success',
                'message'=>"Salary Item Updated Successfully!!"
            ]);


            // return redirect()->route('salary_items.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-salary_itemEditModal');
            $this->dispatchBrowserEvent('alert',[
                'percentage'=>'error',
                'message'=>"Something goes wrong while creating salary item!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->salary_items = SalaryItem::latest()->get();
        return view('livewire.salary-items.index',[
            'salary_items'=>   $this->salary_items
        ]);
    }
}
