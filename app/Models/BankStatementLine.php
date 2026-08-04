<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BankStatementLine extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'bank_statement_id',
        'bank_account_id',
        'transaction_date',
        'value_date',
        'description',
        'reference',
        'external_ref',
        'debit',
        'credit',
        'balance',
        'dedupe_hash',
        'status',
        'matched_journal_entry_line_id',
        'matched_at',
        'matched_by_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'value_date'       => 'date',
        'debit'            => 'decimal:2',
        'credit'           => 'decimal:2',
        'balance'          => 'decimal:2',
        'matched_at'       => 'datetime',
    ];

    public function bank_statement()
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function matched_journal_entry_line()
    {
        return $this->belongsTo(JournalEntryLine::class, 'matched_journal_entry_line_id');
    }

    public function matched_by()
    {
        return $this->belongsTo(User::class, 'matched_by_id');
    }

    /**
     * Positive = money in (credit), negative = money out (debit) - the sign
     * convention used throughout matching/auto-match amount comparisons.
     */
    public function getAmountAttribute(): float
    {
        return (float) $this->credit - (float) $this->debit;
    }

    public function isUnmatched(): bool
    {
        return $this->status === 'unmatched';
    }
}
