<?php

namespace App\Http\Livewire\TransportOrders;

use App\Models\Destination;
use App\Models\TransportOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Preview extends Component
{
    public $company;
    public $transport_order;
    public $origin;
    public $destination;
    public $pattern;
    public $authorizer;
    

    public function mount($id){
        $this->company = Auth::user()->employee->company;

        $this->transport_order = TransportOrder::with([
        'customer:id,name',
        'trip_origins.destination.country',
        'trip_origins.loading_point',

        'trip_destinations.destination.country',
        'trip_destinations.offloading_point',

        'fromDestination.country',
        'toDestination.country',

        'loading_point',
        'offloading_point',
        ])->find($id);
        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        $this->origin = Destination::find($this->transport_order->from);
        $this->destination = Destination::find($this->transport_order->to);
        $this->authorizer = User::find($this->transport_order->authorized_by_id);
        
     
    }

    public function render()
    {
        return view('livewire.transport-orders.preview');
    }
}
