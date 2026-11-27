<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A custom follow-up on a freight job ("Permit ready before discharge").
 * Sending: FreightJobReminderService. Bell / digest: FreightReminderService.
 */
class FreightJobReminder extends Model
{
    use \App\Models\Concerns\BelongsToCompany; // multi-tenancy (docs/multi-tenancy-plan.md)

    use SoftDeletes;

    /** Days-before choices offered on the form => label. */
    public const REMIND_OPTIONS = [
        14 => '14 days before',
        7 => '7 days before',
        3 => '3 days before',
        2 => '2 days before',
        1 => '1 day before',
        0 => 'On the due date',
    ];

    public const DEFAULT_REMIND_DAYS = [3, 1, 0];

    protected $fillable = [
        'company_id', 'freight_job_id', 'user_id', 'title', 'notes', 'client_message', 'due_at',
        'remind_days', 'sent_days', 'staff_user_ids', 'extra_emails', 'notify_client', 'daily_when_overdue',
        'last_overdue_sent_on', 'status', 'completed_at', 'completed_by', 'last_sent_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'last_overdue_sent_on' => 'date',
        'remind_days' => 'array',
        'sent_days' => 'array',
        'staff_user_ids' => 'array',
        'notify_client' => 'boolean',
        'daily_when_overdue' => 'boolean',
    ];

    public function freight_job()
    {
        return $this->belongsTo(FreightJob::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Same rule as the freight bell (FreightReminderService::seesAllJobs):
     * Super Admin / Freight dept see every reminder; anyone else only those
     * they set, are emailed on, or whose job they created / are an officer on.
     */
    public function scopeVisibleTo($query, ?User $user)
    {
        if (! $user) {
            return $query;
        }

        // Company-specific (multi-tenancy safe), even for Super Admins.
        $query->where('company_id', $user->employee?->company_id ?? $user->company?->id);

        if (\App\Services\Freight\FreightReminderService::seesAllJobs($user)) {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('user_id', $user->id)
            ->orWhereJsonContains('staff_user_ids', (int) $user->id)
            ->orWhereHas('freight_job', fn ($j) => $j->where(fn ($t) => $t->where('user_id', $user->id)
                ->orWhere('operations_officer_id', $user->id)
                ->orWhere('clearing_officer_id', $user->id)
                ->orWhere('salesperson_id', $user->id))));
    }

    /** Only reminders the given user set or is emailed on ("Mine"). */
    public function scopeInvolving($query, int $userId)
    {
        return $query->where(fn ($q) => $q->where('user_id', $userId)->orWhereJsonContains('staff_user_ids', $userId));
    }

    /**
     * Job Reminders page filters, shared with FreightJobRemindersExport.
     * Keys: search, status (open|overdue|today|week|done|all), mine (user id), from, to (due dates).
     */
    public function scopeListFilter($query, array $filters)
    {
        $today = Carbon::today();
        $status = $filters['status'] ?? 'open';

        $query->whereHas('freight_job', fn ($j) => $j->whereNull('deleted_at'));

        switch ($status) {
            case 'overdue':
                $query->open()->where('due_at', '<', $today);
                break;
            case 'today':
                $query->open()->whereDate('due_at', $today);
                break;
            case 'week':
                $query->open()->whereBetween('due_at', [$today, $today->copy()->addDays(7)->endOfDay()]);
                break;
            case 'done':
                $query->where('status', 'done');
                break;
            case 'all':
                break;
            default:
                $query->open();
        }

        if (filled($filters['search'] ?? null)) {
            $search = trim($filters['search']);
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhereHas('freight_job', fn ($j) => $j->where('job_number', 'like', "%{$search}%")
                    ->orWhere('customer_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))));
        }

        if (! empty($filters['mine'])) {
            $query->involving((int) $filters['mine']);
        }
        if (filled($filters['from'] ?? null)) {
            $query->whereDate('due_at', '>=', $filters['from']);
        }
        if (filled($filters['to'] ?? null)) {
            $query->whereDate('due_at', '<=', $filters['to']);
        }

        return $query->orderByRaw("status = 'done'")->orderBy('due_at')->orderBy('id');
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    /** Days until due (negative = overdue), by calendar date. */
    public function daysLeft(): int
    {
        return Carbon::today()->diffInDays($this->due_at->copy()->startOfDay(), false);
    }

    /** Remind days whose send date has arrived and that haven't been emailed. */
    public function dueRemindDays(): array
    {
        $today = Carbon::today();
        $sent = array_map('intval', (array) $this->sent_days);

        return collect((array) $this->remind_days)
            ->map(fn ($d) => (int) $d)
            ->reject(fn ($d) => in_array($d, $sent, true))
            ->filter(fn ($d) => $this->due_at->copy()->startOfDay()->subDays($d)->lte($today))
            ->values()
            ->all();
    }

    /** Next date an email goes out, or null. */
    public function nextSendDate(): ?Carbon
    {
        if ($this->isDone()) {
            return null;
        }

        $sent = array_map('intval', (array) $this->sent_days);
        $dates = collect((array) $this->remind_days)
            ->map(fn ($d) => (int) $d)
            ->reject(fn ($d) => in_array($d, $sent, true))
            ->map(fn ($d) => $this->due_at->copy()->startOfDay()->subDays($d))
            ->sort()
            ->values();

        $next = $dates->first();
        if ($next && $next->lt(Carbon::today())) {
            return Carbon::today();
        }
        if (! $next && $this->daily_when_overdue && $this->daysLeft() < 0) {
            return $this->last_overdue_sent_on && $this->last_overdue_sent_on->isToday() ? Carbon::tomorrow() : Carbon::today();
        }

        return $next;
    }

    public function remindLabel(): string
    {
        $days = collect((array) $this->remind_days)->map(fn ($d) => (int) $d)->sortDesc()->values();
        if ($days->isEmpty()) {
            return 'No emails';
        }

        return $days->map(fn ($d) => $d === 0 ? 'on the day' : "{$d}d before")->implode(', ');
    }

    /**
     * Staff users to notify (creator always included) - only employees of
     * the reminder's company, so no other company's staff ever receive it.
     */
    public function staffUsers()
    {
        $ids = collect((array) $this->staff_user_ids)->push($this->user_id)->filter()->map(fn ($id) => (int) $id)->unique()->values();

        return User::whereIn('id', $ids)
            ->when($this->company_id, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $this->company_id)))
            ->get();
    }

    /** @return array<string,string> email => name, for the extra addresses typed on the form. */
    public function extraEmailList(): array
    {
        return self::parseEmails($this->extra_emails);
    }

    public static function parseEmails(?string $value): array
    {
        $out = [];
        foreach (preg_split('/[\s,;]+/', (string) $value) as $email) {
            $email = strtolower(trim($email));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out[$email] = $email;
            }
        }

        return $out;
    }

    /** @return array<string,string> the job customer's contact emails (email => name). */
    public function clientEmails(): array
    {
        $customerId = $this->freight_job?->customer_id;

        return $customerId ? Fitness::contactEmailsFor('Customer', $customerId) : [];
    }
}
