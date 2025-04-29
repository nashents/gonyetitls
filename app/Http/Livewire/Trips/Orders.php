<?php

namespace App\Http\Livewire\Trips;

use Livewire\Component;
use App\Models\TransportOrder;

class Orders extends Component
{


    public $orders;


    public function mount(){
        $this->orders = TransportOrder::whereYear('created_at', date('Y'))->latest()->get();
      }

    public function render()
    {
        return view('livewire.trips.orders');
    }
}
