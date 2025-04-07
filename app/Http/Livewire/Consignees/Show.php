<?php

namespace App\Http\Livewire\Consignees;

use Livewire\Component;

class Show extends Component
{

    public $consignee;
    public $trips;

    public function mount($consignee){
        $this->consignee = $consignee;
        $this->trips = $this->consignee->trips;
    }

    public function render()
    {
        return view('livewire.consignees.show');
    }
}
