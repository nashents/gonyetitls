<?php

namespace App\Http\Livewire\Consignors;

use Livewire\Component;

class Show extends Component
{

    public $consignor;
    public $trips;

    public function mount($consignor){
        $this->consignor = $consignor;
        $this->trips = $this->consignor->trips;
    }

    public function render()
    {
        return view('livewire.consignors.show');
    }
}
