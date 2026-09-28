<?php

namespace App\Observers;

use App\Models\BillExpense;
use App\Models\JournalEntry;
use App\Services\Accounting\BillJournalService;
use Illuminate\Support\Facades\Log;

/**
 * Posts a bill that was created already approved + to_be_paid (the legacy
 * fuel-order/trip flows save the Bill first and its BillExpense after) once
 * its expense lines are in place - see BillObserver::created() for why the
 * bill can't be posted on its own insert. Waits until the lines balance to
 * the bill total, so a multi-line bill saved in a loop posts once, after
 * its last line.
 */
class BillExpenseObserver
{
    public function saved(BillExpense $billExpense)
    {
        $bill = $billExpense->bill;

        if (! $bill || $bill->trashed() || $bill->authorization !== 'approved' || $bill->to_be_paid != true) {
            return;
        }

        // Any journal history at all means it was posted (or deliberately
        // reversed) already - corrections go through LedgerResyncService.
        if (JournalEntry::withTrashed()->where('bill_id', $bill->id)->exists()) {
            return;
        }

        $journal = app(BillJournalService::class);

        if (! $journal->isPostable($bill)) {
            return;
        }

        try {
            $journal->post($bill);
        } catch (\Throwable $e) {
            Log::error("BillJournalService failed for bill #{$bill->id} after expense line save: " . $e->getMessage());
        }
    }
}
