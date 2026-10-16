<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\User;
use App\Services\Accounting\AdvanceInvoiceReclassService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * Catch-up for advance invoices stuck in Customer Advances: until 2026-10-16
 * the release to Sales only ran from an unused class, so trips reaching
 * Offloaded never released anything. Releases every approved advance invoice
 * whose trips are all Offloaded and that has no live RECLASS- entry yet.
 */
class ReleaseEarnedAdvanceInvoices extends Command
{
    protected $signature = 'invoices:release-earned-advances
                            {--dry-run : List what would be released without posting}
                            {--user= : User id recorded as poster (default: first admin)}';

    protected $description = 'Release advance invoices whose trips are all Offloaded from Customer Advances to Sales';

    public function handle(AdvanceInvoiceReclassService $service): int
    {
        $user = $this->option('user') ? User::find($this->option('user')) : User::whereHas('employee')->orderBy('id')->first();
        if ($user) {
            Auth::setUser($user);
        }

        $dryRun = (bool) $this->option('dry-run');
        $released = 0;
        $rows = [];

        Invoice::where('invoice_type', 'advance')->where('authorization', 'approved')->orderBy('id')
            ->chunkById(100, function ($invoices) use ($service, $dryRun, &$released, &$rows) {
                foreach ($invoices as $invoice) {
                    if ($dryRun) {
                        // Check only: run the real check inside a transaction we roll back
                        \DB::beginTransaction();
                        $status = $service->reclassIfEarned($invoice->id, $pending);
                        \DB::rollBack();
                    } else {
                        $status = $service->reclassIfEarned($invoice->id, $pending);
                    }

                    if ($status === AdvanceInvoiceReclassService::POSTED) {
                        $released++;
                    }
                    if ($status !== AdvanceInvoiceReclassService::ALREADY_POSTED) {
                        $rows[] = [$invoice->invoice_number, $status, implode(', ', $pending ?? [])];
                    }
                }
            });

        if ($rows) {
            $this->table(['Invoice', 'Result', 'Trips not Offloaded'], $rows);
        }
        $this->info(($dryRun ? 'Would release: ' : 'Released: ') . $released . ' invoice(s).');

        return self::SUCCESS;
    }
}
