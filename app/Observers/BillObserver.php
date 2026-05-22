<?php

namespace App\Observers;

use App\Models\Bill;
use App\Services\Accounting\BillJournalService;

class BillObserver
{
    /**
     * Handle the Bill "created" event.
     *
     * @param  \App\Models\Bill  $bill
     * @return void
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
        if ($bill->isDirty('authorization') && $bill->authorization === 'approved') {
            app(BillJournalService::class)->post($bill);
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
