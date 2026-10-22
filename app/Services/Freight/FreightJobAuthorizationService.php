<?php

namespace App\Services\Freight;

use App\Mail\AuthorizationNotificationMail;
use App\Mail\PendingNotificationEmails;
use App\Models\FreightJob;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Trip-style authorization for freight jobs: new jobs start 'pending', an
 * authorizer approves or rejects them, and until approved a job can only sit
 * in draft/cancelled and cannot raise bills or invoices.
 */
class FreightJobAuthorizationService
{
    public const NOTIFICATION_CATEGORY = 'Freight Job Authorization';
    public const FREIGHT_DEPARTMENT = 'Freight Forwarding & Clearing';

    /**
     * Super Admins, plus Admins / Management in the Freight Forwarding &
     * Clearing department.
     */
    public function canAuthorize(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $roles = $user->roles->pluck('name')->all();
        if (in_array('Super Admin', $roles, true)) {
            return true;
        }

        $employee = $user->employee;
        if (! $employee || ! $employee->departments->contains('name', self::FREIGHT_DEPARTMENT)) {
            return false;
        }

        return in_array('Admin', $roles, true)
            || $employee->ranks->contains('name', 'Management');
    }

    /**
     * Records the decision and emails the job's creator. Approving a draft job
     * moves it to 'confirmed' (the first operational status).
     */
    public function authorize(FreightJob $job, string $decision, ?string $comments, int $userId): FreightJob
    {
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException("Unknown authorization decision '{$decision}'.");
        }

        $job->authorization = $decision;
        $job->authorized_by_id = $userId;
        $job->authorization_date = now();
        $job->authorization_comments = $comments;

        if ($decision === 'approved' && $job->status === 'draft') {
            $job->status = 'confirmed';
        }

        $job->save();

        $this->notifyCreator($job);

        return $job;
    }

    /**
     * Puts a rejected job back in the queue after it has been edited, and
     * re-notifies the authorizers.
     */
    public function resubmit(FreightJob $job): void
    {
        if ($job->authorization !== 'rejected') {
            return;
        }

        $job->authorization = 'pending';
        $job->authorized_by_id = null;
        $job->authorization_date = null;
        $job->save();

        $this->notifyAuthorizers($job);
    }

    /** Emails every active "Freight Job Authorization" recipient (Notifications settings). */
    public function notifyAuthorizers(FreightJob $job): void
    {
        $company = $job->company;
        if (! $company) {
            return;
        }

        $recipients = Notification::where('when', 'before')
            ->where('category', self::NOTIFICATION_CATEGORY)
            ->where('status', 1)
            ->get();

        foreach ($recipients as $notification) {
            $email = $notification->email ?? $notification->employee?->email;
            if (! $email) {
                continue;
            }
            try {
                Mail::to($email)->send(new PendingNotificationEmails($company, $notification, $job));
            } catch (\Throwable $e) {
                Log::warning("Freight job {$job->job_number}: pending-authorization email to {$email} failed: " . $e->getMessage());
            }
        }
    }

    private function notifyCreator(FreightJob $job): void
    {
        $user = $job->user;
        $company = $job->company;
        if (! $user?->email || ! $company) {
            return;
        }

        try {
            Mail::to($user->email)->send(new AuthorizationNotificationMail($company, self::NOTIFICATION_CATEGORY, $user, $job));
        } catch (\Throwable $e) {
            Log::warning("Freight job {$job->job_number}: authorization email to {$user->email} failed: " . $e->getMessage());
        }
    }
}
