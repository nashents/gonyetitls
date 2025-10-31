<?php

namespace App\Http\Livewire\Trips;

use Livewire\Component;
use App\Models\Destination;

class Manifest extends Component
{
    public $trip;
    public $manifest;
    public $company;
    public $consignee;
    public $customer;
    public $from;
    public $to;
    public $cargo;

    public function mount($trip){
        
        $this->trip = $trip;
        $this->company = $trip->company ?? null;
        $this->customer = $trip->customer ?? null;
        $this->consignee = $trip->consignee ?? null;
        $this->cargo = $trip->cargo ?? null;

        $this->from = Destination::find($trip->from);
        $this->to = Destination::find($trip->to);
    }
    public function render()
    {
        return view('livewire.trips.manifest');
    }
}
