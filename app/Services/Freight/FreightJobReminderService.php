<?php

namespace App\Services\Freight;

use App\Mail\FreightJobClientReminderMail;
use App\Mail\FreightJobReminderMail;
use App\Models\Company;
use App\Models\FreightJobReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails custom freight job follow-ups (FreightJobReminder) on the days the
 * user picked before the due date, plus daily once overdue when asked.
 * Runs with the freight reminders digest (freight:send-reminders, started by
 * the SendFreightReminders middleware hourly - no cron). Each remind day is
 * claimed under a row lock before sending, so overlapping runs never send
 * the same email twice; a failed send releases the claim for the next run.
 */
class FreightJobReminderService
{
    /** Job statuses after which follow-ups stop emailing. */
    public const STOPPED_JOB_STATUSES = ['closed', 'cancelled'];

    public function sendDue(?int $companyId = null): int
    {
        $sent = 0;

        $ids = FreightJobReminder::open()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->whereHas('freight_job', fn ($q) => $q->whereNull('deleted_at')->whereNotIn('status', self::STOPPED_JOB_STATUSES))
            ->whereDate('due_at', '<=', Carbon::today()->addDays(max(array_keys(FreightJobReminder::REMIND_OPTIONS))))
            ->pluck('id');

        foreach ($ids as $id) {
            try {
                $sent += $this->sendIfDue($id);
            } catch (\Throwable $e) {
                Log::warning("Freight job reminder {$id} failed: " . $e->getMessage());
            }
        }

        return $sent;
    }

    /** Sends reminder $id if a remind day (or the daily overdue nag) is due. Returns 1 when emailed. */
    private function sendIfDue(int $id): int
    {
        $claimed = DB::transaction(function () use ($id) {
            $reminder = FreightJobReminder::whereKey($id)->open()->lockForUpdate()->first();
            if (! $reminder) {
                return null;
            }

            $days = $reminder->dueRemindDays();
            $overdueNag = ! $days
                && $reminder->daily_when_overdue
                && $reminder->daysLeft() < 0
                && ! ($reminder->last_overdue_sent_on && $reminder->last_overdue_sent_on->isToday());

            if (! $days && ! $overdueNag) {
                return null;
            }

            $before = ['sent_days' => $reminder->sent_days, 'last_overdue_sent_on' => $reminder->last_overdue_sent_on, 'last_sent_at' => $reminder->last_sent_at];

            $reminder->sent_days = array_values(array_unique(array_merge(array_map('intval', (array) $reminder->sent_days), $days)));
            if ($overdueNag || $reminder->daysLeft() < 0) {
                $reminder->last_overdue_sent_on = Carbon::today();
            }
            $reminder->last_sent_at = now();
            $reminder->save();

            return [$reminder, $before];
        });

        if (! $claimed) {
            return 0;
        }

        [$reminder, $before] = $claimed;

        if (! $this->send($reminder)) {
            $reminder->forceFill($before)->save(); // retry on the next run
            return 0;
        }

        return 1;
    }

    /**
     * Emails staff (internal version, with a link to the job) and, when
     * ticked, the client's contacts + extra addresses (courtesy version, no
     * internal link, replies to the reminder's creator). True if the internal
     * email - or, with no staff, at least one external one - went out.
     */
    public function send(FreightJobReminder $reminder): bool
    {
        $reminder->loadMissing('freight_job.customer', 'user');
        $job = $reminder->freight_job;
        $company = Company::find($reminder->company_id ?? $job?->company_id) ?? $reminder->user?->employee?->company;
        if (! $job || ! $company) {
            return false;
        }

        $staffEmails = $reminder->staffUsers()
            ->filter(fn ($u) => filter_var($u->email, FILTER_VALIDATE_EMAIL))
            ->mapWithKeys(fn ($u) => [strtolower($u->email) => trim($u->name . ' ' . $u->surname)])
            ->all();

        $ok = false;

        if ($staffEmails) {
            try {
                Mail::to(array_keys($staffEmails))->send(new FreightJobReminderMail($company, $reminder));
                $ok = true;
            } catch (\Throwable $e) {
                Log::warning("Freight job reminder {$reminder->id} staff email failed: " . $e->getMessage());
                return false;
            }
        }

        $external = $reminder->extraEmailList();
        if ($reminder->notify_client) {
            $external = $reminder->clientEmails() + $external;
        }
        // Never send the courtesy version to someone already on the staff email.
        $external = array_diff_key($external, $staffEmails);

        foreach ($external as $email => $name) {
            try {
                Mail::to($email)->send(new FreightJobClientReminderMail($company, $reminder, $name === $email ? null : $name));
                $ok = true;
            } catch (\Throwable $e) {
                Log::warning("Freight job reminder {$reminder->id} email to {$email} failed: " . $e->getMessage());
            }
        }

        return $ok;
    }

    /** Everyone the reminder emails, for the form/list preview: ['staff' => [...], 'external' => [...]]. */
    public function recipients(FreightJobReminder $reminder): array
    {
        $staff = $reminder->staffUsers()->map(fn ($u) => trim($u->name . ' ' . $u->surname))->filter()->values()->all();
        $external = $reminder->extraEmailList();
        if ($reminder->notify_client) {
            $external = $reminder->clientEmails() + $external;
        }

        return ['staff' => $staff, 'external' => array_keys($external)];
    }
}
