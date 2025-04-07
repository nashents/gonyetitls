<?php

namespace App\Http\Livewire\Brokers;

use App\Models\Broker;
use Livewire\Component;

class Show extends Component
{

    public $broker;
    public $trips;
    public function mount($id){
        $this->broker = Broker::find($id);
        $this->trips = $this->broker->trips;
    }
    public function render()
    {
        return view('livewire.brokers.show');
    }
}
