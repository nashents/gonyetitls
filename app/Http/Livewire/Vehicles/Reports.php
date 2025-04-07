<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;

class Reports extends Component
{

    public $search = NULL;
    public $to;
    public $from;
    public $vehicles;

    public function search(){

        $this->search = TRUE;
        $this->vehicles = Vehicle::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

    }
    public function render()
    {
        return view('livewire.vehicles.reports');
    }
}
