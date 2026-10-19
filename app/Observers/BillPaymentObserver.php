<?php

namespace App\Observers;

use App\Models\BillPayment;
use App\Services\Accounting\DrawdownJournalService;

/**
 * A vendor prepayment applied to a bill (source 'drawdown') posts
 * DR AP / CR Vendor Prepayments; removing the allocation reverses it.
 */
class BillPaymentObserver
{
    public function created(BillPayment $allocation)
    {
        app(DrawdownJournalService::class)->postBillDrawdown($allocation);
    }

    public function deleted(BillPayment $allocation)
    {
        if ($allocation->source === 'drawdown') {
            app(DrawdownJournalService::class)->reverse('bill_payment_id', $allocation->id, 'Prepayment allocation removed');
        }
    }

    public function restored(BillPayment $allocation)
    {
        app(DrawdownJournalService::class)->postBillDrawdown($allocation);
    }
}
