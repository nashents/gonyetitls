<?php

namespace App\Http\Livewire\DebitNotes;

use Livewire\Component;

class Preview extends Component
{
    public $bill;
    public $bill_expenses;
    public $debit_note;
    public $debit_note_items;
    public $company;

    public function mount($debit_note, $debit_note_items, $company){
        $this->debit_note = $debit_note;
        $this->bill = $debit_note->bill;
        $this->bill_expenses = $this->bill ? $this->bill->bill_expenses : collect();
        $this->debit_note_items = $debit_note_items;
        $this->company = $company;
    }

    public function render()
    {
        return view('livewire.debit-notes.preview');
    }
}
