<?php

namespace App\Console\Commands;

use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Explains what makes up an account's ledger balance: totals per source
 * (bill / payment / customer fuel / manual journal ...), then every document
 * whose postings still leave a non-zero amount on the account. A document
 * that was reversed and reposted elsewhere nets to zero and is not listed -
 * its debit and credit still show in the Trial Balance's Dr/Cr columns.
 * Same filter as TrialBalanceCalculator (status != draft).
 *
 * php artisan ledger:explain-account "Trip Expense"
 * php artisan ledger:explain-account "Trip Expense" --from=2026-09-01 --to=2026-09-30
 */
class ExplainAccountBalance extends Command
{
    protected $signature = 'ledger:explain-account
        {account=Trip Expense : Account name}
        {--from= : From date (journal date)}
        {--to= : To date (journal date)}
        {--limit=200 : Max documents to list}
        {--bill= : Instead, show every journal entry ever posted for this bill number}';

    protected $description = 'Break an account\'s ledger balance down by source and list the documents still leaving an amount on it';

    public function handle()
    {
        if ($billNumber = $this->option('bill')) {
            return $this->billHistory($billNumber);
        }

        $ids = Account::where('name', $this->argument('account'))->pluck('id');
        if ($ids->isEmpty()) {
            $this->error('No account named "' . $this->argument('account') . '".');
            return self::FAILURE;
        }

        $source = "CASE
            WHEN j.bill_id IS NOT NULL THEN 'Bill'
            WHEN j.payment_id IS NOT NULL THEN 'Payment'
            WHEN j.customer_fuel_supply_id IS NOT NULL THEN 'Customer fuel supply'
            WHEN j.invoice_id IS NOT NULL THEN 'Invoice'
            WHEN j.credit_note_id IS NOT NULL THEN 'Credit note'
            WHEN j.debit_note_id IS NOT NULL THEN 'Debit note'
            WHEN j.supplier_journal_id IS NOT NULL THEN 'Suppliers journal'
            WHEN j.debtor_journal_id IS NOT NULL THEN 'Debtors journal'
            WHEN j.payroll_run_id IS NOT NULL THEN 'Payroll'
            WHEN j.is_manual = 1 THEN 'Manual journal'
            ELSE 'Other' END";
        $document = "COALESCE(CONCAT('bill:', j.bill_id), CONCAT('payment:', j.payment_id), CONCAT('supply:', j.customer_fuel_supply_id),
            CONCAT('invoice:', j.invoice_id), CONCAT('cn:', j.credit_note_id), CONCAT('dn:', j.debit_note_id),
            CONCAT('sj:', j.supplier_journal_id), CONCAT('dj:', j.debtor_journal_id), CONCAT('payroll:', j.payroll_run_id),
            CONCAT('journal:', IF(j.reference LIKE 'REV-%', SUBSTRING(j.reference, 5), j.journal_number)))";

        // Same base-currency amounts as TrialBalanceCalculator.
        $currency = (int) DB::table('companies')->value('currency_id');
        $dr = "(CASE WHEN l.currency_id IS NULL OR l.currency_id = {$currency} THEN l.debit ELSE l.exchange_debit END)";
        $cr = "(CASE WHEN l.currency_id IS NULL OR l.currency_id = {$currency} THEN l.credit ELSE l.exchange_credit END)";

        $base = fn () => DB::table('journal_entry_lines as l')
            ->join('journal_entries as j', 'j.id', '=', 'l.journal_entry_id')
            ->whereIn('l.account_id', $ids)
            ->where('j.status', '!=', 'draft')
            ->whereNull('j.deleted_at')
            ->when($this->option('from'), fn ($q, $d) => $q->where('j.date', '>=', $d))
            ->when($this->option('to'), fn ($q, $d) => $q->where('j.date', '<=', $d));

        $bySource = $base()->selectRaw("{$source} as src, SUM($dr) dr, SUM($cr) cr, SUM($dr - $cr) net")
            ->groupBy('src')->orderByRaw('ABS(net) DESC')->get();

        $this->info('Balance of "' . $this->argument('account') . '" by source (base-currency debit/credit columns):');
        $this->table(['Source', 'Debit', 'Credit', 'Net (Dr - Cr)'], $bySource->map(fn ($r) => [
            $r->src, number_format($r->dr, 2), number_format($r->cr, 2), number_format($r->net, 2),
        ])->push(['TOTAL', number_format($bySource->sum('dr'), 2), number_format($bySource->sum('cr'), 2), number_format($bySource->sum('net'), 2)])->all());

        $docs = $base()->selectRaw("{$source} as src, {$document} as doc, MIN(j.date) first_date,
                GROUP_CONCAT(DISTINCT j.journal_number ORDER BY j.id) journals,
                MAX(j.description) description, SUM($dr - $cr) net")
            ->groupBy('src', 'doc')
            ->havingRaw('ABS(net) >= 0.01')
            ->orderByRaw('ABS(net) DESC')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($docs->isEmpty()) {
            $this->info('Every document nets to zero on this account.');
            return self::SUCCESS;
        }

        $billNumbers = DB::table('bills')->whereIn('id', $docs->filter(fn ($d) => str_starts_with($d->doc, 'bill:'))->map(fn ($d) => (int) substr($d->doc, 5)))->pluck('bill_number', 'id');
        $paymentNumbers = DB::table('payments')->whereIn('id', $docs->filter(fn ($d) => str_starts_with($d->doc, 'payment:'))->map(fn ($d) => (int) substr($d->doc, 8)))->pluck('payment_number', 'id');

        $this->info("Documents still leaving an amount on the account (largest first, max {$this->option('limit')}):");
        $this->table(['Source', 'Document', 'Date', 'Journals', 'Net (Dr - Cr)', 'Description'], $docs->map(function ($d) use ($billNumbers, $paymentNumbers) {
            [$type, $id] = explode(':', $d->doc, 2);
            $label = $type === 'bill' ? ($billNumbers[$id] ?? $d->doc) : ($type === 'payment' ? ($paymentNumbers[$id] ?? $d->doc) : $d->doc);

            return [$d->src, $label, $d->first_date, $d->journals, number_format($d->net, 2), mb_strimwidth((string) $d->description, 0, 60, '...')];
        })->all());

        return self::SUCCESS;
    }

    /**
     * Every entry for one bill with its status and what it reverses - shows
     * whether repeated postings were each reversed (and when) or are live
     * duplicates.
     */
    private function billHistory(string $billNumber): int
    {
        $billIds = DB::table('bills')->where('bill_number', $billNumber)->pluck('id');
        if ($billIds->isEmpty()) {
            $this->error("No bill {$billNumber}.");
            return self::FAILURE;
        }

        $entries = DB::table('journal_entries as j')
            ->whereIn('j.bill_id', $billIds)
            ->orderBy('j.id')
            ->get(['j.id', 'j.journal_number', 'j.date', 'j.status', 'j.reference', 'j.created_at', 'j.deleted_at', 'j.description']);

        $byNumber = $entries->keyBy('journal_number');
        $live = 0;
        $this->table(['Journal', 'Date', 'Created', 'Status', 'Reverses', 'Reversed by', 'Effect', 'Description'], $entries->map(function ($e) use ($entries, &$live) {
            $reversedBy = optional($entries->firstWhere('reference', "REV-{$e->journal_number}"))->journal_number;
            $isRev = str_starts_with((string) $e->reference, 'REV-');
            $effect = $e->deleted_at ? 'deleted' : ($e->status === 'draft' ? 'draft' : ($e->status === 'reversed' ? 'cancelled' : ($isRev ? 'cancels' : 'LIVE')));
            if ($effect === 'LIVE') {
                $live++;
            }

            return [$e->journal_number, $e->date, substr((string) $e->created_at, 0, 10), $e->status, $isRev ? substr($e->reference, 4) : '', $reversedBy ?? '', $effect, mb_strimwidth((string) $e->description, 0, 50, '...')];
        })->all());

        $live > 1
            ? $this->warn("{$live} live postings - the bill is double-posted.")
            : $this->info("{$live} live posting. Reversed postings and their reversals cancel out; where a reversal's Date differs from the original's it lands in another month (fix: journals:redate-correction-reversals --since=...).");

        return self::SUCCESS;
    }
}
