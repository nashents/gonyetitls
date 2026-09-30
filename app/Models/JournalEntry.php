<?php

namespace App\Models;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\Bill;
use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class JournalEntry extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
            'company_id',
            'invoice_id',
            'bill_id',
            'payment_id',
            'payroll_run_id',
            'credit_note_id',
            'debit_note_id',
            'customer_fuel_supply_id',
            'debtor_journal_id',
            'supplier_journal_id',
            'is_manual',
            'journal_number',
            'date',
            'reference',
            'description',
            'status',
            'created_by_id',
            'posted_by_id',
            'posted_at',
        ];

    protected $casts = [
        'date'      => 'date',
        'posted_at' => 'datetime',
        'is_manual' => 'boolean',
    ];

    protected static function booted()
    {
        // A draft isn't in the Trial Balance, so it's checked when it's posted.
        static::updating(function (JournalEntry $entry) {
            if ($entry->isDirty('status') && $entry->getOriginal('status') === 'draft' && $entry->status !== 'draft') {
                $entry->assertBalanced();
            }
        });
    }

    /**
     * The one rule every posting must meet before it's committed, so the
     * Trial Balance can't be thrown out by any source - bill, invoice,
     * payment, payroll, manual journal or anything added later. Called by
     * each posting service at the end of its DB::transaction, where throwing
     * rolls the whole posting back.
     *
     *  - Debits must equal credits in the company's reporting currency - the
     *    same amounts the Trial Balance sums (a line's own debit/credit when
     *    it's in that currency, its exchange_debit/credit otherwise).
     *  - When every line is in one currency, the raw debits and credits must
     *    also agree. Mixed-currency entries (e.g. a payment with a realized
     *    FX gain/loss line) legitimately don't balance raw, only in the
     *    reporting currency.
     *
     * Each converted line is rounded to the cent on its own, so a few lines
     * may drift from a total rounded once - half a cent per extra line is
     * allowed for that and nothing more.
     *
     * Reversals (JournalReversalService) aren't checked: a reversal mirrors
     * its original line for line, so the pair always nets to zero - and it's
     * how a broken one-sided entry gets taken back out.
     */
    public function assertBalanced(): void
    {
        if ($this->status === 'draft') {
            return;
        }

        $baseCurrencyId = Company::whereKey($this->company_id)->value('currency_id');
        $lines = $this->journal_entry_lines()->get(['currency_id', 'debit', 'credit', 'exchange_debit', 'exchange_credit']);

        if ($lines->isEmpty()) {
            throw new UnbalancedJournalEntryException("Journal entry {$this->journal_number} ({$this->sourceLabel()}) has no lines.");
        }

        $inBase = fn ($line) => $line->currency_id === null || (int) $line->currency_id === (int) $baseCurrencyId;

        $reportDebit = $lines->sum(fn ($l) => (float) ($inBase($l) ? $l->debit : $l->exchange_debit));
        $reportCredit = $lines->sum(fn ($l) => (float) ($inBase($l) ? $l->credit : $l->exchange_credit));
        $rawDebit = $lines->sum(fn ($l) => (float) $l->debit);
        $rawCredit = $lines->sum(fn ($l) => (float) $l->credit);
        $singleCurrency = $lines->map(fn ($l) => $inBase($l) ? (int) $baseCurrencyId : (int) $l->currency_id)->unique()->count() === 1;

        $tolerance = 0.005 * ($lines->count() - 1) + 0.0001;

        $reportOff = abs($reportDebit - $reportCredit) > $tolerance;
        $rawOff = $singleCurrency && abs($rawDebit - $rawCredit) > $tolerance;

        if ($reportOff || $rawOff) {
            [$debit, $credit] = $reportOff ? [$reportDebit, $reportCredit] : [$rawDebit, $rawCredit];

            throw new UnbalancedJournalEntryException(sprintf(
                'Journal entry %s (%s) does not balance%s: debits %s, credits %s, difference %s. It was not posted.',
                $this->journal_number,
                $this->sourceLabel(),
                $reportOff ? ' in the reporting currency' : '',
                number_format($debit, 2),
                number_format($credit, 2),
                number_format($debit - $credit, 2)
            ));
        }
    }

    private function sourceLabel(): string
    {
        foreach (['bill_id' => 'bill', 'invoice_id' => 'invoice', 'payment_id' => 'payment', 'credit_note_id' => 'credit note',
                  'debit_note_id' => 'debit note', 'payroll_run_id' => 'payroll run', 'customer_fuel_supply_id' => 'customer fuel supply',
                  'debtor_journal_id' => 'debtor journal', 'supplier_journal_id' => 'supplier journal'] as $column => $label) {
            if ($this->{$column}) {
                return "{$label} #{$this->{$column}}" . ($this->reference ? ", {$this->reference}" : '');
            }
        }

        return ($this->is_manual ? 'manual journal' : 'no source document') . ($this->reference ? ", {$this->reference}" : '');
    }

    public function journal_entry_lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function customerFuelSupply()
    {
        return $this->belongsTo(CustomerFuelSupply::class);
    }

    public function debtorJournal()
    {
        return $this->belongsTo(DebtorJournal::class);
    }

    public function supplierJournal()
    {
        return $this->belongsTo(SupplierJournal::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function posted_by()
    {
        return $this->belongsTo(User::class, 'posted_by_id');
    }
}
