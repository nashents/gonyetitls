<?php

namespace App\Http\Livewire\Requisitions;

use App\Models\Trip;
use App\Models\Expense;
use Livewire\Component;
use App\Models\Requisition;

use App\Models\RequisitionItem;

class Items extends Component
{
    public $expenses;
    public $expense;
    public $expense_id;

    public $reason;
    public $qty;
    public $amount;
    public $trips;
    public $subtotal;
    public $item_subtotal;
    public $added_item_subtotal;
    public $deleted_item_subtotal;
    public $edited_item_subtotal;
    public $x;
    public $old_subtotal;
    public $total;
    public $requisition;
    public $requisition_id;
    public $requisition_items;
    public $requisition_item_id;

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

    public function mount($id){
        $this->requisition_id = $id;
        $this->requisition = Requisition::find($id);
        $this->subtotal =  $this->requisition->subtotal;
        $this->total =   $this->requisition->total;
        $this->expenses = Expense::orderBy('name','asc')->get();
        $this->reason = $this->requisition->reason;
        $this->requisition_items = RequisitionItem::where('requisition_id',$this->requisition_id)->get();

    }

    private function resetInputFields(){
        $this->qty = '';
        $this->amount = '';
        $this->expense_id = '';
        $this->subtotal = Null;
     
    }
   

    public function store(){

            if (isset($this->expense_id)) {
                foreach($this->expense_id as $key => $value){
                    $requisition_item = new RequisitionItem;
                    $requisition_item->requisition_id = $this->requisition->id;
                    if (isset($this->expense_id[$key])) {
                        $requisition_item->expense_id = $this->expense_id[$key];
                    }
                   
                    if (isset($this->qty[$key])) {
                        $requisition_item->qty = $this->qty[$key];
                    }
                    if (isset($this->amount[$key])) {
                        $requisition_item->amount = $this->amount[$key];
                    }
                    if (isset($this->amount[$key]) && isset($this->qty[$key])) {
                    $item_subtotal = ($this->amount[$key] * $this->qty[$key]);
                    $requisition_item->subtotal = $item_subtotal;
                    }
                    $this->subtotal =  $this->subtotal +  $item_subtotal;
                    $requisition_item->save();
                }
    
            }
    
            $requisition = Requisition::find($this->requisition->id);
            $requisition->total =  $requisition->total +  $this->subtotal;
            $requisition->update();
               

        $this->dispatchBrowserEvent('hide-requisition_itemModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Item(s) Added Successfully!!"
        ]);
    }


    public function edit($id){
        $this->requisition_item_id = $id;
        $requisition_item = RequisitionItem::find($id);
        $this->expense_id = $requisition_item->expense_id;
        $this->qty = $requisition_item->qty;
        $this->amount = $requisition_item->amount;
        $this->old_subtotal = $requisition_item->subtotal;
        $this->dispatchBrowserEvent('show-requisition_itemEditModal');
    }

    public function removeShow($id){
        $this->requisition_item_id = $id;
        $requisition_item = RequisitionItem::find($id);
        $this->old_subtotal = $requisition_item->subtotal;
        $this->dispatchBrowserEvent('show-requisition_itemDeleteModal');
    }

    public function delete(){ 
        $requisition =  Requisition::find($this->requisition_id);
        if (isset($this->old_subtotal) && $this->old_subtotal > 0) {
            $requisition->total = $requisition->total - $this->old_subtotal;
           
        }
        $requisition->update();
      
        $requisition_item = RequisitionItem::find( $this->requisition_item_id);
        $requisition_item->delete();

        $this->dispatchBrowserEvent('hide-requisition_itemDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Item Deleted Successfully!!"
        ]);
    }

    public function update(){

        $requisition_item = RequisitionItem::find($this->requisition_item_id);
    
        $requisition_item->expense_id = $this->expense_id;
        $requisition_item->qty = $this->qty;
        $requisition_item->amount = $this->amount;
        if ((isset($this->qty) && $this->qty > 0) && (isset($this->amount) && $this->amount > 0 ) ) {
            $this->subtotal = $this->amount * $this->qty; 
        }
        $requisition_item->subtotal =  $this->subtotal;
        $requisition_item->update();

        $requisition =  Requisition::find($this->requisition_id);
        if (isset($this->old_subtotal) && isset($this->subtotal)) {
            $total = ($requisition->total - $this->old_subtotal) + $this->subtotal;
            $requisition->total = $total;
        }
        $requisition->update();

        $this->dispatchBrowserEvent('hide-requisition_itemEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Item Updated Successfully!!"
        ]);
    }


    public function render()
    {
        $this->requisition_items = RequisitionItem::where('requisition_id',$this->requisition_id)->get();
        return view('livewire.requisitions.items',[
            'requisition_items' => $this->requisition_items
        ]);
    }
}
