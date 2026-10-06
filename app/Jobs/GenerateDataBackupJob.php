<?php

namespace App\Jobs;

use App\Models\DataBackup;
use App\Models\User;
use App\Services\DataBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Builds the all-time Excel backup zip for a DataBackup record. The exports
 * read Auth::user() (company logo, role checks), so the requesting user is
 * set on the guard for the duration of the job.
 */
class GenerateDataBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 7200;

    protected int $backupId;

    public function __construct(int $backupId)
    {
        $this->backupId = $backupId;
    }

    public function handle(DataBackupService $service): void
    {
        // Claim atomically: the run outlasts the queue's retry_after, so a
        // second worker may pick the job up again while this one is busy.
        $claimed = DataBackup::where('id', $this->backupId)
            ->where('status', 'queued')
            ->update(['status' => 'running', 'started_at' => now()]);

        if (! $claimed) {
            return;
        }

        $backup = DataBackup::findOrFail($this->backupId);

        Auth::setUser(User::findOrFail($backup->user_id));

        $result = $service->build();

        $backup->update([
            'status' => 'completed',
            'path' => $result['path'],
            'size' => filesize($result['path']),
            'exported_count' => count($result['exported']),
            'failed_exports' => $result['failed'] ?: null,
            'completed_at' => now(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        $backup = DataBackup::find($this->backupId);
        if (! $backup) {
            return;
        }

        // A re-reserved copy exceeding its attempts while the original is
        // still building — leave the running backup alone.
        if ($e instanceof MaxAttemptsExceededException && $backup->status === 'running') {
            return;
        }

        $backup->update([
            'status' => 'failed',
            'error' => $e->getMessage(),
            'completed_at' => now(),
        ]);
    }
}
