<?php

namespace App\Http\Livewire\TransportOrders;

use Livewire\Component;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $transport_orders;

    public function mount(){

        $this->transport_orders = TransportOrder::latest()->take(50)->get();
    }
    public function render()
    {
        return view('livewire.transport-orders.index',[
            'transport_orders' => $this->transport_orders
        ]);
    }
}
