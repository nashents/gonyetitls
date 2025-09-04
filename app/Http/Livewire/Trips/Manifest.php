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
        $this->company = $trip->company;
        $this->customer = $trip->customer;
        $this->consignee = $trip->consignee;
        $this->cargo = $trip->cargo;

        $this->from = Destination::find($trip->from);
        $this->to = Destination::find($trip->to);
    }
    public function render()
    {
        return view('livewire.trips.manifest');
    }
}
