<?php

namespace App\Http\Livewire\Trainings;

use Livewire\Component;

class Show extends Component
{
    public $training;

    public function mount($training)
    {
        $this->training = $training;
    }

    public function render()
    {
        return view('livewire.trainings.show');
    }
}
