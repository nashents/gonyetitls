<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Exports\FreightJobsExport;
use App\Http\Livewire\Freight\Jobs\Concerns\AuthorizesFreightJobs;
use App\Models\Customer;
use App\Models\FreightJob;
use App\Models\FreightServiceType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithPagination, AuthorizesFreightJobs;

    protected $paginationTheme = 'bootstrap';

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-custom';
    }

    public $perPage = 10;
    public $search;
    public $filter_status;
    public $filter_freight_service_type_id;
    public $filter_customer_id;
    public $filter_authorization;

    /** Set on the Pending / Approved / Rejected pages: the list is fixed to it. */
    public $lockedAuthorization;

    protected $queryString = [
        'search',
        'filter_authorization' => ['as' => 'authorization', 'except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    public $employee;
    public $department_names = [];
    public $role_names = [];

    public $customers;
    public $freight_service_types;

    public function mount($authorization = null)
    {
        $this->lockedAuthorization = in_array($authorization, ['pending', 'approved', 'rejected'], true) ? $authorization : null;

        $user = Auth::user();
        $this->employee = $user->employee;

        // System logins (e.g. a Super Admin without a staff record) have no employee.
        foreach (($this->employee?->departments ?? []) as $department) {
            $this->department_names[] = $department->name;
        }
        foreach (($user->roles ?? []) as $role) {
            $this->role_names[] = $role->name;
        }

        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->freight_service_types = FreightServiceType::orderBy('name', 'asc')->get();
    }

    /**
     * Only draft jobs with no recorded costs/charges can be deleted - once a
     * job has moved past draft or has financial lines attached, cancel/close
     * it via Edit instead of deleting it.
     */
    public function delete($id)
    {
        $job = FreightJob::findOrFail($id);

        if ($job->status !== 'draft') {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Only draft freight jobs can be deleted. Cancel or close this job instead.']);
            return;
        }

        if ($job->costs()->exists() || $job->charges()->exists()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This freight job has costs or charges recorded and cannot be deleted.']);
            return;
        }

        foreach ($job->shipments as $shipment) {
            $shipment->cargo_items()->delete();
            $shipment->parties()->delete();
            $shipment->delete();
        }
        $job->delete();

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight job deleted.']);
    }

    /** The on-screen filters (FreightJob::scopeListFilter keys); exports use the same. */
    private function listFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->filter_status,
            'authorization' => $this->lockedAuthorization ?: $this->filter_authorization,
            'freight_service_type_id' => $this->filter_freight_service_type_id,
            'customer_id' => $this->filter_customer_id,
        ];
    }

    private function exportFileName(string $extension): string
    {
        return 'freight_jobs' . ($this->lockedAuthorization ? '_' . $this->lockedAuthorization : '') . '_' . time() . '.' . $extension;
    }

    public function exportFreightJobsExcel(Excel $excel)
    {
        return $excel->download(new FreightJobsExport($this->listFilters()), $this->exportFileName('xlsx'));
    }

    public function exportFreightJobsCSV(Excel $excel)
    {
        return $excel->download(new FreightJobsExport($this->listFilters()), $this->exportFileName('csv'), Excel::CSV);
    }

    public function exportFreightJobsPDF(Excel $excel)
    {
        return $excel->download(new FreightJobsExport($this->listFilters()), $this->exportFileName('pdf'), Excel::DOMPDF);
    }

    public function render()
    {
        // NOTE: deliberately NOT using ->latest()->limit(1) inside this eager
        // load - in this Laravel version that limit applies once across the
        // whole combined query, not per parent job, so every job after the
        // first ends up with zero shipments loaded. Loading the full (small,
        // 1-2 rows per job in practice) collection and taking the latest in
        // PHP avoids that.
        $jobs = FreightJob::query()
            // Costing column: actual_* are kept on the job; accrued (unverified) cost is summed here.
            ->withSum(['costs as accrued_cost' => fn ($q) => $q->whereIn('verification_status', \App\Services\Freight\FreightCostingService::COST_ACCRUED_STATUSES)], 'exchange_amount')
            ->with(['currency:id,symbol,name', 'customer:id,name', 'freight_service_type:id,name', 'fromDestination.country', 'toDestination.country', 'documents', 'shipments' => function ($q) {
                $q->with([
                    'transport_documents',
                    'customs_declarations',
                    'containers.shipping_line',
                    'containers.exposures',
                    'parties',
                ]);
            }])
            ->listFilter($this->listFilters());

        $jobs = $jobs->paginate($this->perPage);
        $this->pageIds = $this->authorizableStatuses()
            ? $jobs->getCollection()->whereIn('authorization', $this->authorizableStatuses())->pluck('id')->all()
            : [];

        return view('livewire.freight.jobs.index', [
            'jobs' => $jobs,
            'pendingAuthorizationCount' => FreightJob::where('authorization', 'pending')->count(),
        ]);
    }

    /**
     * System-wide authorization rule (as Trips): only the Pending and
     * Rejected lists authorize. Manage (all jobs) and Approved never do.
     */
    protected function authorizableStatuses(): array
    {
        return in_array($this->lockedAuthorization, ['pending', 'rejected'], true) ? [$this->lockedAuthorization] : [];
    }

    /** Checkboxes, Bulk Actions and the row Authorization action. */
    public function getShowAuthorizeControlsProperty(): bool
    {
        return $this->canAuthorize && (bool) $this->authorizableStatuses();
    }

    /** New jobs are created from Manage Freight Jobs only, never the authorization lists. */
    public function getCanCreateHereProperty(): bool
    {
        return $this->lockedAuthorization === null;
    }
}
