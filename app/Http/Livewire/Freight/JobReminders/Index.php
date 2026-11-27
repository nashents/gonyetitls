<?php

namespace App\Http\Livewire\Freight\JobReminders;

use App\Exports\FreightJobRemindersExport;
use App\Http\Livewire\Freight\Concerns\FreightPagination;
use App\Models\FreightJobReminder;
use App\Services\Freight\FreightJobReminderService;
use App\Services\Freight\FreightReminderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Maatwebsite\Excel\Excel;

/**
 * Freight > Job Reminders: every job's custom follow-ups in one list
 * (set on each job's Reminders tab). Visibility follows the freight bell.
 */
class Index extends Component
{
    use FreightPagination;

    public $search;
    public $status = 'open';
    public $mine = false;
    public $from;
    public $to;

    public function updating($field)
    {
        if (in_array($field, ['search', 'status', 'mine', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->resetPage();
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'mine' => $this->mine ? Auth::id() : null,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }

    private function find($id): FreightJobReminder
    {
        return FreightJobReminder::visibleTo(Auth::user())->findOrFail($id);
    }

    public function markDone($id)
    {
        $reminder = $this->find($id);
        $reminder->update(['status' => 'done', 'completed_at' => now(), 'completed_by' => Auth::id()]);
        $this->flushBell($reminder);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "\"{$reminder->title}\" marked as done. No further emails."]);
    }

    public function reopen($id)
    {
        $reminder = $this->find($id);
        $reminder->update(['status' => 'open', 'completed_at' => null, 'completed_by' => null]);
        $this->flushBell($reminder);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "\"{$reminder->title}\" reopened."]);
    }

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

    private function flushBell(FreightJobReminder $reminder): void
    {
        \App\Services\Freight\FreightReminderService::flushCache();
    }

    private function exportName(string $ext): string
    {
        return 'job_reminders_' . $this->status . '_' . time() . '.' . $ext;
    }

    public function exportExcel(Excel $excel)
    {
        return $excel->download(new FreightJobRemindersExport($this->filters()), $this->exportName('xlsx'));
    }

    public function exportCSV(Excel $excel)
    {
        return $excel->download(new FreightJobRemindersExport($this->filters()), $this->exportName('csv'), Excel::CSV);
    }

    public function exportPDF(Excel $excel)
    {
        return $excel->download(new FreightJobRemindersExport($this->filters()), $this->exportName('pdf'), Excel::DOMPDF);
    }

    public function render(FreightJobReminderService $service)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $base = fn () => FreightJobReminder::visibleTo($user)
            ->open()
            ->whereHas('freight_job', fn ($j) => $j->whereNull('deleted_at'))
            ->when($this->mine, fn ($q) => $q->involving((int) Auth::id()));

        $counts = [
            'overdue' => $base()->where('due_at', '<', $today)->count(),
            'today' => $base()->whereDate('due_at', $today)->count(),
            'week' => $base()->whereBetween('due_at', [$today, $today->copy()->addDays(7)->endOfDay()])->count(),
            'open' => $base()->count(),
        ];

        $rows = FreightJobReminder::with(['freight_job.customer:id,name', 'user:id,name,surname', 'completer:id,name,surname'])
            ->visibleTo($user)
            ->listFilter($this->filters())
            ->paginate(self::$freightPerPage);

        return view('livewire.freight.job-reminders.index', [
            'rows' => $rows,
            'counts' => $counts,
            'service' => $service,
            'seesAll' => $user ? FreightReminderService::seesAllJobs($user) : true,
        ]);
    }
}
