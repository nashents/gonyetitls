<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Models\FreightJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Deleted Freight Jobs with Restore - same pattern as Deleted Invoices / Bills
 * (confirm modal, then back to the list). A job can only be deleted while it
 * is a Draft with no costs or charges (Jobs\Index::delete), so restoring has
 * no ledger effects to reapply: the job comes back with the shipments,
 * cargo lines and parties that were removed with it.
 */
class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-custom';
    }

    public $search;
    public $perPage = 10;
    public $restoring_job_id;
    public $restoring_job_number;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function restore($id)
    {
        $job = FreightJob::onlyTrashed()->forCurrentCompany()->findOrFail($id);
        $this->restoring_job_id = $job->id;
        $this->restoring_job_number = $job->job_number;
        $this->dispatchBrowserEvent('show-freightJobRestoreModal');
    }

    public function update()
    {
        $job = FreightJob::onlyTrashed()->forCurrentCompany()->findOrFail($this->restoring_job_id);

        DB::transaction(function () use ($job) {
            // Children deleted together with the job (same request) - not ones removed earlier on purpose.
            $since = Carbon::parse($job->deleted_at)->subMinute();

            $job->restore();

            foreach ($job->shipments()->onlyTrashed()->where('deleted_at', '>=', $since)->get() as $shipment) {
                $shipment->restore();
                $shipment->cargo_items()->onlyTrashed()->where('deleted_at', '>=', $since)->restore();
                $shipment->parties()->onlyTrashed()->where('deleted_at', '>=', $since)->restore();
            }
        });

        $this->dispatchBrowserEvent('hide-freightJobRestoreModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Freight job {$job->job_number} restored.",
        ]);

        return redirect()->route('freight.jobs.show', $job->id);
    }

    public function render()
    {
        $jobs = FreightJob::onlyTrashed()->forCurrentCompany()
            ->with(['customer:id,name', 'freight_service_type:id,name', 'fromDestination.country', 'toDestination.country'])
            ->when(filled($this->search), function ($q) {
                $search = trim($this->search);
                $q->where(fn ($q) => $q->where('job_number', 'like', "%{$search}%")
                    ->orWhere('customer_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")));
            })
            ->orderByDesc('deleted_at')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.freight.jobs.deleted', ['jobs' => $jobs]);
    }
}
