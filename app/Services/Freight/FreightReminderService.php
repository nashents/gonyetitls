<?php

namespace App\Services\Freight;

use App\Mail\FreightRemindersDigestMail;
use App\Models\Company;
use App\Models\ContainerChargeExposure;
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
        $company = $user->employee?->company ?? $user->company;
        if (! $company || ! $company->hasCompanyType(FreightJobAuthorizationService::FREIGHT_DEPARTMENT)) {
            return collect();
        }

        $seeAll = $user->roles->contains('name', 'Super Admin')
            || ($user->employee && $user->employee->departments->contains('name', FreightJobAuthorizationService::FREIGHT_DEPARTMENT));

        $all = Cache::remember('freight-reminders:' . $company->id . ':' . Carbon::today()->toDateString(), now()->addMinutes(2), fn () => $this->collect($company->id)->all());

        return collect($all)
            ->when(! $seeAll, fn ($items) => $items->filter(fn ($r) => in_array($user->id, $r['user_ids'], true)))
            ->values();
    }

    /** Every open reminder for a company (null = all companies). */
    public function collect(?int $companyId = null): Collection
    {
        $today = Carbon::today();
        $horizon = $today->copy()->addDays(self::LOOKAHEAD_DAYS)->endOfDay();

        $openJob = function ($q) use ($companyId) {
            $q->whereNotIn('status', self::FINISHED_STATUSES)
                ->where('authorization', '!=', 'rejected')
                ->when($companyId, fn ($qq) => $qq->where(fn ($c) => $c->where('company_id', $companyId)->orWhereNull('company_id')));
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

        // Container free time
        ContainerChargeExposure::query()
            ->with(['shipping_container', 'shipment.freight_job'])
            ->whereHas('shipment.freight_job', $openJob)
            ->whereNull('stop_date')
            ->whereNotNull('last_free_day')
            ->whereDate('last_free_day', '<=', $horizon)
            ->get()
            ->each(function (ContainerChargeExposure $exposure) use ($today, $reminders) {
                $days = $today->diffInDays($exposure->last_free_day->copy()->startOfDay(), false);
                $charge = $exposure->charge_type === 'demurrage' ? 'demurrage' : 'storage';
                $container = $exposure->shipping_container?->container_number ?: 'container';
                $text = $days < 0
                    ? "{$charge} accruing for " . abs($days) . ' day' . (abs($days) === 1 ? '' : 's')
                    : ($days === 0 ? "{$charge} free time ends today" : "{$charge} free time ends in {$days} day" . ($days === 1 ? '' : 's'));
                $reminders->push($this->item('free_time', $exposure->shipment->freight_job, $days, 'fa-cube', "Container {$container} {$text}", "free_time:{$exposure->id}"));
            });

        return $reminders->sortBy([['days', 'asc'], ['job_number', 'asc']])->values();
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
        $reminders = $this->collect($companyId);
        if ($reminders->isEmpty()) {
            return 0;
        }

        // email => [reminder key => reminder]
        $byEmail = [];

        $users = User::whereIn('id', $reminders->pluck('user_ids')->flatten()->unique())->get()->keyBy('id');
        foreach ($reminders as $reminder) {
            foreach ($reminder['user_ids'] as $userId) {
                $email = $users->get($userId)?->email;
                if ($email) {
                    $byEmail[strtolower($email)][$reminder['key']] = $reminder;
                }
            }
        }

        $watchers = Notification::where('category', self::NOTIFICATION_CATEGORY)->where('status', 1)->with('employee')->get();
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
