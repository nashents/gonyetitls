<?php

namespace App\Http\Livewire\TopUps;

use Livewire\Component;

class Show extends Component
{
    public $top_up;

    public function mount($top_up){
        $this->top_up = $top_up;
    }
    public function render()
    {
        return view('livewire.top-ups.show');
    }
}
