<?php

namespace App\Http\Livewire\CreditNotes;

use Livewire\Component;
use App\Models\CreditNote;

class Show extends Component
{
    public $credit_note;

    public function mount($id){
        $this->credit_note = CreditNote::find($id);
    }
    public function render()
    {
        return view('livewire.credit-notes.show');
    }
}
