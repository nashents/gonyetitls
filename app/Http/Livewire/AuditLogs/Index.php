<?php

namespace App\Http\Livewire\AuditLogs;

use App\Exports\AuditLogsExport;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use OwenIt\Auditing\Models\Audit;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

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
        if (in_array($field, ['search', 'from', 'to', 'selectedUser', 'selectedEvent', 'selectedModel'])) {
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

    public function render()
    {
        $search = trim($this->search ?? '');

        $audits = Audit::query()
            ->with('user')
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
            ->paginate(15);

        return view('livewire.audit-logs.index', [
            'audits' => $audits,
        ]);
    }
}
