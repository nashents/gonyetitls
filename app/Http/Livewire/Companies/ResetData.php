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

    public string $confirmText = '';
    public bool $done = false;
    public array $results = [];

    public function mount()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $this->loadPreview();
    }

    public function loadPreview()
    {
        $this->refreshPreview();
        $this->done = false;
        $this->results = [];
        $this->confirmText = '';
    }

    protected function refreshPreview()
    {
        $report = app(CompanyDataResetService::class)->preview();

        $this->preview = $report['groups'];
        $this->total = $report['total'];
        $this->unclassified = $report['unclassified'];
        $this->balances = $report['balances'];
    }

    public function runReset(CompanyDataResetService $service)
    {
        abort_unless(Auth::user()->is_admin(), 403);

        if ($this->confirmText !== 'RESET') {
            $this->addError('confirmText', 'Type RESET (all caps) to confirm.');

            return;
        }

        if ($this->total === 0 && empty($this->balances)) {
            return;
        }

        $this->results = $service->execute();
        $this->done = true;
        $this->confirmText = '';

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Data reset complete. All captured transactional data has been wiped.',
        ]);

        $this->refreshPreview();
    }

    public function render()
    {
        return view('livewire.companies.reset-data');
    }
}
