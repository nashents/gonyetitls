<?php

namespace App\Http\Livewire\Payments;

use App\Models\Payment;
use Livewire\Component;
use App\Services\Accounting\CustomerDepositService;

class Show extends Component
{
    public $payment;

    public function mount($id){
        $this->payment = Payment::with([
            'invoice_payments.invoice',
            'bill_payments.bill',
        ])->find($id);
    }
    public function render()
    {
        // What is left of this one deposit - drawdown_balance is the running
        // wallet total, not this payment's own remainder.
        $deposit_available = null;

        if ($this->payment && $this->payment->customer_id && $this->payment->transaction_category === CustomerDepositService::CATEGORY) {
            $deposit = app(CustomerDepositService::class)
                ->deposits((int) $this->payment->customer_id, (int) $this->payment->currency_id)
                ->first(fn ($deposit) => $deposit->payment->id === $this->payment->id);

            $deposit_available = $deposit ? $deposit->available : null;
        }

        return view('livewire.payments.show', [
            'deposit_available' => $deposit_available,
        ]);
    }
}
