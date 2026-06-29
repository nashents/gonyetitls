<?php

namespace App\Http\Livewire\PayrollRuns;

use App\Models\Currency;
use App\Models\PayrollRun;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterStatus = '';
    protected $queryString = ['search', 'filterStatus'];

    // Create form
    public $showCreateModal = false;
    public $name;
    public $period_start;
    public $period_end;
    public $payroll_date;
    public $payment_date;
    public $selectedFrequency;
    public $selectedCurrency;
    public $proration_method = 'calendar_days';
    public $notes;

    // Support data
    public $frequencies;
    public $currencies;
    public $company;

    // Lifecycle action
    public $confirmRunId;
    public $confirmAction;
    public $confirmReason = '';

    public function mount()
    {
        $this->company     = Auth::user()->employee->company;
        $this->frequencies = \App\Models\PayrollFrequency::where('active', true)
            ->where(function ($q) { $q->whereNull('company_id')->orWhere('company_id', $this->company->id); })
            ->orderBy('name')->get();
        $this->currencies  = Currency::orderBy('name')->get();
        $this->selectedCurrency = $this->company->currency_id;
    }

    // ── Create ──────────────────────────────────────────────────────────────

    public function openCreateModal()
    {
        $this->reset(['name','period_start','period_end','payroll_date','payment_date','selectedFrequency','notes']);
        $this->selectedCurrency = $this->company->currency_id;
        $this->showCreateModal = true;
    }

    public function store()
    {
        $this->authorize('create', PayrollRun::class);

        $this->validate([
            'name'              => 'required|string|max:120',
            'period_start'      => 'required|date',
            'period_end'        => 'required|date|after_or_equal:period_start',
            'payroll_date'      => 'required|date',
            'selectedFrequency' => 'required|exists:payroll_frequencies,id',
            'selectedCurrency'  => 'required|exists:currencies,id',
        ]);

        PayrollRun::create([
            'company_id'           => $this->company->id,
            'payroll_frequency_id' => $this->selectedFrequency,
            'currency_id'          => $this->selectedCurrency,
            'name'                 => $this->name,
            'period_start'         => $this->period_start,
            'period_end'           => $this->period_end,
            'payroll_date'         => $this->payroll_date,
            'payment_date'         => $this->payment_date ?: $this->payroll_date,
            'proration_method'     => $this->proration_method,
            'notes'                => $this->notes,
            'status'               => 'draft',
            'created_by'           => Auth::id(),
        ]);

        $this->showCreateModal = false;
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run created.']);
    }

    // ── Lifecycle actions ────────────────────────────────────────────────────

    public function confirmLifecycle($runId, $action)
    {
        $this->confirmRunId  = $runId;
        $this->confirmAction = $action;
        $this->confirmReason = '';
        $this->dispatchBrowserEvent('show-confirm-modal');
    }

    public function executeLifecycle()
    {
        $run = PayrollRun::findOrFail($this->confirmRunId);
        $now = now();

        match ($this->confirmAction) {
            'approve' => $this->doApprove($run, $now),
            'lock'    => $this->doLock($run, $now),
            'post'    => $this->doPost($run, $now),
            'reverse' => $this->doReverse($run, $now),
            default   => null,
        };

        $this->reset(['confirmRunId', 'confirmAction', 'confirmReason']);
        $this->dispatchBrowserEvent('hide-confirm-modal');
    }

    private function doApprove(PayrollRun $run, $now): void
    {
        $this->authorize('update', $run);
        if (!in_array($run->status, ['draft', 'validated'])) abort(422, 'Cannot approve in current status.');
        $run->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => $now]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run approved.']);
    }

    private function doLock(PayrollRun $run, $now): void
    {
        $this->authorize('update', $run);
        if ($run->status !== 'approved') abort(422, 'Run must be approved before locking.');
        $run->update(['status' => 'locked', 'locked_by' => Auth::id(), 'locked_at' => $now]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run locked.']);
    }

    private function doPost(PayrollRun $run, $now): void
    {
        $this->authorize('update', $run);
        if (!in_array($run->status, ['locked', 'exported'])) abort(422, 'Run must be locked before posting.');
        $run->update(['status' => 'posted', 'posted_by' => Auth::id(), 'posted_at' => $now]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run posted to GL.']);
    }

    private function doReverse(PayrollRun $run, $now): void
    {
        $this->authorize('update', $run);
        if (!$run->canBeReversed()) abort(422, 'Run cannot be reversed in its current status.');
        $run->update(['status' => 'reversed', 'reversed_by' => Auth::id(), 'reversed_at' => $now, 'notes' => $this->confirmReason]);
        $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => 'Payroll run reversed.']);
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $runs = PayrollRun::where('company_id', $this->company->id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->with(['frequency', 'currency', 'createdBy', 'approvedBy'])
            ->orderByDesc('period_start')
            ->paginate(15);

        return view('livewire.payroll-runs.index', compact('runs'));
    }
}
