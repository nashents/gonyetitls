<?php

namespace App\Http\Livewire\DebitNotes;

use App\Models\Bill;
use Livewire\Component;
use App\Models\DebitNote;
use App\Models\DebitNoteItem;

class DebitNoteItems extends Component
{
    public $debit_note;
    public $debit_note_items;
    public $debit_note_item_id;
    public $item;
    public $qty;
    public $amount;
    public $subtotal;
    public $item_subtotal;
    public $debit_note_items_total;
    public $new_debit_note_total;
    public $debit_note_total;
    public $bill_amount;
    public $bill_balance;
    public $edited_item_subtotal;

    public $total;
    public $description;
    public $debit_note_id;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    private function resetInputFields(){
        $this->item = "" ;
        $this->description = "" ;
        $this->qty = "" ;
        $this->amount = "" ;
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }


    public function mount($id){
        $this->debit_note = DebitNote::find($id);
        $this->debit_note_id = $id;
        $this->bill = $this->debit_note->bill;
        $this->total = $this->debit_note->total;
        $this->debit_note_items = $this->debit_note->debit_note_items;
    }

    public function store(){
        if (isset($this->item)) {
            foreach($this->item as $key => $value){
                $debit_note_item = new DebitNoteItem;
                $debit_note_item->debit_note_id = $this->debit_note->id;
                if (isset($this->item[$key])) {
                    $debit_note_item->item = $this->item[$key];
                }
                if (isset($this->description[$key])) {
                    $debit_note_item->description = $this->description[$key];
                }
                if (isset($this->qty[$key])) {
                    $debit_note_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $debit_note_item->amount = $this->amount[$key];
                }
                if (isset($this->amount[$key]) && isset($this->qty[$key])) {
                $this->subtotal = ($this->amount[$key] * $this->qty[$key]);
                $debit_note_item->subtotal = $this->subtotal;
                }
                $debit_note_item->save();
                $this->debit_note_items_total =  $this->debit_note_items_total +  $this->subtotal;
            }

        }

        $debit_note = DebitNote::find($this->debit_note->id);
        $this->new_debit_note_total = $this->total + $this->debit_note_items_total;
        $debit_note->total = $this->new_debit_note_total;
        if ($this->bill) {
            $bill = Bill::find($this->bill->id);
            $debit_note->bill_amount = $bill->total;
            $debit_note->bill_balance = $bill->total - $this->new_debit_note_total;
        }
        $debit_note->update();

        $this->dispatchBrowserEvent('hide-adddebit_noteItemModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Debit Note Item(s) Added Successfully!!"
        ]);


    }

    public function edit($id){
        $debit_note_item = DebitNoteItem::find($id);
        $this->debit_note = $debit_note_item->debit_note;
        $this->debit_note_items = $this->debit_note->debit_note_items;
        $this->bill = $this->debit_note->bill;
        $this->debit_note_item_id = $debit_note_item->id;
        $this->item = $debit_note_item->item;
        $this->description = $debit_note_item->description;
        $this->amount = $debit_note_item->amount;
        $this->qty = $debit_note_item->qty;
        $this->subtotal = $debit_note_item->subtotal;
        $this->dispatchBrowserEvent('show-editDebit_noteItemModal');

    }

    public function update(){
        if ($this->debit_note_item_id) {
            $debit_note_item = DebitNoteItem::find($this->debit_note_item_id);
            $debit_note_item->item = $this->item;
            $debit_note_item->description = $this->description;
            $debit_note_item->qty = $this->qty;
            $debit_note_item->amount = $this->amount;
            if (isset($this->amount) && isset($this->qty)) {
                $this->item_subtotal = ($this->amount * $this->qty);
                $debit_note_item->subtotal = $this->item_subtotal;
                }
            $debit_note_item->update();
            $this->total = DebitNoteItem::where('debit_note_id',$this->debit_note->id)->sum('subtotal');

            $debit_note = DebitNote::find($this->debit_note->id);
            $this->bill_amount =  $debit_note->bill_amount;
            $debit_note->total = $this->total;
            if ($this->bill) {
                $debit_note->bill_balance = $this->bill_amount - $this->total;
            }
            $debit_note->update();

            $this->dispatchBrowserEvent('hide-editDebit_noteItemModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Debit Note Item Updated Successfully!!"
            ]);
            return redirect(request()->header('Referer'));

        }

    }

    public function removeShow($debit_note_item_id){
        $this->debit_note_item = DebitNoteItem::find($debit_note_item_id);
        $this->subtotal = $this->debit_note_item->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeDebitNoteItem(){

        $debit_note = $this->debit_note_item->debit_note;
        $this->debit_note_total = $debit_note->total;
        $this->bill_amount = $debit_note->bill_amount;
        $this->bill_balance = $debit_note->bill_balance;
        $this->new_debit_note_total = $this->total-$this->subtotal;;

        $debit_note =  DebitNote::find($this->debit_note->id);
        $debit_note->total = $this->new_debit_note_total;
        $debit_note->bill_balance = $this->bill_amount - $this->new_debit_note_total;
        $debit_note->update();

        $this->debit_note_item->delete();

        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Debit Note Item Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $this->debit_note_items = DebitNoteItem::where('debit_note_id',$this->debit_note_id)->get();
        return view('livewire.debit-notes.debit-note-items',[
            'debit_note_items' => $this->debit_note_items
        ]);
    }
}
