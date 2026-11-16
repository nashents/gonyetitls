<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * A license-fee invoice Gonyeti raises against a client company (one per
 * billing month). Balances are kept current by recalculate(), which is the
 * only place status / amount_paid / balance should be written.
 */
class SubscriptionInvoice extends Model
{
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    protected $guarded = ['id'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
        'last_reminder_at' => 'datetime',
        'amount' => 'float',
        'amount_paid' => 'float',
        'balance' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function notifications()
    {
        return $this->hasMany(SubscriptionNotification::class);
    }

    /** Still owed: unpaid or part-paid (cancelled and paid are closed). */
    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL]);
    }

    public function isOutstanding(): bool
    {
        return in_array($this->status, [self::STATUS_UNPAID, self::STATUS_PARTIAL], true);
    }

    public function isOverdue(): bool
    {
        return $this->isOutstanding() && $this->due_date && $this->due_date->lt(Carbon::today());
    }

    /** Label shown to users: an outstanding invoice past its due date reads "Overdue". */
    public function getStatusLabelAttribute(): string
    {
        return $this->isOverdue() ? 'Overdue' : ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->isOverdue()) {
            return 'danger';
        }

        return [
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'warning',
            self::STATUS_CANCELLED => 'secondary',
        ][$this->status] ?? 'info';
    }

    public function money($value): string
    {
        return trim(($this->currency?->symbol ?? '') . number_format((float) $value, 2));
    }

    /** Re-derive paid / balance / status from the recorded payments. */
    public function recalculate(): self
    {
        $paid = round((float) $this->payments()->sum('amount'), 2);
        $this->amount_paid = $paid;
        $this->balance = round(max(0, $this->amount - $paid), 2);

        if ($this->status !== self::STATUS_CANCELLED) {
            if ($this->balance <= 0) {
                $this->status = self::STATUS_PAID;
                $this->paid_at = $this->paid_at ?? now();
            } else {
                $this->status = $paid > 0 ? self::STATUS_PARTIAL : self::STATUS_UNPAID;
                $this->paid_at = null;
            }
        }

        $this->save();

        return $this;
    }
}
