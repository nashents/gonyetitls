<?php

namespace App\Http\Livewire\Companies;

use App\Services\CompanyDataResetService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResetData extends Component
{
    public array $preview = [];
    public int $total = 0;
    public array $unclassified = [];
    public array $balances = [];
    public array $partial = [];
    public array $links = [];
    public array $keepGroups = [];

    // "Truncate everything except employees" — also wipes master data so it
    // can be re-pulled from Sage. See CompanyDataResetService::$employeeOnlyKeep.
    public bool $employeesOnly = false;

    public string $confirmText = '';
    public bool $done = false;
    public array $results = [];

    public function mount()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $this->keepGroups = app(CompanyDataResetService::class)->employeeOnlyKeepGroups();
        $this->loadPreview();
    }

    public function loadPreview()
    {
        $this->refreshPreview();
        $this->done = false;
        $this->results = [];
        $this->confirmText = '';
    }

    public function updatedEmployeesOnly()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $this->resetErrorBag();
        $this->loadPreview();
    }

    public function getConfirmWordProperty(): string
    {
        return $this->employeesOnly ? 'RESET ALL' : 'RESET';
    }

    protected function refreshPreview()
    {
        $report = app(CompanyDataResetService::class)->preview($this->employeesOnly);

        $this->preview = $report['groups'];
        $this->total = $report['total'];
        $this->unclassified = $report['unclassified'];
        $this->balances = $report['balances'];
        $this->partial = $report['partial'];
        $this->links = $report['links'];
    }

    public function runReset(CompanyDataResetService $service)
    {
        abort_unless(Auth::user()->is_admin(), 403);

        if ($this->confirmText !== $this->confirmWord) {
            $this->addError('confirmText', "Type {$this->confirmWord} (all caps) to confirm.");

            return;
        }

        if ($this->total === 0 && empty($this->balances) && empty($this->links)) {
            return;
        }

        $this->results = $service->execute($this->employeesOnly);
        $this->done = true;
        $this->confirmText = '';

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $this->employeesOnly
                ? 'Reset complete. Everything except employees (and their logins) has been wiped — pull master data from Sage next.'
                : 'Data reset complete. All captured transactional data has been wiped.',
        ]);

        $this->refreshPreview();
    }

    public function render()
    {
        return view('livewire.companies.reset-data');
    }
}
