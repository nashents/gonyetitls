<?php

namespace App\Http\Livewire\CreditNotes;

use App\Models\Invoice;
use Livewire\Component;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;

class CreditNoteItems extends Component
{
    public $credit_note;
    public $credit_note_items;
    public $credit_note_item_id;
    public $item;
    public $qty;
    public $amount;
    public $subtotal;
    public $item_subtotal;
    public $credit_note_items_total;
    public $new_credit_note_total;
    public $credit_note_total;
    public $invoice_amount;
    public $invoice_balance;
    public $edited_item_subtotal;
  
    public $total;
    public $description;
    public $credit_note_id;

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
        $this->credit_note = CreditNote::find($id);
        $this->credit_note_id = $id;
        $this->invoice = $this->credit_note->invoice;
        $this->total = $this->credit_note->total;
        $this->credit_note_items = $this->credit_note->credit_note_items;
    }

    public function store(){
        if (isset($this->item)) {
            foreach($this->item as $key => $value){
                $credit_note_item = new CreditNoteItem;
                $credit_note_item->credit_note_id = $this->credit_note->id;
                if (isset($this->item[$key])) {
                    $credit_note_item->item = $this->item[$key];
                }
                if (isset($this->description[$key])) {
                    $credit_note_item->description = $this->description[$key];
                }
                if (isset($this->qty[$key])) {
                    $credit_note_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $credit_note_item->amount = $this->amount[$key];
                }
                if (isset($this->amount[$key]) && isset($this->qty[$key])) {
                $this->subtotal = ($this->amount[$key] * $this->qty[$key]);
                $credit_note_item->subtotal = $this->subtotal;
                }
                $credit_note_item->save();
                $this->credit_note_items_total =  $this->credit_note_items_total +  $this->subtotal;
            }

        }

        $invoice = Invoice::find($this->invoice->id);
        $credit_note = CreditNote::find($this->credit_note->id);
        $credit_note->invoice_amount = $invoice->total;
        $this->new_credit_note_total = $this->total + $this->credit_note_items_total;
        $credit_note->total = $this->new_credit_note_total;
        $credit_note->invoice_balance = $invoice->total -  $this->new_credit_note_total;
        $credit_note->update();

        $this->dispatchBrowserEvent('hide-addcredit_noteItemModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Credit Note Item(s) Added Successfully!!"
        ]);

   
    }

    public function edit($id){
        $credit_note_item = CreditNoteItem::find($id);
        $this->credit_note = $credit_note_item->credit_note;
        $this->credit_note_items = $this->credit_note->credit_note_items;
        $this->invoice = $this->credit_note->invoice;
        $this->credit_note_item_id = $credit_note_item->id;
        $this->item = $credit_note_item->item;
        $this->description = $credit_note_item->description;
        $this->amount = $credit_note_item->amount;
        $this->qty = $credit_note_item->qty;
        $this->subtotal = $credit_note_item->subtotal;
        $this->dispatchBrowserEvent('show-editCredit_noteItemModal');

    }

    public function update(){
        if ($this->credit_note_item_id) {
            $credit_note_item = CreditNoteItem::find($this->credit_note_item_id);
            $credit_note_item->item = $this->item;
            $credit_note_item->description = $this->description;
            $credit_note_item->qty = $this->qty;
            $credit_note_item->amount = $this->amount;
            if (isset($this->amount) && isset($this->qty)) {
                $this->item_subtotal = ($this->amount * $this->qty);
                $credit_note_item->subtotal = $this->item_subtotal;
                }
            $credit_note_item->update();
            $this->total = CreditNoteItem::where('credit_note_id',$this->credit_note->id)->sum('subtotal');
           
            $invoice = Invoice::find($this->invoice->id);
            $credit_note = CreditNote::find($this->credit_note->id);
            $this->invoice_amount =  $credit_note->invoice_amount;
            $credit_note->total = $this->total;
            $credit_note->invoice_balance = $this->invoice_amount - $this->total;
            $credit_note->update();

            $this->dispatchBrowserEvent('hide-editCredit_noteItemModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Credit Note Item Updated Successfully!!"
            ]);
            return redirect(request()->header('Referer'));
    
        }
      
    }

    public function removeShow($credit_note_item_id){
        $this->credit_note_item = CreditNoteItem::find($credit_note_item_id);
        $this->subtotal = $this->credit_note_item->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeCreditNoteItem(){ 
      
        $credit_note = $this->credit_note_item->credit_note;
        $this->credit_note_total = $credit_note->total;
        $this->invoice_amount = $credit_note->invoice_amount;
        $this->invoice_balance = $credit_note->invoice_balance;
        $this->new_credit_note_total = $this->total-$this->subtotal;;

        $credit_note =  CreditNote::find($this->credit_note->id);
        $credit_note->total = $this->new_credit_note_total;
        $credit_note->invoice_balance = $this->invoice_amount - $this->new_credit_note_total;
        $credit_note->update();

        $this->credit_note_item->delete();

        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Credit Note Item Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $this->credit_note_items = CreditNoteItem::where('credit_note_id',$this->credit_note_id)->get();
        return view('livewire.credit-notes.credit-note-items',[
            'credit_note_items' => $this->credit_note_items
        ]);
    }
}
