<?php

namespace App\Observers;

use App\Models\JournalEntry;
use App\Models\Payment;
use App\Services\Accounting\JournalReversalService;
use App\Services\Accounting\PaymentJournalService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function created(Payment $payment)
    {
        try {
            app(PaymentJournalService::class)->post($payment);
        } catch (\Throwable $e) {
            Log::error('PaymentJournalService failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Payment "updated" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function updated(Payment $payment)
    {
        //
    }

    /**
     * Handle the Payment "deleted" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function deleted(Payment $payment)
    {
        // Safety net: a deleted payment must not stay in the ledger (see
        // BillObserver::deleted). No-op where the caller already reversed.
        app(JournalReversalService::class)->reverseLiveEffects(
            JournalEntry::where('payment_id', $payment->id),
            "Payment {$payment->payment_number} deleted"
        );
    }

    /**
     * Handle the Payment "restored" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function restored(Payment $payment)
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function forceDeleted(Payment $payment)
    {
        //
    }
}
