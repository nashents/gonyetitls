<?php

namespace App\Services\Payroll;

use App\Models\PayrollAuditLog;
use App\Models\PayrollRun;
use App\Models\JournalEntry;
use App\Services\Accounting\PayrollJournalService;
use Illuminate\Support\Facades\Auth;

/**
 * PayrollRunLifecycleService
 *
 * Single implementation of the PayrollRun status transitions (approve, lock,
 * post, reverse). Previously duplicated verbatim between
 * app/Http/Livewire/PayrollRuns/Index.php and .../Show.php.
 */
class PayrollRunLifecycleService
{
    public function approve(PayrollRun $run): void
    {
        if (!in_array($run->status, ['draft', 'validated'])) {
            abort(422, 'Cannot approve in current status.');
        }
        $run->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        PayrollAuditLog::record('approved', $run->id, [], [], null, null, null, $run->company_id);
    }

    public function lock(PayrollRun $run): void
    {
        if ($run->status !== 'approved') {
            abort(422, 'Run must be approved before locking.');
        }
        $run->update(['status' => 'locked', 'locked_by' => Auth::id(), 'locked_at' => now()]);
        PayrollAuditLog::record('locked', $run->id, [], [], null, null, null, $run->company_id);
    }

    /**
     * Locks the run as posted AND creates the real GL journal entry —
     * "posted to GL" now actually means something.
     */
    public function post(PayrollRun $run): JournalEntry
    {
        if (!in_array($run->status, ['locked', 'exported'])) {
            abort(422, 'Run must be locked before posting.');
        }

        $entry = app(PayrollJournalService::class)->post($run);

        $run->update(['status' => 'posted', 'posted_by' => Auth::id(), 'posted_at' => now()]);
        PayrollAuditLog::record('posted', $run->id, [], [], null, null, null, $run->company_id);

        return $entry;
    }

    public function reverse(PayrollRun $run, string $reason): void
    {
        if (empty($reason)) {
            abort(422, 'A reason is required for reversal.');
        }
        if (!$run->canBeReversed()) {
            abort(422, 'Run cannot be reversed in its current status.');
        }
        $run->update([
            'status'      => 'reversed',
            'reversed_by' => Auth::id(),
            'reversed_at' => now(),
            'notes'       => $reason,
        ]);
        PayrollAuditLog::record('reversed', $run->id, [], [], $reason, null, null, $run->company_id);
    }
}
