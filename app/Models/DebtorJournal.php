<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Manual adjustment to a customer's account - see the
 * create_debtor_journals_table migration and DebtorJournalService for the
 * accounting.
 */
class DebtorJournal extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public const DEBIT = 'debit';
    public const CREDIT = 'credit';

    protected $fillable = [
        'company_id',
        'user_id',
        'journal_number',
        'customer_id',
        'currency_id',
        'exchange_rate',
        'type',
        'account_id',
        'date',
        'amount',
        'reference',
        'description',
        'status',
        'voided_by_id',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'date'          => 'date',
        'voided_at'     => 'datetime',
        'amount'        => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voided_by()
    {
        return $this->belongsTo(User::class, 'voided_by_id');
    }

    public function invoice_payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function journal_entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function journal_entry()
    {
        return $this->hasOne(JournalEntry::class)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->latestOfMany('id');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function isCredit(): bool
    {
        return $this->type === self::CREDIT;
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function allocatedAmount(): float
    {
        return round((float) $this->invoice_payments()->sum(\DB::raw('COALESCE(amount+0,0)')), 2);
    }

    public function unallocatedAmount(): float
    {
        if (!$this->isCredit() || $this->isVoided()) {
            return 0.0;
        }

        return round(max(0, (float) $this->amount - $this->allocatedAmount()), 2);
    }
}
