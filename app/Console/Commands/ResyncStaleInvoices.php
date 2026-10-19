<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Accounting\JournalReversalService;
use App\Services\Accounting\LedgerResyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * Approved invoices edited after approval were never reposted (the observer
 * only posted at the approval transition), so their Accounts Receivable -
 * and for advance invoices Customer Advances - stayed at the old figures;
 * a later advance release then moved the NEW total, stranding the
 * difference in Customer Advances. Reposts each stale invoice at its
 * current figures (reversal on the old posting's date, repost on the
 * invoice date) and redoes an advance release on its original date.
 * Idempotent - LedgerResyncService skips invoices whose posting matches.
 *
 * php artisan ledger:resync-stale-invoices --dry-run
 */
class ResyncStaleInvoices extends Command
{
    protected $signature = 'ledger:resync-stale-invoices {--dry-run : Preview only, change nothing}';

    protected $description = 'Repost approved invoices whose ledger posting no longer matches their total / rate / currency / date';

    public function handle(LedgerResyncService $resync, JournalReversalService $reversal)
    {
        $stale = [];
        foreach (Invoice::where('authorization', 'approved')->get() as $invoice) {
            $entry = JournalEntry::where('invoice_id', $invoice->id)->where('status', 'posted')
                ->where('reference', 'not like', 'REV-%')->where('reference', 'not like', 'RECLASS-%')
                ->orderBy('id')->get()
                ->first(fn ($e) => $reversal->isLiveEffect($e));
            if (! $entry) {
                continue;
            }

            $ar = $entry->journal_entry_lines()->whereHas('account', fn ($q) => $q->where('name', 'Accounts Receivable'))->first();
            $posted = $ar ? max((float) $ar->debit, (float) $ar->credit) : null;

            if ($posted === null
                || abs($posted - (float) $invoice->total) > 0.01
                || abs((float) ($ar->exchange_rate ?? 1) - (float) ($invoice->exchange_rate ?? 1)) > 0.000001
                || (int) $ar->currency_id !== (int) $invoice->currency_id
                || optional($entry->date)->toDateString() !== substr((string) $invoice->date, 0, 10)) {
                $stale[] = [$invoice, $posted];
            }
        }

        if (! $stale) {
            $this->info('Every approved invoice is posted at its current figures.');
            return self::SUCCESS;
        }

        $this->table(['Invoice', 'Type', 'Posted', 'Now'], array_map(fn ($s) => [
            $s[0]->invoice_number, $s[0]->invoice_type ?: 'standard',
            $s[1] === null ? '-' : number_format($s[1], 2), number_format((float) $s[0]->total, 2),
        ], $stale));

        if ($this->option('dry-run')) {
            $this->warn(count($stale) . ' invoice(s) would be reposted. Nothing changed.');
            return self::SUCCESS;
        }

        $fixed = 0;
        foreach ($stale as [$invoice]) {
            try {
                $user = User::whereHas('employee')->find($invoice->user_id) ?: User::whereHas('employee')->first();
                if ($user) {
                    Auth::setUser($user);
                }
                $resync->resyncInvoice($invoice->fresh(), "Invoice {$invoice->invoice_number} reposted at its current figures", force: true);
                $fixed++;
            } catch (\Throwable $e) {
                $this->error("{$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        $this->info("Reposted {$fixed} invoice(s).");
        return self::SUCCESS;
    }
}
