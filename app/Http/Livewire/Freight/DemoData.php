<?php

namespace App\Http\Livewire\Freight;

use App\Models\DemoDataBatch;
use App\Services\DemoData\DemoDataRollbackBlocked;
use App\Services\Freight\FreightDemoDataService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Freight > Demo Data (is_admin only): load sample freight jobs into the
 * admin's company so a new client can explore the module, then roll them
 * back exactly (FreightDemoDataService / DemoDataRecorder).
 */
class DemoData extends Component
{
    /** Rollback preview: ['delete' => [...], 'keep' => [...], 'blockers' => [...]] */
    public $plan;

    public function mount()
    {
        $this->authorizeAdmin();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->is_admin, 403, 'Only system administrators can manage demo data.');
    }

    private function companyId(): ?int
    {
        return FreightDemoDataService::companyIdFor(Auth::user());
    }

    public function load(FreightDemoDataService $service)
    {
        $this->authorizeAdmin();

        try {
            $batch = $service->load(Auth::user());
        } catch (\Throwable $e) {
            report($e);
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Demo data was not loaded: ' . $e->getMessage()]);
            return;
        }

        $this->plan = null;
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Demo data loaded: ' . ($batch->summary['freight_jobs'] ?? 0) . ' freight jobs with shipments, containers, customs, costs and charges.',
        ]);
    }

    /** Shows what a rollback would delete / keep / why it can't run yet. */
    public function preview(FreightDemoDataService $service)
    {
        $this->authorizeAdmin();
        $batch = $this->activeBatch($service);
        if (!$batch) {
            return;
        }

        $plan = $service->plan($batch);
        $this->plan = [
            'delete' => array_map('count', $plan['delete']),
            'keep' => array_map('count', $plan['keep']),
            'blockers' => $plan['blockers'],
        ];
    }

    public function rollback(FreightDemoDataService $service)
    {
        $this->authorizeAdmin();
        $batch = $this->activeBatch($service);
        if (!$batch) {
            return;
        }

        try {
            $plan = $service->rollback($batch, Auth::user());
        } catch (DemoDataRollbackBlocked $e) {
            $this->plan = ['delete' => [], 'keep' => [], 'blockers' => $e->reasons];
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Rollback stopped - see the reasons on the page.']);
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Rollback failed, nothing was deleted: ' . $e->getMessage()]);
            return;
        }

        $this->plan = null;
        $kept = array_sum(array_map('count', $plan['keep']));
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Demo data removed (' . array_sum(array_map('count', $plan['delete'])) . ' records).' . ($kept ? " {$kept} demo record(s) were kept because real data uses them." : ''),
        ]);
    }

    public function cancelPreview()
    {
        $this->plan = null;
    }

    private function activeBatch(FreightDemoDataService $service): ?DemoDataBatch
    {
        $companyId = $this->companyId();

        return $companyId ? $service->activeBatch($companyId) : null;
    }

    /** Friendly label for a table name in the summaries. */
    public static function label(string $table): string
    {
        return ucwords(str_replace('_', ' ', $table));
    }

    public function render(FreightDemoDataService $service)
    {
        $companyId = $this->companyId();

        return view('livewire.freight.demo-data', [
            'companyId' => $companyId,
            'company' => Auth::user()->employee?->company,
            'active' => $companyId ? $service->activeBatch($companyId) : null,
            'history' => $companyId
                ? DemoDataBatch::with(['user', 'rolledBackBy'])->where('module', FreightDemoDataService::MODULE)->where('company_id', $companyId)->latest('id')->limit(10)->get()
                : collect(),
            'scenarios' => FreightDemoDataService::SCENARIOS,
        ]);
    }
}
