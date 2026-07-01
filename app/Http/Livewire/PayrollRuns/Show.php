<?php

namespace App\Http\Livewire\PayrollRuns;

use App\Models\PayrollRun;
use App\Services\Payroll\PayrollRunLifecycleService;
use Livewire\Component;
use Livewire\WithPagination;

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
        $this->authorize('update', $this->run);

        $service = app(PayrollRunLifecycleService::class);
        $messages = [
            'approve' => 'Payroll run approved.',
            'lock'    => 'Payroll run locked.',
            'post'    => 'Payroll run posted to GL.',
            'reverse' => 'Payroll run reversed.',
        ];

        match ($this->confirmAction) {
            'approve' => $service->approve($this->run),
            'lock'    => $service->lock($this->run),
            'post'    => $service->post($this->run),
            'reverse' => $service->reverse($this->run, $this->confirmReason),
            default   => null,
        };

        if (isset($messages[$this->confirmAction])) {
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $messages[$this->confirmAction]]);
        }

        $this->run->refresh();
        $this->dispatchBrowserEvent('hide-confirm-modal');
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
