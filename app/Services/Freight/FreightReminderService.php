<?php

namespace App\Services\Freight;

use App\Mail\FreightRemindersDigestMail;
use App\Models\Company;
use App\Models\ContainerChargeExposure;
use App\Models\FreightJobReminder;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentMilestone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Due / overdue freight operations, computed live from the job data:
 *  - shipment arrivals (ETA with no actual arrival)
 *  - shipment departures (ETD with no actual departure)
 *  - milestones planned for today or earlier and not completed
 *  - container storage / demurrage free time running out or already accruing
 *
 * The navbar bell shows these per user (forUser) and a daily digest email goes
 * to each job's creator / operations officer / clearing officer plus everyone
 * set up under the "Freight Reminders" notification category (sendDailyDigests).
 */
class FreightReminderService
{
    public const NOTIFICATION_CATEGORY = 'Freight Reminders';

    /** Days ahead to warn about upcoming arrivals / free time ending. */
    public const LOOKAHEAD_DAYS = 3;

    /** Job statuses that no longer need operational reminders. */
    public const FINISHED_STATUSES = ['delivered', 'invoiced', 'closed', 'cancelled'];

    /**
     * Reminders visible to $user, most urgent first. Super Admins and the
     * Freight Forwarding & Clearing department see every job; anyone else
     * sees the jobs they created or are an officer on. Empty unless the
     * user's company is tagged as a freight forwarder.
     */
    public function forUser(User $user): Collection
    {
        // Company-specific (multi-tenancy safe): only the user's own company's
        // jobs, never another company's - even for a person picked on a
        // follow-up there. Staff without an email get follow-ups here.
        $company = $user->employee?->company ?? $user->company;
        if (! $company || ! $company->hasCompanyType(FreightJobAuthorizationService::FREIGHT_DEPARTMENT)) {
            return collect();
        }

        $seeAll = self::seesAllJobs($user);

        $all = Cache::remember('freight-reminders:' . $company->id . ':' . Carbon::today()->toDateString(), now()->addMinutes(2), fn () => $this->collect($company->id)->all());

        return collect($all)
            ->filter(fn ($r) => (int) $r['company_id'] === (int) $company->id)
            ->when(! $seeAll, fn ($items) => $items->filter(fn ($r) => in_array($user->id, $r['user_ids'], true)))
            ->values();
    }

    /** Super Admins and the Freight Forwarding & Clearing department see every job's reminders. */
    public static function seesAllJobs(User $user): bool
    {
        return $user->roles->contains('name', 'Super Admin')
            || ($user->employee && $user->employee->departments->contains('name', FreightJobAuthorizationService::FREIGHT_DEPARTMENT));
    }

    /** Every open reminder for a company (null = all companies). */
    public function collect(?int $companyId = null): Collection
    {
        $today = Carbon::today();
        $horizon = $today->copy()->addDays(self::LOOKAHEAD_DAYS)->endOfDay();

        $openJob = function ($q) use ($companyId) {
            $q->whereNotIn('status', self::FINISHED_STATUSES)
                ->where('authorization', '!=', 'rejected')
                ->when($companyId, fn ($qq) => $qq->where('company_id', $companyId));
        };

        $reminders = collect();

        // Arrivals
        Shipment::query()
            ->with('freight_job')
            ->whereHas('freight_job', $openJob)
            ->whereNull('actual_arrival')
            ->whereNotNull('eta')
            ->where('eta', '<=', $horizon)
            ->get()
            ->each(function (Shipment $shipment) use ($today, $reminders) {
                $days = $today->diffInDays($shipment->eta->copy()->startOfDay(), false);
                $text = $days < 0
                    ? abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' overdue (ETA ' . $shipment->eta->format('d M') . ')'
                    : ($days === 0 ? 'arriving today' : 'arriving in ' . $days . ' day' . ($days === 1 ? '' : 's') . ' (' . $shipment->eta->format('d M') . ')');
                $reminders->push($this->item('arrival', $shipment->freight_job, $days, 'fa-ship', "Shipment {$shipment->shipment_number} {$text}", "arrival:{$shipment->id}"));
            });

        // Departures (only while the shipment hasn't left or arrived, and its
        // ETA hasn't passed - after that the arrival reminder says it all)
        Shipment::query()
            ->with('freight_job')
            ->whereHas('freight_job', $openJob)
            ->whereNull('actual_departure')
            ->whereNull('actual_arrival')
            ->where(fn ($q) => $q->whereNull('eta')->orWhere('eta', '>=', $today))
            ->whereNotNull('etd')
            ->where('etd', '<=', $today->copy()->addDay()->endOfDay())
            ->get()
            ->each(function (Shipment $shipment) use ($today, $reminders) {
                $days = $today->diffInDays($shipment->etd->copy()->startOfDay(), false);
                $text = $days < 0
                    ? 'departure ' . abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' overdue (ETD ' . $shipment->etd->format('d M') . ')'
                    : ($days === 0 ? 'departing today' : 'departing tomorrow');
                $reminders->push($this->item('departure', $shipment->freight_job, $days, 'fa-plane', "Shipment {$shipment->shipment_number} {$text}", "departure:{$shipment->id}"));
            });

        // Milestones due
        ShipmentMilestone::query()
            ->with('shipment.freight_job')
            ->whereHas('shipment.freight_job', $openJob)
            ->where('status', '!=', 'completed')
            ->whereNull('actual_at')
            ->whereNotNull('planned_at')
            ->where('planned_at', '<=', $today->copy()->endOfDay())
            ->get()
            ->each(function (ShipmentMilestone $milestone) use ($today, $reminders) {
                $days = $today->diffInDays(Carbon::parse($milestone->planned_at)->startOfDay(), false);
                $text = $days < 0 ? abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' overdue' : 'due today';
                $reminders->push($this->item('milestone', $milestone->shipment->freight_job, $days, 'fa-flag', "{$milestone->milestone_name} ({$milestone->shipment->shipment_number}) {$text}", "milestone:{$milestone->id}"));
            });

        $chargeLabels = ContainerChargeExposure::chargeTypes(true);

        // Container free time
        ContainerChargeExposure::query()
            ->with(['shipping_container', 'shipment.freight_job'])
            ->whereHas('shipment.freight_job', $openJob)
            ->whereNull('stop_date')
            ->whereNotNull('last_free_day')
            ->whereDate('last_free_day', '<=', $horizon)
            ->get()
            ->each(function (ContainerChargeExposure $exposure) use ($today, $reminders, $chargeLabels) {
                $days = $today->diffInDays($exposure->last_free_day->copy()->startOfDay(), false);
                $charge = in_array($exposure->charge_type, ['port_storage', 'detention'], true)
                    ? 'storage'
                    : mb_strtolower($chargeLabels[$exposure->charge_type] ?? $exposure->charge_type);
                $container = $exposure->shipping_container?->container_number ?: 'container';
                $text = $days < 0
                    ? "{$charge} accruing for " . abs($days) . ' day' . (abs($days) === 1 ? '' : 's')
                    : ($days === 0 ? "{$charge} free time ends today" : "{$charge} free time ends in {$days} day" . ($days === 1 ? '' : 's'));
                $reminders->push($this->item('free_time', $exposure->shipment->freight_job, $days, 'fa-cube', "Container {$container} {$text}", "free_time:{$exposure->id}"));
            });

        $reminders = $reminders->merge($this->followUpItems($companyId));

        return $reminders->sortBy([['days', 'asc'], ['job_number', 'asc']])->values();
    }

    public const FOLLOW_UPS_CACHE_PREFIX = 'freight-followups:';

    /** Clears the cached bell lists after a follow-up is added / changed. */
    public static function flushCache(?int $companyId = null): void
    {
        $today = Carbon::today()->toDateString();
        Cache::forget(self::FOLLOW_UPS_CACHE_PREFIX . $today);
        foreach (array_filter([$companyId]) ?: Company::pluck('id')->all() as $id) {
            Cache::forget('freight-reminders:' . $id . ':' . $today);
        }
    }

    /**
     * Custom follow-ups (job Reminders tab): from the first remind day (or the
     * lookahead) until marked done. Shown even for delivered jobs - the user
     * set them on purpose - but not closed/cancelled ones. $companyId null =
     * every company (a picked person sees theirs whatever company the job is in).
     */
    public function followUpItems(?int $companyId = null): Collection
    {
        $today = Carbon::today();
        $horizon = $today->copy()->addDays(self::LOOKAHEAD_DAYS)->endOfDay();

        return FreightJobReminder::query()
            ->open()
            ->with('freight_job')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->whereHas('freight_job', fn ($q) => $q->whereNull('deleted_at')->whereNotIn('status', FreightJobReminderService::STOPPED_JOB_STATUSES))
            ->whereDate('due_at', '<=', $today->copy()->addDays(max(array_keys(FreightJobReminder::REMIND_OPTIONS))))
            ->get()
            ->filter(function (FreightJobReminder $r) use ($horizon, $today) {
                $firstRemind = $r->due_at->copy()->startOfDay()->subDays((int) collect((array) $r->remind_days)->max());
                return $r->due_at->lte($horizon) || $firstRemind->lte($today);
            })
            ->map(function (FreightJobReminder $r) {
                $days = $r->daysLeft();
                $text = $days < 0
                    ? abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' overdue'
                    : ($days === 0 ? 'due today' : 'due in ' . $days . ' day' . ($days === 1 ? '' : 's') . ' (' . $r->due_at->format('d M') . ')');
                $item = $this->item('follow_up', $r->freight_job, $days, 'fa-bell', "Follow-up: {$r->title} {$text}", "follow_up:{$r->id}");
                $item['url'] .= '#reminders';
                $item['user_ids'] = array_values(array_unique(array_merge($item['user_ids'], array_map('intval', array_filter((array) $r->staff_user_ids)), array_filter([(int) $r->user_id]))));
                return $item;
            })
            ->values();
    }

    private function item(string $type, $job, int $days, string $icon, string $message, string $key): array
    {
        return [
            'key' => $key,
            'type' => $type,
            'days' => $days,
            'severity' => $days < 0 ? 'overdue' : ($days === 0 ? 'today' : 'upcoming'),
            'icon' => $icon,
            'message' => $message,
            'job_id' => $job->id,
            'job_number' => $job->job_number,
            'company_id' => $job->company_id,
            'url' => route('freight.jobs.show', $job->id),
            'user_ids' => array_values(array_unique(array_filter([
                $job->user_id, $job->operations_officer_id, $job->clearing_officer_id, $job->salesperson_id,
            ]))),
        ];
    }

    /**
     * Emails each recipient one digest per day. Safe to call repeatedly (the
     * middleware does, hourly): the unique (email, sent_on) row claimed before
     * sending means nobody is emailed twice on the same day.
     */
    public function sendDailyDigests(?int $companyId = null): int
    {
        // Company-specific: one company at a time, so nobody's digest mixes companies.
        if (! $companyId) {
            return Company::pluck('id')->sum(fn ($id) => $this->sendDailyDigests((int) $id));
        }

        $reminders = $this->collect($companyId);
        if ($reminders->isEmpty()) {
            return 0;
        }

        // email => [reminder key => reminder]
        $byEmail = [];

        // Only people employed by this company (an officer/staff id left over
        // from another company never receives this company's jobs).
        $users = User::whereIn('id', $reminders->pluck('user_ids')->flatten()->unique())
            ->whereHas('employee', fn ($e) => $e->where('company_id', $companyId))
            ->get()->keyBy('id');
        foreach ($reminders as $reminder) {
            foreach ($reminder['user_ids'] as $userId) {
                $email = $users->get($userId)?->email;
                if ($email) {
                    $byEmail[strtolower($email)][$reminder['key']] = $reminder;
                }
            }
        }

        // "Freight Reminders" watchers: only those belonging to this company
        // (via their employee, else the user who set the watcher up).
        $watchers = Notification::where('category', self::NOTIFICATION_CATEGORY)->where('status', 1)->with(['employee', 'user.employee'])->get()
            ->filter(fn ($w) => (int) ($w->employee?->company_id ?? $w->user?->employee?->company_id) === $companyId);
        foreach ($watchers as $watcher) {
            $email = $watcher->email ?? $watcher->employee?->email;
            if ($email) {
                foreach ($reminders as $reminder) {
                    $byEmail[strtolower($email)][$reminder['key']] = $reminder;
                }
            }
        }

        $sent = 0;
        $today = Carbon::today()->toDateString();
        foreach ($byEmail as $email => $items) {
            $items = collect($items)->sortBy([['days', 'asc'], ['job_number', 'asc']])->values();
            $company = Company::find($items->first()['company_id'] ?? $companyId);
            if (! $company?->noreply) {
                continue;
            }

            try {
                DB::table('freight_reminder_digests')->insert([
                    'email' => $email,
                    'sent_on' => $today,
                    'reminder_count' => $items->count(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (QueryException $e) {
                continue; // already sent today
            }

            try {
                Mail::to($email)->send(new FreightRemindersDigestMail($company, $items->all()));
                $sent++;
            } catch (\Throwable $e) {
                // Release the claim so the next hourly run retries this recipient.
                DB::table('freight_reminder_digests')->where('email', $email)->where('sent_on', $today)->delete();
                Log::warning("Freight reminders digest to {$email} failed: " . $e->getMessage());
            }
        }

        return $sent;
    }
}
