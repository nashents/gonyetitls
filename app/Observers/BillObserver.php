<?php

namespace App\Observers;

use App\Models\Bill;
use App\Services\Accounting\BillJournalService;
use Illuminate\Support\Facades\Log;

class BillObserver
{
    /**
     * Deliberately does NOT post. A bill that was just inserted can't have
     * any bill_expenses yet (they need its id), so posting here always
     * wrote an Accounts Payable credit with no debit leg - and that entry
     * then blocked the correct post via BillJournalService's existing-entry
     * guard. A bill created already approved + to_be_paid is posted by
     * BillExpenseObserver instead, once its expense lines add up to the
     * bill total.
     */
    public function created(Bill $bill)
    {
        //
    }

    /**
     * Handle the Bill "updated" event.
     *
     * @param  \App\Models\Bill  $bill
     * @return void
     */
    public function updated(Bill $bill)
    {
        if ($bill->isDirty('authorization') && $bill->authorization === 'approved' && $bill->to_be_paid == True) {
            $this->post($bill);
        }
    }

    /**
     * A posting failure here (e.g. a missing control account, or a bill
     * created with incomplete data by a flow that skipped a required
     * field) must never roll back the approval itself - without this catch,
     * an uncaught exception here propagates out of the model event into
     * whatever DB::transaction() the approval action is wrapped in,
     * silently reverting the authorization change too and leaving the user
     * with no indication anything went wrong. Matches PaymentObserver's
     * catch-and-log pattern; the bill is picked up by the existing
     * "unposted bills" count and manual "Post to Ledger" retry instead.
     */
    private function post(Bill $bill): void
    {
        try {
            app(BillJournalService::class)->post($bill);
        } catch (\Throwable $e) {
            Log::error('BillJournalService failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Bill "deleted" event.
     *
     * @param  \App\Models\Bill  $bill
     * @return void
     */
    public function deleted(Bill $bill)
    {
        //
    }

    /**
     * Handle the Bill "restored" event.
     *
     * @param  \App\Models\Bill  $bill
     * @return void
     */
    public function restored(Bill $bill)
    {
        //
    }

    /**
     * Handle the Bill "force deleted" event.
     *
     * @param  \App\Models\Bill  $bill
     * @return void
     */
    public function forceDeleted(Bill $bill)
    {
        //
    }
}
