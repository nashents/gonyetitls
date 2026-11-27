<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;
use App\Models\Employee;
use App\Models\FreightJob;
use App\Models\FreightJobReminder;
use App\Services\Freight\FreightJobReminderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Job > Reminders tab: custom follow-ups ("Permit ready before discharge")
 * emailed to staff and optionally the client on chosen days before the due
 * date (FreightJobReminderService), and listed in the freight bell / digest.
 */
class Reminders extends Component
{
    use FreightEmbeddedPagination;

    public $jobId;
    public $show_done = false;

    public $reminder_id;
    public $title;
    public $due_at;
    public $remind_days = [];
    public $staff_user_ids = [];
    public $notify_client = false;
    public $client_message;
    public $extra_emails;
    public $notes;
    public $daily_when_overdue = false;
    public $staff_search;

    public function mount($jobId)
    {
        $this->jobId = $jobId;
    }

    private function job(): FreightJob
    {
        return FreightJob::with(['customer', 'shipments'])->findOrFail($this->jobId);
    }

    /** Job team: creator, operations / clearing officers, salesperson. */
    private function jobTeamUserIds(FreightJob $job): array
    {
        return array_values(array_unique(array_filter([
            Auth::id(), $job->user_id, $job->operations_officer_id, $job->clearing_officer_id, $job->salesperson_id,
        ])));
    }

    /** The job's company (else the signed-in user's): reminders never cross companies. */
    private function companyId(FreightJob $job): ?int
    {
        return $job->company_id ?? Auth::user()->employee?->company_id;
    }

    /**
     * Active non-driver employees with a login in the job's company (any
     * department), plus anyone already picked from that company. Ticked
     * people are listed first; the search box narrows the rest.
     */
    private function staffOptions(FreightJob $job)
    {
        $keep = array_map('intval', array_filter((array) $this->staff_user_ids));
        $search = trim((string) $this->staff_search);

        return Employee::with('user:id,email')
            ->where('company_id', $this->companyId($job))
            ->whereNotNull('user_id')
            ->where(function ($q) use ($keep, $search) {
                $q->where(function ($s) use ($search) {
                    $s->activeStaff()->ofStaffType('admin'); // no drivers
                    if ($search !== '') {
                        $s->where(fn ($n) => $n->where(\Illuminate\Support\Facades\DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                    }
                });
                if ($keep) {
                    $q->orWhereIn('user_id', $keep);
                }
            })
            ->orderBy('name')
            ->orderBy('surname')
            ->get()
            ->sortBy(fn ($e) => in_array((int) $e->user_id, $keep, true) ? 0 : 1)
            ->values();
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'due_at' => 'required|date',
            'remind_days' => 'array',
            'remind_days.*' => ['integer', Rule::in(array_keys(FreightJobReminder::REMIND_OPTIONS))],
            'staff_user_ids' => 'array',
            'staff_user_ids.*' => ['integer', function ($attribute, $value, $fail) {
                // Only staff of the job's company can be put on its reminders.
                if (! Employee::where('user_id', $value)->where('company_id', $this->companyId($this->job()))->exists()) {
                    $fail("Only staff of this job's company can be picked.");
                }
            }],
            'notify_client' => 'boolean',
            'client_message' => 'nullable|string|max:2000',
            'extra_emails' => ['nullable', 'string', 'max:1000', function ($attribute, $value, $fail) {
                foreach (preg_split('/[\s,;]+/', (string) $value) as $email) {
                    if (trim($email) !== '' && ! filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                        $fail("\"{$email}\" is not a valid email address.");
                        return;
                    }
                }
            }],
            'notes' => 'nullable|string|max:2000',
        ];
    }

    protected $validationAttributes = [
        'due_at' => 'due date',
        'extra_emails' => 'other emails',
    ];

    public function create()
    {
        $this->resetForm();
        $job = $this->job();
        $this->remind_days = FreightJobReminder::DEFAULT_REMIND_DAYS;
        // Job team, limited to employees of the job's company.
        $team = Employee::where('company_id', $this->companyId($job))->whereIn('user_id', $this->jobTeamUserIds($job))->pluck('user_id')->all();
        $this->staff_user_ids = array_map('strval', $team);
        $this->notify_client = false;
        $this->dispatchBrowserEvent('show-jobReminderModal');
    }

    public function edit($id)
    {
        $reminder = $this->find($id);
        $this->resetErrorBag();
        $this->reminder_id = $reminder->id;
        $this->title = $reminder->title;
        $this->due_at = $reminder->due_at->format('Y-m-d\TH:i');
        $this->remind_days = array_map('strval', (array) $reminder->remind_days);
        $this->staff_user_ids = array_map('strval', (array) $reminder->staff_user_ids);
        $this->notify_client = (bool) $reminder->notify_client;
        $this->client_message = $reminder->client_message;
        $this->extra_emails = $reminder->extra_emails;
        $this->notes = $reminder->notes;
        $this->daily_when_overdue = (bool) $reminder->daily_when_overdue;
        $this->dispatchBrowserEvent('show-jobReminderModal');
    }

    /** Quick-fill the due date from the shipment's ETD / ETA. */
    public function useDate($which)
    {
        $shipment = $this->job()->shipments->first();
        $date = $which === 'etd' ? $shipment?->etd : $shipment?->eta;
        if ($date) {
            $this->due_at = $date->copy()->setTime(8, 0)->format('Y-m-d\TH:i');
        }
    }

    public function save()
    {
        $this->validate();
        $job = $this->job();

        $days = collect($this->remind_days)->map(fn ($d) => (int) $d)->unique()->sortDesc()->values()->all();
        $data = [
            'company_id' => $this->companyId($job),
            'freight_job_id' => $job->id,
            'title' => trim($this->title),
            'due_at' => Carbon::parse($this->due_at),
            'remind_days' => $days,
            'staff_user_ids' => collect($this->staff_user_ids)->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'notify_client' => (bool) $this->notify_client,
            'client_message' => $this->client_message ?: null,
            'extra_emails' => $this->extra_emails ? implode(', ', array_keys(FreightJobReminder::parseEmails($this->extra_emails))) : null,
            'notes' => $this->notes ?: null,
            'daily_when_overdue' => (bool) $this->daily_when_overdue,
        ];

        if ($this->reminder_id) {
            $reminder = $this->find($this->reminder_id);
            // A new due date or schedule starts the emails afresh.
            if (! $reminder->due_at->equalTo($data['due_at']) || $days !== array_map('intval', (array) $reminder->remind_days)) {
                $data['sent_days'] = [];
                $data['last_overdue_sent_on'] = null;
            }
            $reminder->update($data);
            $message = 'Reminder updated.';
        } else {
            $reminder = FreightJobReminder::create($data + ['user_id' => Auth::id(), 'status' => 'open', 'sent_days' => []]);
            $message = 'Reminder set.';
        }

        $next = $reminder->fresh()->nextSendDate();
        $message .= $next
            ? ' First email goes out ' . ($next->isToday() ? 'today' : 'on ' . $next->format('d M Y')) . '.'
            : ' No emails scheduled - it shows in the reminders bell.';

        $this->flushBell();
        $this->resetForm();
        $this->dispatchBrowserEvent('hide-jobReminderModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $message]);
    }

    public function markDone($id)
    {
        $reminder = $this->find($id);
        $reminder->update(['status' => 'done', 'completed_at' => now(), 'completed_by' => Auth::id()]);
        $this->flushBell();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "\"{$reminder->title}\" marked as done. No further emails."]);
    }

    public function reopen($id)
    {
        $reminder = $this->find($id);
        $reminder->update(['status' => 'open', 'completed_at' => null, 'completed_by' => null]);
        $this->flushBell();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "\"{$reminder->title}\" reopened."]);
    }

    /** Emails everyone on the reminder right now (doesn't change the schedule). */
    public function sendNow($id, FreightJobReminderService $service)
    {
        $reminder = $this->find($id);
        if ($reminder->isDone()) {
            return;
        }

        if ($service->send($reminder)) {
            $reminder->update(['last_sent_at' => now()]);
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Reminder emailed.']);
        } else {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Nothing was sent - check the recipients have valid email addresses and the company has a no-reply address.']);
        }
    }

    public function delete($id)
    {
        $reminder = $this->find($id);
        $reminder->delete();
        $this->flushBell();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Reminder deleted.']);
    }

    public function resetForm()
    {
        $this->reset(['reminder_id', 'title', 'due_at', 'remind_days', 'staff_user_ids', 'notify_client', 'client_message', 'extra_emails', 'notes', 'daily_when_overdue', 'staff_search']);
        $this->resetErrorBag();
    }

    private function find($id): FreightJobReminder
    {
        return FreightJobReminder::where('freight_job_id', $this->jobId)->findOrFail($id);
    }

    private function flushBell(): void
    {
        \App\Services\Freight\FreightReminderService::flushCache();
    }

    public function updatedShowDone()
    {
        $this->resetPage();
    }

    public function render(FreightJobReminderService $service)
    {
        $job = $this->job();

        $rows = FreightJobReminder::with(['user', 'completer'])
            ->where('freight_job_id', $job->id)
            ->when(! $this->show_done, fn ($q) => $q->open())
            ->orderByRaw("status = 'done'")
            ->orderBy('due_at')
            ->orderBy('id')
            ->paginate(10);

        $clientEmails = $job->customer_id ? \App\Models\Fitness::contactEmailsFor('Customer', $job->customer_id) : [];
        $shipment = $job->shipments->first();

        return view('livewire.freight.jobs.reminders', [
            'job' => $job,
            'rows' => $rows,
            'service' => $service,
            'staffOptions' => $this->staffOptions($job),
            'remindOptions' => FreightJobReminder::REMIND_OPTIONS,
            'clientEmails' => $clientEmails,
            'etd' => $shipment?->etd,
            'eta' => $shipment?->eta,
            'doneCount' => FreightJobReminder::where('freight_job_id', $job->id)->where('status', 'done')->count(),
        ]);
    }
}
