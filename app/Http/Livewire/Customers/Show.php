<?php

namespace App\Http\Livewire\Customers;

use Livewire\Component;
use App\Models\Currency;
use Illuminate\Support\Facades\Hash;

class Show extends Component
{

    public $customer;
    public $curencies;
    public $trips;
    public $invoices;

    public $portal_password;
    public $portal_password_confirmation;

    public function mount($customer){
        $this->customer = $customer;
        $this->currencies = Currency::all();
        $this->trips = $this->customer->trips;
        $this->invoices = $this->customer->invoices;
    }

    public function setPortalPassword()
    {
        $this->validate([
            'portal_password' => 'required|string|min:8|confirmed',
        ]);

        $this->customer->update(['password' => Hash::make($this->portal_password)]);

        $this->reset(['portal_password', 'portal_password_confirmation']);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight portal password set.']);
    }

    public function render()
    {
        return view('livewire.customers.show');
    }
}
