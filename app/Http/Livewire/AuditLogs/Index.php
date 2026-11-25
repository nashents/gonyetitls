<?php

namespace App\Http\Livewire\AuditLogs;

use App\Exports\AuditLogsExport;
use App\Models\Attendance;
use App\Models\AttendanceRegister;
use App\Models\User;
use App\Services\Audit\AuditValueResolver;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use OwenIt\Auditing\Models\Audit;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search', 'attendance' => ['except' => ''], 'attendanceEmployee' => ['except' => '']];

    /** Investigate one attendance register: its own audits + every row captured on it. */
    public $attendance = '';
    public $attendanceEmployee = '';

    public $from;
    public $to;
    public $selectedUser;
    public $selectedEvent;
    public $selectedModel;

    public $users;
    public $models;
    public $events = ['created', 'updated', 'deleted', 'restored'];

    public $viewingAudit;

    public function mount()
    {
        $this->users = User::orderBy('name', 'asc')->orderBy('surname', 'asc')->get();
        $this->models = Audit::query()
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');
    }

    public function updating($field, $value)
    {
        if (in_array($field, ['search', 'from', 'to', 'selectedUser', 'selectedEvent', 'selectedModel', 'attendance', 'attendanceEmployee'])) {
            $this->resetPage();
        }
    }

    public function view($id)
    {
        $this->viewingAudit = Audit::with('user')->find($id);
        $this->dispatchBrowserEvent('show-auditViewModal');
    }

    public function resetFilters()
    {
        $this->search = null;
        $this->from = null;
        $this->to = null;
        $this->selectedUser = null;
        $this->selectedEvent = null;
        $this->selectedModel = null;
        $this->attendance = '';
        $this->attendanceEmployee = '';
        $this->resetPage();
    }

    public function exportAuditLogsCSV(Excel $excel)
    {
        return $excel->download(new AuditLogsExport($this->from, $this->to, $this->selectedUser, $this->selectedEvent, $this->selectedModel, $this->search), 'audit_logs_' . time() . '.csv', Excel::CSV);
    }

    public function exportAuditLogsExcel(Excel $excel)
    {
        return $excel->download(new AuditLogsExport($this->from, $this->to, $this->selectedUser, $this->selectedEvent, $this->selectedModel, $this->search), 'audit_logs_' . time() . '.xlsx');
    }

    /**
     * Ids of attendance_registers rows that belong to register $attendanceId,
     * plus "lost" rows: saved with no attendance_id (an old Edit bug dropped it)
     * by the same user within 2 minutes of an audit on this register.
     *
     * @return array{linked: int[], lost: int[]}
     */
    protected function attendanceRowIds(int $attendanceId, ?int $employeeId): array
    {
        $rows = AttendanceRegister::withTrashed()
            ->where('attendance_id', $attendanceId)
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->pluck('id')->all();

        // Moments this register was being worked on: its own audits and those
        // of every row linked to it (an edit may only touch rows).
        $allLinkedRows = AttendanceRegister::withTrashed()->where('attendance_id', $attendanceId)->pluck('id');
        $headerAudits = Audit::query()
            ->where(fn ($q) => $q->where('auditable_type', Attendance::class)->where('auditable_id', $attendanceId))
            ->orWhere(fn ($q) => $q->where('auditable_type', AttendanceRegister::class)->whereIn('auditable_id', $allLinkedRows->all() ?: [0]))
            ->get(['user_id', 'created_at']);

        $lost = [];
        if ($headerAudits->isNotEmpty()) {
            $orphanIds = AttendanceRegister::withTrashed()
                ->whereNull('attendance_id')
                ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
                ->pluck('id');

            if ($orphanIds->isNotEmpty()) {
                $lost = Audit::query()
                    ->where('auditable_type', AttendanceRegister::class)
                    ->whereIn('auditable_id', $orphanIds)
                    ->get(['auditable_id', 'user_id', 'created_at'])
                    ->filter(fn ($a) => $headerAudits->contains(fn ($h) => $h->user_id == $a->user_id
                        && $h->created_at && $a->created_at
                        && abs($h->created_at->diffInSeconds($a->created_at)) <= 120))
                    ->pluck('auditable_id')->unique()->values()->all();
            }
        }

        return ['linked' => $rows, 'lost' => $lost];
    }

    protected function applyAttendanceScope($query, array $ids): void
    {
        $attendanceId = (int) $this->attendance;
        $employeeId = filled($this->attendanceEmployee) ? (int) $this->attendanceEmployee : null;
        $rowIds = array_merge($ids['linked'], $ids['lost']);

        $query->where(function ($q) use ($attendanceId, $employeeId, $rowIds) {
            if (!$employeeId) {
                $q->where(fn ($h) => $h->where('auditable_type', Attendance::class)->where('auditable_id', $attendanceId));
            }
            // Rows on this register (also catches rows since deleted or moved).
            $q->orWhere(function ($r) use ($attendanceId, $employeeId, $rowIds) {
                $r->where('auditable_type', AttendanceRegister::class)
                    ->where(function ($w) use ($attendanceId, $rowIds) {
                        $w->whereIn('auditable_id', $rowIds ?: [0])
                            ->orWhere('new_values', 'regexp', '"attendance_id":"?' . $attendanceId . '"?[,}]')
                            ->orWhere('old_values', 'regexp', '"attendance_id":"?' . $attendanceId . '"?[,}]');
                    });
                if ($employeeId) {
                    $r->where(function ($w) use ($employeeId, $rowIds) {
                        $w->whereIn('auditable_id', $rowIds ?: [0])
                            ->orWhere('new_values', 'regexp', '"employee_id":"?' . $employeeId . '"?[,}]')
                            ->orWhere('old_values', 'regexp', '"employee_id":"?' . $employeeId . '"?[,}]');
                    });
                }
            });
        });
    }

    public function render()
    {
        $search = trim($this->search ?? '');

        $investigation = null;
        $attendanceIds = ['linked' => [], 'lost' => []];
        if (filled($this->attendance) && ($register = Attendance::withTrashed()->with('department')->find((int) $this->attendance))) {
            $attendanceIds = $this->attendanceRowIds($register->id, filled($this->attendanceEmployee) ? (int) $this->attendanceEmployee : null);
            $current = filled($this->attendanceEmployee)
                ? AttendanceRegister::where('attendance_id', $register->id)->where('employee_id', (int) $this->attendanceEmployee)->first()
                : null;
            $investigation = [
                'register' => $register,
                'employees' => $register->expectedEmployees()
                    ->merge(\App\Models\Employee::whereIn('id', AttendanceRegister::withTrashed()->where('attendance_id', $register->id)->pluck('employee_id'))->get())
                    ->unique('id')->sortBy(fn ($e) => strtolower($e->name . ' ' . $e->surname))->values(),
                'current' => $current,
                'lost' => $attendanceIds['lost'],
            ];
        }

        $audits = Audit::query()
            ->with('user')
            ->when($investigation, fn ($q) => $this->applyAttendanceScope($q, $attendanceIds))
            ->when(filled($this->from) && filled($this->to), function ($q) {
                $q->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
            })
            ->when(filled($this->from) && !filled($this->to), function ($q) {
                $q->where('created_at', '>=', $this->from . ' 00:00:00');
            })
            ->when(!filled($this->from) && filled($this->to), function ($q) {
                $q->where('created_at', '<=', $this->to . ' 23:59:59');
            })
            ->when(filled($this->selectedUser), function ($q) {
                $q->where('user_id', $this->selectedUser);
            })
            ->when(filled($this->selectedEvent), function ($q) {
                $q->where('event', $this->selectedEvent);
            })
            ->when(filled($this->selectedModel), function ($q) {
                $q->where('auditable_type', $this->selectedModel);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $like = "%{$search}%";
                    $qq->where('event', 'like', $like)
                        ->orWhere('auditable_type', 'like', $like)
                        ->orWhere('auditable_id', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('url', 'like', $like)
                        ->orWhereHas('user', function ($u) use ($like) {
                            $u->where('name', 'like', $like)
                                ->orWhere('surname', 'like', $like)
                                ->orWhereRaw("CONCAT(name,' ',surname) LIKE ?", [$like]);
                        });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        // Readable record labels ("12 (ATT00012)"), one query per model type.
        $resolver = app(AuditValueResolver::class);
        foreach ($audits->getCollection()->groupBy('auditable_type') as $type => $group) {
            $resolver->prime($type, $group->pluck('auditable_id')->all());
        }

        return view('livewire.audit-logs.index', [
            'audits' => $audits,
            'resolver' => $resolver,
            'investigation' => $investigation,
            'viewingChanges' => $this->viewingAudit ? $resolver->changes($this->viewingAudit) : [],
        ]);
    }
}
