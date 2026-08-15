<?php

namespace App\Http\Livewire\DebitNotes;

use App\Models\Bill;
use App\Models\DebitNote;
use Livewire\Component;

class Index extends Component
{
    public $debit_notes;

    public function mount(){
        $this->debit_notes = DebitNote::latest()->get();
    }

    public function render()
    {
        return view('livewire.debit-notes.index');
    }
}
