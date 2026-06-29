<?php

namespace App\Http\Livewire\PayrollRuns;

use App\Models\PayrollRun;
use App\Models\Payroll;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public PayrollRun $run;
    public $employeeSearch = '';

    public $confirmAction  = '';
    public $confirmReason  = '';

    public function mount(PayrollRun $run)
    {
        $this->authorize('view', $run);
        $this->run = $run->load(['frequency', 'currency', 'createdBy', 'approvedBy', 'lockedBy', 'postedBy']);
    }

    // ── Lifecycle helpers ─────────────────────────────────────────────────

    public function confirmLifecycle(string $action)
    {
        $this->confirmAction = $action;
        $this->confirmReason = '';
        $this->dispatchBrowserEvent('show-confirm-modal');
    }

    public function executeLifecycle()
    {
        $now = now();

        match ($this->confirmAction) {
            'approve' => $this->doApprove($now),
            'lock'    => $this->doLock($now),
            'post'    => $this->doPost($now),
            'reverse' => $this->doReverse($now),
            default   => null,
        };

        $this->run->refresh();
        $this->dispatchBrowserEvent('hide-confirm-modal');
    }

    private function doApprove($now): void
    {
        $this->authorize('update', $this->run);
        $this->run->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => $now]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run approved.']);
    }

    private function doLock($now): void
    {
        $this->authorize('update', $this->run);
        $this->run->update(['status' => 'locked', 'locked_by' => Auth::id(), 'locked_at' => $now]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run locked.']);
    }

    private function doPost($now): void
    {
        $this->authorize('update', $this->run);
        $this->run->update(['status' => 'posted', 'posted_by' => Auth::id(), 'posted_at' => $now]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run posted to GL.']);
    }

    private function doReverse($now): void
    {
        $this->authorize('update', $this->run);
        if (empty($this->confirmReason)) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'A reason is required for reversal.']);
            return;
        }
        $this->run->update([
            'status'      => 'reversed',
            'reversed_by' => Auth::id(),
            'reversed_at' => $now,
            'notes'       => $this->confirmReason,
        ]);
        $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => 'Payroll run reversed.']);
    }

    public function render()
    {
        // Payroll salary lines linked to this run via payrolls → payroll_salaries
        $salaryLines = \App\Models\PayrollSalary::whereHas('payroll', fn($q) => $q->where('payroll_run_id', $this->run->id))
            ->when($this->employeeSearch, fn($q) => $q->whereHas('employee', fn($eq) =>
                $eq->where('name', 'like', "%{$this->employeeSearch}%")
                   ->orWhere('surname', 'like', "%{$this->employeeSearch}%")
            ))
            ->with('employee', 'payroll')
            ->orderBy('employee_id')
            ->paginate(20);

        return view('livewire.payroll-runs.show', compact('salaryLines'));
    }
}
