<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Driver;
use Livewire\Component;

class Trips extends Component
{

    public $trips;
    public $driver;

    public function mount($id){
        $this->driver = Driver::find($id);
        $this->trips = $this->driver->trips;
    }

    public function render()
    {
        return view('livewire.drivers.trips');
    }
}
