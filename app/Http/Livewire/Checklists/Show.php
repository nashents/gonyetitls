<?php

namespace App\Http\Livewire\Checklists;

use Livewire\Component;
use App\Models\Checklist;

class Show extends Component
{
    public $checklist;
    public $checklist_results;

    public function mount($id){
        $this->checklist = Checklist::find($id);
        $this->checklist_results = $this->checklist->checklist_results;
    }
    public function render()
    {
        return view('livewire.checklists.show');
    }
}
