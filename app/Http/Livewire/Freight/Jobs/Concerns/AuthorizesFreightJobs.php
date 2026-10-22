<?php

namespace App\Http\Livewire\Freight\Jobs\Concerns;

use App\Models\FreightJob;
use App\Services\Freight\FreightJobAuthorizationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Single + bulk approve/reject for freight jobs, shared by the jobs list and
 * the job page. Uses the app-wide #authorizationModal / #bulkyAuthorizationModal
 * events (layouts/app.blade.php). Components call afterFreightJobAuthorized().
 */
trait AuthorizesFreightJobs
{
    public $authorizing_job_id;
    public $authorizing_job_number;
    public $authorize;
    public $comments;

    public $selectedRows = [];
    public $selectPageRows = false;
    public array $pageIds = [];
    public $bulk_authorize;
    public $bulk_comments;

    public function getCanAuthorizeProperty(): bool
    {
        return app(FreightJobAuthorizationService::class)->canAuthorize(Auth::user());
    }

    public function authorizeJob($id)
    {
        if (! $this->canAuthorize) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'You are not allowed to authorize freight jobs.']);
            return;
        }

        $job = FreightJob::findOrFail($id);
        $this->authorizing_job_id = $job->id;
        $this->authorizing_job_number = $job->job_number;
        $this->authorize = null;
        $this->comments = null;
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-authorizationModal');
    }

    public function saveAuthorization()
    {
        if (! $this->canAuthorize) {
            $this->addError('authorize', 'You are not allowed to authorize freight jobs.');
            return;
        }
        if (! in_array($this->authorize, ['approved', 'rejected'], true)) {
            $this->addError('authorize', 'Please select a decision.');
            return;
        }
        if ($this->authorize === 'rejected' && blank($this->comments)) {
            $this->addError('comments', 'Please give a reason for rejecting this job.');
            return;
        }

        $job = FreightJob::findOrFail($this->authorizing_job_id);
        app(FreightJobAuthorizationService::class)->authorize($job, $this->authorize, $this->comments, Auth::id());

        $this->dispatchBrowserEvent('hide-authorizationModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Freight job {$job->job_number} " . ($this->authorize === 'approved' ? 'approved.' : 'rejected.'),
        ]);

        $this->afterFreightJobAuthorized();
    }

    public function updatedSelectPageRows($value)
    {
        $this->selectedRows = $value ? array_map('strval', $this->pageIds) : [];
    }

    public function showBulkAuthorize()
    {
        $this->bulk_authorize = null;
        $this->bulk_comments = null;
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
    }

    public function bulkAuthorize()
    {
        if (! $this->canAuthorize) {
            $this->addError('bulk_authorize', 'You are not allowed to authorize freight jobs.');
            return;
        }
        if (! in_array($this->bulk_authorize, ['approved', 'rejected'], true)) {
            $this->addError('bulk_authorize', 'Please select a decision.');
            return;
        }

        $service = app(FreightJobAuthorizationService::class);
        $jobs = FreightJob::whereIn('id', $this->selectedRows)->where('authorization', 'pending')->orderBy('id')->get();

        $done = 0;
        $skipped = [];
        foreach ($jobs as $job) {
            try {
                $service->authorize($job, $this->bulk_authorize, $this->bulk_comments, Auth::id());
                $done++;
            } catch (\Throwable $e) {
                Log::error("Bulk freight job authorization failed for {$job->job_number}: " . $e->getMessage());
                $skipped[] = "{$job->job_number}: {$e->getMessage()}";
            }
        }

        $this->reset(['selectedRows', 'selectPageRows']);
        $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');

        $verb = $this->bulk_authorize === 'approved' ? 'approved' : 'rejected';
        if ($skipped) {
            session()->flash('warning', count($skipped) . ' freight job(s) not ' . $verb . ': ' . implode(' | ', $skipped));
        }
        $this->dispatchBrowserEvent('alert', [
            'type' => $skipped ? 'warning' : 'success',
            'message' => "{$done} freight job(s) {$verb}" . ($skipped ? ', ' . count($skipped) . ' skipped.' : '.'),
        ]);

        $this->afterFreightJobAuthorized();
    }

    protected function afterFreightJobAuthorized(): void
    {
    }
}
