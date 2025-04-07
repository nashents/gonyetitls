<?php

namespace App\Http\Livewire\Trips;

use App\Models\Payment;
use Livewire\Component;

class Payments extends Component
{    public $payments;
    public $payment_id;

    public function mount($id){
        $this->payments = Payment::where('trip_id',$id)->latest()->get();
    }
    public function render()
    {
        return view('livewire.trips.payments',[
            'payments' => $this->payments,
        ]);
    }
}
