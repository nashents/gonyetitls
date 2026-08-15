<?php

namespace App\Http\Livewire\DebitNotes;

use Livewire\Component;
use App\Models\DebitNote;

class Show extends Component
{
    public $debit_note;

    public function mount($id){
        $this->debit_note = DebitNote::find($id);
    }
    public function render()
    {
        return view('livewire.debit-notes.show');
    }
}
