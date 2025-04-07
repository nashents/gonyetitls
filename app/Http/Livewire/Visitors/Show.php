<?php

namespace App\Http\Livewire\Visitors;

use App\Models\Visitor;
use Livewire\Component;

class Show extends Component
{
    public $visitor;

    public function mount($id){
        $this->visitor = Visitor::find($id);
    }

    public function render()
    {
        return view('livewire.visitors.show');
    }
}
