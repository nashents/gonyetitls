<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAuditLog extends Model
{
    // Audit logs are immutable — no updated_at, no soft deletes
    const UPDATED_AT = null;

    protected $fillable = [
        'company_id', 'payroll_run_id', 'payroll_id',
        'auditable_type', 'auditable_id',
        'action', 'user_id',
        'old_values', 'new_values',
        'ip_address', 'user_agent',
        'reason', 'performed_at',
    ];

    protected $casts = [
        'old_values'   => 'array',
        'new_values'   => 'array',
        'performed_at' => 'datetime',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write an audit entry from the current request context.
     */
    public static function record(
        string $action,
        ?int $payrollRunId = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $reason = null,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?int $companyId = null,
    ): self {
        return static::create([
            'company_id'      => $companyId,
            'payroll_run_id'  => $payrollRunId,
            'auditable_type'  => $auditableType,
            'auditable_id'    => $auditableId,
            'action'          => $action,
            'user_id'         => auth()->id(),
            'old_values'      => $oldValues ?: null,
            'new_values'      => $newValues ?: null,
            'ip_address'      => request()?->ip(),
            'user_agent'      => request()?->userAgent(),
            'reason'          => $reason,
            'performed_at'    => now(),
        ]);
    }
}
