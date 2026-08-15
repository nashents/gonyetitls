<?php

namespace App\Http\Livewire\DebitNotes;

use Livewire\Component;
use App\Models\DebitNote;

class Deleted extends Component
{
    public $debit_notes;

    public function mount(){
        $this->debit_notes = DebitNote::onlyTrashed()->latest()->get();
    }

    public function render()
    {
        return view('livewire.debit-notes.deleted');
    }
}
