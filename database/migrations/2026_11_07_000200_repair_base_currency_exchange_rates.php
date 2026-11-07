<?php

use App\Models\Bill;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\User;
use App\Services\Accounting\JournalReversalService;
use App\Services\Accounting\LedgerResyncService;
use App\Services\Accounting\PaymentJournalService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * A document in the company's own (reporting) currency must carry exchange
 * rate 1. Forms hid the rate field when the user switched back from a
 * foreign currency but kept - and saved - the old rate, so:
 *
 *  - TPI00007 (USD invoice) carried 16.2: its payment TPP00011 posted
 *    JNL-00022 "Realized FX loss" 31,920 (2,100 x (16.2 - 1)).
 *  - FB00016 (USD fuel bill) carried a rate below 1 (0.062-style, copied
 *    from its fuel order): its payment TPP00086 posted JNL-00185
 *    "Realized FX loss" 539.72.
 *
 * The Trial Balance reads reporting-currency lines at their raw amounts, so
 * the documents' own postings were right - only the payment entries' FX
 * lines were wrong (one-sided). A payment saved with a stale rate on a
 * correct document produces the same thing, so those are caught too.
 *
 * For every affected invoice/bill: rate 1 on the document, its lines and its
 * same-currency payments; an invoice is resynced to the ledger (if it was
 * posted); every payment already posted is reversed - dated like the
 * original - and reposted, so the bogus FX line drops out. Bills are not
 * reposted: their lines are in the reporting currency (read raw) and most
 * are source-raised (fuel, trips) with their own account rules.
 *
 * Genuine FX on foreign-currency documents is never touched. Instances
 * without the problem match nothing - no updates, no journal entries.
 * Idempotent: repaired rows have rate 1 and are not picked up again.
 */
class RepairBaseCurrencyExchangeRates extends Migration
{
    private array $bases = [];
    private $soleBase = null;

    public function up()
    {
        $this->bases = DB::table('companies')->whereNotNull('currency_id')->pluck('currency_id', 'id')->all();
        if (! $this->bases) {
            return;
        }
        // Bills have no company_id: safe whenever every company shares one
        // reporting currency.
        $distinct = array_unique(array_map('intval', $this->bases));
        $this->soleBase = count($distinct) === 1 ? reset($distinct) : null;

        $invoices = $this->affected(Invoice::class, 'invoice_id');
        $bills = $this->affected(Bill::class, 'bill_id');

        if ($invoices->isEmpty() && $bills->isEmpty()) {
            return;
        }

        $originalUser = Auth::user();

        foreach ($invoices as $invoice) {
            $this->run($invoice->invoice_number, $invoice->user_id, fn () => $this->repairInvoice($invoice));
        }
        foreach ($bills as $bill) {
            $this->run($bill->bill_number, $bill->user_id, fn () => $this->repairBill($bill));
        }

        if ($originalUser) {
            Auth::setUser($originalUser);
        }
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }

    // ── Selection ─────────────────────────────────────────────────────────

    private function isOff($rate): bool
    {
        return is_numeric($rate) && abs((float) $rate - 1) > 0.000001;
    }

    private function baseFor($companyId, $userId)
    {
        if ($companyId && isset($this->bases[$companyId])) {
            return $this->bases[$companyId];
        }
        if ($this->soleBase) {
            return $this->soleBase;
        }
        $userCompany = optional(optional(optional(User::find($userId))->employee)->company)->id;

        return $userCompany && isset($this->bases[$userCompany]) ? $this->bases[$userCompany] : null;
    }

    /**
     * Reporting-currency documents with a rate other than 1, plus those
     * whose same-currency payment was saved with one.
     */
    private function affected(string $model, string $paymentKey)
    {
        $baseIds = array_values(array_unique($this->bases));

        $byOwnRate = $model::whereIn('currency_id', $baseIds)
            ->whereNotNull('exchange_rate')->where('exchange_rate', '!=', '')
            ->get()->filter(fn ($d) => $this->isOff($d->exchange_rate));

        $viaPayment = Payment::whereNotNull($paymentKey)->whereIn('currency_id', $baseIds)
            ->whereNotNull('exchange_rate')->where('exchange_rate', '!=', '')
            ->get()->filter(fn (Payment $p) => ! $p->isCrossCurrency() && $this->isOff($p->exchange_rate))
            ->pluck($paymentKey)->unique();

        $docs = $byOwnRate->merge($model::whereIn('id', $viaPayment)->get())->unique('id');

        return $docs->filter(function ($d) {
            $base = $this->baseFor($d->company_id ?? null, $d->user_id);

            return $base && (int) $d->currency_id === (int) $base;
        })->values();
    }

    // ── Repair ────────────────────────────────────────────────────────────

    private function run(string $number, $userId, callable $repair): void
    {
        // Postings carry the acting user's company - post as the creator.
        if ($user = User::find($userId)) {
            Auth::setUser($user);
        }

        try {
            DB::transaction($repair);
            echo "  {$number}: exchange rate reset to 1, payments reposted\n";
        } catch (\Throwable $e) {
            echo "  failed {$number}: {$e->getMessage()}\n";
            Log::error("RepairBaseCurrencyExchangeRates: {$number}: " . $e->getMessage());
        }
    }

    private function repairInvoice(Invoice $invoice): void
    {
        $reason = "Invoice {$invoice->invoice_number}: reporting-currency exchange rate reset to 1";

        // Raw updates: no observers - the ledger is redone explicitly below.
        $this->resetHeader('invoices', $invoice->id, $invoice->total);
        $this->resetLines('invoice_items', 'invoice_id', $invoice->id);
        $this->resetPayments('invoice_id', $invoice->id);

        $reversal = app(JournalReversalService::class);
        $posted = JournalEntry::where('invoice_id', $invoice->id)->where('status', '!=', 'reversed')->get()
            ->contains(fn (JournalEntry $e) => $reversal->isLiveEffect($e));

        // A draft/unapproved invoice must not get posted by this repair.
        if ($posted) {
            app(LedgerResyncService::class)->resyncInvoice(Invoice::find($invoice->id), $reason, force: true);
        }

        $this->repostPayments('invoice_id', $invoice->id, $reason);
    }

    private function repairBill(Bill $bill): void
    {
        $reason = "Bill {$bill->bill_number}: reporting-currency exchange rate reset to 1";

        $this->resetHeader('bills', $bill->id, $bill->total);
        $this->resetLines('bill_expenses', 'bill_id', $bill->id);
        $this->resetPayments('bill_id', $bill->id);
        $this->repostPayments('bill_id', $bill->id, $reason);
    }

    private function resetHeader(string $table, int $id, $total): void
    {
        $row = ['exchange_rate' => 1];
        if (Schema::hasColumn($table, 'exchange_amount')) {
            $row['exchange_amount'] = is_numeric($total) ? $total : null;
        }
        DB::table($table)->where('id', $id)->update($row);
    }

    private function resetLines(string $table, string $key, int $id): void
    {
        if (! Schema::hasColumn($table, 'exchange_rate')) {
            return;
        }
        $hasAmount = Schema::hasColumn($table, 'exchange_amount');

        foreach (DB::table($table)->where($key, $id)->get() as $line) {
            $row = ['exchange_rate' => 1];
            if ($hasAmount) {
                $native = $line->subtotal_incl ?? $line->subtotal ?? $line->amount ?? null;
                $rate = is_numeric($line->exchange_rate) ? (float) $line->exchange_rate : 1.0;
                $row['exchange_amount'] = is_numeric($native) ? $native
                    : (is_numeric($line->exchange_amount) && $rate > 0 ? round((float) $line->exchange_amount / $rate, 2) : null);
            }
            DB::table($table)->where('id', $line->id)->update($row);
        }
    }

    /** Same-currency payments only - a cross-currency one keeps its paid_* rate. */
    private function resetPayments(string $key, int $id): void
    {
        foreach (Payment::where($key, $id)->get() as $payment) {
            if (! $payment->isCrossCurrency() && $this->isOff($payment->exchange_rate)) {
                DB::table('payments')->where('id', $payment->id)->update([
                    'exchange_rate'   => 1,
                    'exchange_amount' => $payment->amount,
                ]);
            }
        }
    }

    /** Every payment that was posted: reverse (original date) and post afresh. */
    private function repostPayments(string $key, int $id, string $reason): void
    {
        $reversal = app(JournalReversalService::class);

        foreach (Payment::where($key, $id)->get() as $payment) {
            $reversed = $reversal->reverseLiveEffects(JournalEntry::where('payment_id', $payment->id), $reason, originalDate: true);

            if ($reversed > 0) {
                app(PaymentJournalService::class)->post($payment->fresh());
            }
        }
    }
}
