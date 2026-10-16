<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\Fuel;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\TripExpense;
use App\Models\User;
use App\Services\Accounting\FuelJournalService;
use App\Services\Accounting\JournalReversalService;
use App\Services\Accounting\LedgerResyncService;
use App\Services\Accounting\TripExpenseJournalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * Every posting must carry its origin source document's date. Finds and
 * reposts (reversal dated like the entry it cancels, repost on the source
 * date):
 *  - fuel order bills whose bill_date drifted from the fuel order's date
 *    (an edit re-dated the order but the resync saw an unchanged amount);
 *  - trip expense bills whose bill_date drifted from the trip expense's;
 *  - any bill/invoice/credit/debit note whose live posting is dated
 *    differently from the document.
 * Idempotent.
 *
 * php artisan ledger:sync-posting-dates --dry-run
 */
class SyncPostingDates extends Command
{
    protected $signature = 'ledger:sync-posting-dates {--dry-run : Preview only, change nothing}';

    protected $description = 'Repost documents whose ledger posting is not dated like their origin source document';

    private const DOCUMENTS = [
        'bill_id' => [Bill::class, 'bill_date', 'bill_number'],
        'invoice_id' => [Invoice::class, 'date', 'invoice_number'],
        'credit_note_id' => [CreditNote::class, 'date', 'credit_note_number'],
        'debit_note_id' => [DebitNote::class, 'date', 'debit_note_number'],
    ];

    public function handle(FuelJournalService $fuelJournal, TripExpenseJournalService $tripExpenseJournal, LedgerResyncService $resync, JournalReversalService $reversal)
    {
        $rows = [];
        $fuels = [];
        $tripExpenses = [];
        $documents = [];

        // 1) Generated bills whose own date drifted from their origin.
        foreach (Bill::whereNotNull('fuel_id')->whereNull('top_up_id')->with('fuel')->get() as $bill) {
            $fuel = $bill->fuel;
            if ($fuel && $fuel->authorization === 'approved' && $fuel->date && $this->day($bill->bill_date) !== $this->day($fuel->date)) {
                $fuels[$fuel->id] = $fuel;
                $rows[] = [$bill->bill_number, "fuel order {$fuel->order_number}", $this->day($bill->bill_date), $this->day($fuel->date)];
            }
        }
        foreach (Bill::whereNotNull('trip_expense_id')->whereNull('fuel_id')->whereNull('transporter_id')->with('trip_expense')->get() as $bill) {
            $te = $bill->trip_expense;
            if ($te && $te->date && $this->day($bill->bill_date) !== $this->day($te->date)) {
                $tripExpenses[$te->id] = $te;
                $rows[] = [$bill->bill_number, "trip expense #{$te->id}", $this->day($bill->bill_date), $this->day($te->date)];
            }
        }

        // 2) Live postings dated differently from their (current) document.
        foreach (self::DOCUMENTS as $column => [$model, $dateField, $numberField]) {
            $entries = JournalEntry::whereNotNull($column)->where('status', 'posted')
                ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
                ->get();
            foreach ($entries as $entry) {
                if (! $reversal->isLiveEffect($entry)) {
                    continue;
                }
                $document = $model::find($entry->{$column});
                if (! $document || ! $document->{$dateField} || ($column === 'bill_id' && isset($fuels[$document->fuel_id]))) {
                    continue;
                }
                if ($this->day($entry->date) !== $this->day($document->{$dateField})) {
                    $documents["{$column}:{$document->id}"] = [$column, $document];
                    $rows[] = [$document->{$numberField}, $entry->journal_number, $this->day($entry->date), $this->day($document->{$dateField})];
                }
            }
        }

        if (! $rows) {
            $this->info('Every posting is dated like its source document.');
            return self::SUCCESS;
        }

        $this->table(['Document', 'Source / journal', 'Dated now', 'Source date'], $rows);

        if ($this->option('dry-run')) {
            $this->warn(count($rows) . ' posting(s) would be reposted on the source date. Nothing changed.');
            return self::SUCCESS;
        }

        $fixed = 0;
        $run = function (?int $userId, callable $fn, string $label) use (&$fixed) {
            try {
                // Bills carry no company_id - posting reads Auth::user()->employee.
                $user = ($userId ? User::whereHas('employee')->find($userId) : null) ?: User::whereHas('employee')->first();
                if ($user) {
                    Auth::setUser($user);
                }
                $fn();
                $fixed++;
            } catch (\Throwable $e) {
                $this->error("{$label}: {$e->getMessage()}");
            }
        };

        // postConsumption()/postExpense() reset bill_date from the origin;
        // the resync's date check then reposts on it.
        foreach ($fuels as $fuel) {
            $run($fuel->user_id, fn () => $fuelJournal->postConsumption($fuel->fresh()), "Fuel order {$fuel->order_number}");
        }
        foreach ($tripExpenses as $te) {
            $run($te->user_id, fn () => $tripExpenseJournal->postExpense($te->fresh()), "Trip expense #{$te->id}");
        }
        foreach ($documents as [$column, $document]) {
            $reason = 'Reposted on the source document date';
            $run($document->user_id, fn () => match ($column) {
                'bill_id' => $resync->resyncBill($document->fresh(), $reason),
                'invoice_id' => $resync->resyncInvoice($document->fresh(), $reason),
                'credit_note_id' => $resync->resyncCreditNote($document->fresh(), $reason),
                'debit_note_id' => $resync->resyncDebitNote($document->fresh(), $reason),
            }, $column . ' #' . $document->id);
        }

        $this->info("Reposted {$fixed} item(s).");
        return self::SUCCESS;
    }

    private function day($date): ?string
    {
        return $date ? substr((string) ($date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date), 0, 10) : null;
    }
}
