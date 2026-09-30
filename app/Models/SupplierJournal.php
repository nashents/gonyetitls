<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Manual adjustment to a supplier's (vendor's) account - see the
 * create_supplier_journals_table migration and SupplierJournalService for
 * the accounting.
 */
class SupplierJournal extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public const DEBIT = 'debit';
    public const CREDIT = 'credit';

    protected $fillable = [
        'company_id',
        'user_id',
        'journal_number',
        'vendor_id',
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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

    public function bill_payments()
    {
        return $this->hasMany(BillPayment::class);
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

    /** A debit reduces what we owe - it's the side that settles bills. */
    public function isDebit(): bool
    {
        return $this->type === self::DEBIT;
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function allocatedAmount(): float
    {
        return round((float) $this->bill_payments()->sum(\DB::raw('COALESCE(amount+0,0)')), 2);
    }

    public function unallocatedAmount(): float
    {
        if (!$this->isDebit() || $this->isVoided()) {
            return 0.0;
        }

        return round(max(0, (float) $this->amount - $this->allocatedAmount()), 2);
    }
}
