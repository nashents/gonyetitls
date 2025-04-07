<?php

namespace App\Http\Livewire\CreditNotes;

use Livewire\Component;

class Preview extends Component
{
    public $invoice;
    public $invoice_items;
    public $credit_note;
    public $credit_note_items;
    public $company;

    public function mount($credit_note, $credit_note_items, $company){
        $this->credit_note = $credit_note;
        $this->invoice = $credit_note->invoice;
        $this->invoice_items = $this->invoice->invoice_items;
        $this->credit_note_items = $credit_note_items;
        $this->company = $company;
    }

    public function render()
    {
        return view('livewire.credit-notes.preview');
    }
}
