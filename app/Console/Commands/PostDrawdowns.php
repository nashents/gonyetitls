<?php

namespace App\Console\Commands;

use App\Models\BillPayment;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Services\Accounting\DrawdownJournalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * Posts deposit/prepayment allocations made before DrawdownJournalService
 * existed (they posted nothing). Idempotent - skips allocations already posted.
 *
 * php artisan ledger:post-drawdowns
 */
class PostDrawdowns extends Command
{
    protected $signature = 'ledger:post-drawdowns';

    protected $description = 'Post existing customer-deposit and vendor-prepayment allocations that never reached the ledger';

    public function handle(DrawdownJournalService $service)
    {
        if (! Auth::check() && ($user = User::whereHas('employee')->first())) {
            Auth::setUser($user);
        }

        $posted = 0;
        foreach (InvoicePayment::where('source', 'drawdown')->get() as $allocation) {
            $posted += $service->postInvoiceDrawdown($allocation) ? 1 : 0;
        }
        foreach (BillPayment::where('source', 'drawdown')->get() as $allocation) {
            $posted += $service->postBillDrawdown($allocation) ? 1 : 0;
        }

        $this->info("Posted {$posted} deposit/prepayment allocation(s).");
        return self::SUCCESS;
    }
}
