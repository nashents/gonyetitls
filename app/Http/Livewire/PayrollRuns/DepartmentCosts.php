<?php

namespace App\Http\Livewire\PayrollRuns;

use App\Exports\PayrollDepartmentCostsExport;
use App\Models\Currency;
use App\Models\Department;
use App\Models\PayrollRun;
use App\Services\Payroll\PayrollDepartmentCostService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Excel;

class DepartmentCosts extends Component
{
    public $company;

    public $dateFrom;
    public $dateTo;
    public $includeDrafts = false;
    public $payrollRunId = '';
    public $currencyId = '';
    public $departmentId = '';

    // "{currency_id}:{department_key}" of the department rows opened for employee detail
    public $expanded = [];

    protected $queryString = [
        'dateFrom'      => ['except' => ''],
        'dateTo'        => ['except' => ''],
        'payrollRunId'  => ['except' => ''],
        'currencyId'    => ['except' => ''],
        'departmentId'  => ['except' => ''],
        'includeDrafts' => ['except' => false],
    ];

    public function mount()
    {
        $this->authorize('viewAny', PayrollRun::class);

        $this->company = Auth::user()->employee?->company;
        if (! $this->company) {
            abort(422, 'Your user account is not linked to an employee record with a company, so payroll reports are unavailable.');
        }

        $this->dateFrom = $this->dateFrom ?: now()->startOfMonth()->toDateString();
        $this->dateTo   = $this->dateTo ?: now()->endOfMonth()->toDateString();
    }

    public function thisMonth()
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->endOfMonth()->toDateString();
        $this->payrollRunId = '';
    }

    public function lastMonth()
    {
        $this->dateFrom = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $this->dateTo   = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $this->payrollRunId = '';
    }

    public function toggle($key)
    {
        $this->expanded = in_array($key, $this->expanded, true)
            ? array_values(array_diff($this->expanded, [$key]))
            : array_merge($this->expanded, [$key]);
    }

    public function export(Excel $excel)
    {
        $this->authorize('viewAny', PayrollRun::class);

        return $excel->download(
            new PayrollDepartmentCostsExport($this->filters(), $this->periodLabel()),
            'payroll_department_costs_' . time() . '.xlsx'
        );
    }

    private function filters(): array
    {
        return [
            'company_id'     => $this->company->id,
            'date_from'      => $this->dateFrom,
            'date_to'        => $this->dateTo,
            'include_drafts' => (bool) $this->includeDrafts,
            'payroll_run_id' => $this->payrollRunId ?: null,
            'currency_id'    => $this->currencyId ?: null,
            'department_id'  => $this->departmentId ?: null,
        ];
    }

    private function periodLabel(): string
    {
        if ($this->payrollRunId) {
            return 'Payroll run: ' . (PayrollRun::find($this->payrollRunId)?->name ?? '#' . $this->payrollRunId);
        }

        return \Carbon\Carbon::parse($this->dateFrom)->format('d M Y') . ' – ' . \Carbon\Carbon::parse($this->dateTo)->format('d M Y');
    }

    public function render()
    {
        $groups = app(PayrollDepartmentCostService::class)->report($this->filters());

        return view('livewire.payroll-runs.department-costs', [
            'groups'      => $groups,
            'periodLabel' => $this->periodLabel(),
            'runs'        => PayrollRun::where('company_id', $this->company->id)
                ->where('status', '!=', 'reversed')
                ->orderByDesc('period_start')->limit(60)->get(['id', 'name', 'status', 'period_start']),
            'currencies'  => Currency::orderBy('name')->get(['id', 'name', 'symbol']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
