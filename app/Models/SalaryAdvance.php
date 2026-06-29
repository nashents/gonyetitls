<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryAdvance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'company_id', 'currency_id',
        'advance_number', 'amount', 'balance', 'monthly_recovery_amount',
        'advance_date', 'repayment_start_date',
        'reason', 'notes',
        'authorization', 'authorized_by', 'authorized_at', 'authorization_comments',
        'status', 'created_by',
    ];

    protected $casts = [
        'amount'                   => 'decimal:4',
        'balance'                  => 'decimal:4',
        'monthly_recovery_amount'  => 'decimal:4',
        'advance_date'             => 'date',
        'repayment_start_date'     => 'date',
        'authorized_at'            => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function isPending(): bool  { return $this->authorization === 'pending'; }
    public function isApproved(): bool { return $this->authorization === 'approved'; }
    public function isUnpaid(): bool   { return in_array($this->status, ['Unpaid','Partially Paid']); }

    /**
     * Amount recoverable in the next payroll run.
     * If monthly_recovery_amount is 0, recover full balance.
     */
    public function recoveryThisRun(): float
    {
        if (!$this->isApproved() || !$this->isUnpaid()) {
            return 0.0;
        }
        $recovery = $this->monthly_recovery_amount > 0
            ? $this->monthly_recovery_amount
            : (float) $this->balance;

        return min($recovery, (float) $this->balance);
    }
}
