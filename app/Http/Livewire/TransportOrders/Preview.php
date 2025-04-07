<?php

namespace App\Http\Livewire\TransportOrders;

use Livewire\Component;

class Preview extends Component
{
    public $company;
    public $customer;
    public $transport_order;

    public function mount($company, $customer, $transport_order){
        $this->company = $company;
        $this->customer = $customer;
        $this->transport_order = $transport_order;
    }

    public function render()
    {
        return view('livewire.transport-orders.preview');
    }
}
