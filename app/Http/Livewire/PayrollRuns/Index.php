<?php

namespace App\Http\Livewire\PayrollRuns;

use App\Models\Currency;
use App\Models\PayrollCompanyConfig;
use App\Models\PayrollRun;
use App\Models\Salary;
use App\Services\Payroll\PayrollBatchService;
use App\Services\Payroll\PayrollRunLifecycleService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterStatus = '';
    protected $queryString = ['search', 'filterStatus'];

    // Create form
   
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
    public $confirmAction = '';
    public $confirmReason = '';

    public function mount()
    {
        $this->company = Auth::user()->employee?->company;
        if (!$this->company) {
            abort(422, 'Your user account is not linked to an employee record with a company, so payroll runs are unavailable.');
        }
        $this->frequencies = \App\Models\PayrollFrequency::where('active', true)
            ->where(function ($q) { $q->whereNull('company_id')->orWhere('company_id', $this->company->id); })
            ->orderBy('name')->get();
        $this->selectedFrequency = $this->frequencies->first()?->id;
        $this->currencies  = Currency::orderBy('name')->get();
        $this->selectedCurrency = $this->company->currency_id;

        $config = PayrollCompanyConfig::where('company_id', $this->company->id)->where('active', true)->latest()->first();
        $this->proration_method = $config->proration_method ?? 'calendar_days';
    }

    // ── Create ──────────────────────────────────────────────────────────────

    public function openCreateModal()
    {
        $this->reset(['name','period_start','period_end','payroll_date','payment_date','notes']);
        $this->selectedCurrency = $this->company->currency_id;
        $this->selectedFrequency = $this->frequencies->first()?->id;

        $this->dispatchBrowserEvent('show-createRunModal');
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

        $run = PayrollRun::create([
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

        $salaries = Salary::with(['employee', 'salary_items'])
            ->where('status', 1)
            ->whereHas('employee', fn ($q) => $q->where('company_id', $this->company->id))
            ->get();

        app(PayrollBatchService::class)->buildBatch($run, $salaries);

        
        $this->dispatchBrowserEvent('hide-createRunModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll run created with ' . $salaries->count() . ' employee(s).']);
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
        $this->authorize('update', $run);

        $service = app(PayrollRunLifecycleService::class);
        $messages = [
            'approve' => 'Payroll run approved.',
            'lock'    => 'Payroll run locked.',
            'post'    => 'Payroll run posted to GL.',
            'reverse' => 'Payroll run reversed.',
        ];

        match ($this->confirmAction) {
            'approve' => $service->approve($run),
            'lock'    => $service->lock($run),
            'post'    => $service->post($run),
            'reverse' => $service->reverse($run, $this->confirmReason),
            default   => null,
        };

        if (isset($messages[$this->confirmAction])) {
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $messages[$this->confirmAction]]);
        }

        $this->reset(['confirmRunId', 'confirmAction', 'confirmReason']);
        $this->dispatchBrowserEvent('hide-confirm-modal');
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
