<?php

namespace App\Http\Livewire\Trips;

use Livewire\Component;
use App\Models\TransportOrder;

class Orders extends Component
{


    public $orders;


    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->orders = TransportOrder::whereYear('created_at',$period)->latest()->get();
            }else {
                $this->orders = TransportOrder::latest()->get();
            }
        }
      }

    public function render()
    {
        return view('livewire.trips.orders');
    }
}
