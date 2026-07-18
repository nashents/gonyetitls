<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PayrollRun extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id', 'payroll_frequency_id', 'currency_id',
        'name', 'period_start', 'period_end', 'payroll_date', 'payment_date',
        'status', 'proration_method',
        'employee_count', 'total_gross', 'total_net', 'total_deductions', 'total_employer_contributions',
        'created_by', 'calculated_by', 'validated_by', 'approved_by',
        'locked_by', 'posted_by', 'reversed_by',
        'calculated_at', 'validated_at', 'approved_at', 'locked_at',
        'exported_at', 'posted_at', 'reversed_at',
        'reversal_of_payroll_run_id', 'notes',
    ];

    protected $casts = [
        'period_start'    => 'date',
        'period_end'      => 'date',
        'payroll_date'    => 'date',
        'payment_date'    => 'date',
        'calculated_at'   => 'datetime',
        'validated_at'    => 'datetime',
        'approved_at'     => 'datetime',
        'locked_at'       => 'datetime',
        'exported_at'     => 'datetime',
        'posted_at'       => 'datetime',
        'reversed_at'     => 'datetime',
        'total_gross'     => 'decimal:4',
        'total_net'       => 'decimal:4',
        'total_deductions'=> 'decimal:4',
        'total_employer_contributions' => 'decimal:4',
    ];

    // ── Status helpers ────────────────────────────────────────────────────

    public function isDraft(): bool        { return $this->status === 'draft'; }
    public function isApproved(): bool     { return $this->status === 'approved'; }
    public function isLocked(): bool       { return in_array($this->status, ['locked','posted']); }
    public function isReversed(): bool     { return $this->status === 'reversed'; }
    public function canBeEdited(): bool    { return $this->status === 'draft'; }
    public function canBeApproved(): bool  { return $this->status === 'draft'; }
    public function canBeReversed(): bool  { return in_array($this->status, ['approved','locked','posted']); }

    // ── Relationships ─────────────────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function frequency()
    {
        return $this->belongsTo(PayrollFrequency::class, 'payroll_frequency_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Salary lines for this run go through the legacy Payroll header —
     * payroll_salaries has no payroll_run_id column of its own.
     */
    public function salaries()
    {
        return $this->hasManyThrough(PayrollSalary::class, Payroll::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(PayrollAuditLog::class);
    }

    public function reversals()
    {
        return $this->hasMany(PayrollReversal::class, 'original_payroll_run_id');
    }

    public function reversalOf()
    {
        return $this->belongsTo(PayrollRun::class, 'reversal_of_payroll_run_id');
    }

    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
    public function lockedBy()   { return $this->belongsTo(User::class, 'locked_by'); }
    public function postedBy()   { return $this->belongsTo(User::class, 'posted_by'); }
}
