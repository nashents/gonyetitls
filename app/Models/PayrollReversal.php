<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PayrollReversal extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id', 'original_payroll_run_id', 'reversal_payroll_run_id',
        'reversal_type', 'employee_ids', 'reason', 'status',
        'requested_by', 'approved_by',
        'approval_comments', 'rejection_reason',
        'requested_at', 'approved_at', 'completed_at',
        'earnings_reversed', 'deductions_reversed', 'loans_reversed',
        'advances_reversed', 'leave_accruals_reversed',
        'gl_postings_reversed', 'statutory_liabilities_reversed',
    ];

    protected $casts = [
        'employee_ids'                  => 'array',
        'requested_at'                  => 'datetime',
        'approved_at'                   => 'datetime',
        'completed_at'                  => 'datetime',
        'earnings_reversed'             => 'boolean',
        'deductions_reversed'           => 'boolean',
        'loans_reversed'                => 'boolean',
        'advances_reversed'             => 'boolean',
        'leave_accruals_reversed'       => 'boolean',
        'gl_postings_reversed'          => 'boolean',
        'statutory_liabilities_reversed'=> 'boolean',
    ];

    public function originalRun()
    {
        return $this->belongsTo(PayrollRun::class, 'original_payroll_run_id');
    }

    public function reversalRun()
    {
        return $this->belongsTo(PayrollRun::class, 'reversal_payroll_run_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPendingApproval(): bool { return $this->status === 'pending_approval'; }
    public function isApproved(): bool        { return $this->status === 'approved'; }
    public function isCompleted(): bool       { return $this->status === 'completed'; }
    public function isFullyReversed(): bool
    {
        return $this->earnings_reversed
            && $this->deductions_reversed
            && $this->loans_reversed
            && $this->advances_reversed
            && $this->gl_postings_reversed
            && $this->statutory_liabilities_reversed;
    }
}
