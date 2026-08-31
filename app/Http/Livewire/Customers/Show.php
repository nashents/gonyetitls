<?php

namespace App\Http\Livewire\Customers;

use Livewire\Component;
use App\Models\Currency;
use App\Mail\CustomerPortalCredentialsMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class Show extends Component
{

    public $customer;
    public $curencies;
    public $trips;
    public $invoices;

    private function generatePin($digits = 4)
    {
        $pin = '';
        for ($i = 0; $i < $digits; $i++) {
            $pin .= mt_rand(0, 9);
        }
        return $pin;
    }

    public function mount($customer){
        $this->customer = $customer;
        $this->currencies = Currency::all();
        $this->trips = $this->customer->trips;
        $this->invoices = $this->customer->invoices;
    }

    public function generateAndSendCredentials()
    {
        $pin = $this->generatePin();

        $this->customer->update([
            'password' => Hash::make($pin),
            'portal_enabled' => true,
        ]);

        $company = Auth::user()->company ?? Auth::user()->employee->company ?? null;

        Mail::to($this->customer->email)->send(
            new CustomerPortalCredentialsMail($this->customer, $company, $pin, route('customer.login'))
        );

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Portal PIN generated and emailed to {$this->customer->email}. PIN: {$pin}",
        ]);
    }

    public function toggleActivation()
    {
        $this->customer->update(['portal_enabled' => ! $this->customer->portal_enabled]);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Portal access ' . ($this->customer->portal_enabled ? 'activated.' : 'disabled.'),
        ]);
    }

    public function render()
    {
        return view('livewire.customers.show');
    }
}
