<?php

namespace App\Http\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;

class Reports extends Component
{

    public $search = NULL;
    public $to;
    public $from;
    public $customers;

    public function search(){

        $this->search = TRUE;
        $this->customers = Customer::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

    }
    public function render()
    {
        return view('livewire.customers.reports');
    }
}
