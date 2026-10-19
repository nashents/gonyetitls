<?php

namespace App\Observers;

use App\Models\InvoicePayment;
use App\Services\Accounting\DrawdownJournalService;

/**
 * A customer deposit applied to an invoice (source 'drawdown') posts
 * DR Customer Deposits / CR AR; removing the allocation reverses it. Other
 * sources (direct payments, debtors journals, customer fuel) post through
 * their own documents.
 */
class InvoicePaymentObserver
{
    public function created(InvoicePayment $allocation)
    {
        app(DrawdownJournalService::class)->postInvoiceDrawdown($allocation);
    }

    public function deleted(InvoicePayment $allocation)
    {
        if ($allocation->source === 'drawdown') {
            app(DrawdownJournalService::class)->reverse('invoice_payment_id', $allocation->id, 'Deposit allocation removed');
        }
    }

    public function restored(InvoicePayment $allocation)
    {
        app(DrawdownJournalService::class)->postInvoiceDrawdown($allocation);
    }
}
