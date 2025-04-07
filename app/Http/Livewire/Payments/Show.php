<?php

namespace App\Http\Livewire\Payments;

use App\Models\Payment;
use Livewire\Component;

class Show extends Component
{
    public $payment;

    public function mount($id){
        $this->payment = Payment::find($id);
    }
    public function render()
    {
        return view('livewire.payments.show');
    }
}
