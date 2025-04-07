<?php

namespace App\Http\Livewire\TruckStops;

use Livewire\Component;
use App\Models\TruckStop;

class Show extends Component
{

    public $truck_stop;

    public function mount($id){
        $this->truck_stop = TruckStop::find($id);
    }

    public function render()
    {
        return view('livewire.truck-stops.show');
    }
}
