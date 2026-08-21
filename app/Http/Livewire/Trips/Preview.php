<?php

namespace App\Http\Livewire\Trips;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Preview extends Component
{

    public $trip;
    public $company;

    public function mount($trip){
        $this->trip = $trip;
        $this->company = Auth::user()->employee->company;
    }


    public function render()
    {
        $this->trip->load([
        'trip_transport_orders.transport_order.customer',
        'trip_transport_orders.transport_order.cargo',
        'trip_transport_orders.transport_order.currency',
        'trip_transport_orders.transport_order.fromDestination.country',
        'trip_transport_orders.transport_order.toDestination.country',
        'trip_expenses.allowance',
        'trip_expenses.expense',
        'trip_expenses.currency',
        'trip_expenses.payment_method',
        'customer.contacts',
        'consignee.contacts',
        'driver.employee',
        'cmr_detail',
        // ... rest of trip relations
    ]);
        return view('livewire.trips.preview',[
            'trip' => $this->trip,
            'company' => $this->company
        ]);
    }
}
