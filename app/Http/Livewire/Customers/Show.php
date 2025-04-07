<?php

namespace App\Http\Livewire\Customers;

use Livewire\Component;
use App\Models\Currency;

class Show extends Component
{

    public $customer;
    public $curencies;
    public $trips;
    public $invoices;

    public function mount($customer){
        $this->customer = $customer;
        $this->currencies = Currency::all();
        $this->trips = $this->customer->trips;
        $this->invoices = $this->customer->invoices;
    }
    
    public function render()
    {
        return view('livewire.customers.show');
    }
}
