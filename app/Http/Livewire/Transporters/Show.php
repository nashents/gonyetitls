<?php

namespace App\Http\Livewire\Transporters;

use Livewire\Component;

class Show extends Component
{

    public $transporter;
    public $trips;
    public $bills;

    public function mount($transporter){
        $this->transporter = $transporter;
        $this->trips = $this->transporter->trips;
        $this->bills = $this->transporter->bills;
    }
    
    public function render()
    {
        return view('livewire.transporters.show');
    }
}
