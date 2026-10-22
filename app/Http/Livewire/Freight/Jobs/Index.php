<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Http\Livewire\Freight\Jobs\Concerns\AuthorizesFreightJobs;
use App\Models\Customer;
use App\Models\FreightJob;
use App\Models\FreightServiceType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesFreightJobs;

    protected $paginationTheme = 'bootstrap';

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-custom';
    }

    public $perPage = 15;
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
        'perPage' => ['except' => 15],
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

        foreach ($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
        foreach ($user->roles as $role) {
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

    public function render()
    {
        // NOTE: deliberately NOT using ->latest()->limit(1) inside this eager
        // load - in this Laravel version that limit applies once across the
        // whole combined query, not per parent job, so every job after the
        // first ends up with zero shipments loaded. Loading the full (small,
        // 1-2 rows per job in practice) collection and taking the latest in
        // PHP avoids that.
        $jobs = FreightJob::query()
            ->with(['customer:id,name', 'freight_service_type:id,name', 'fromDestination.country', 'toDestination.country', 'documents', 'shipments' => function ($q) {
                $q->with([
                    'transport_documents',
                    'customs_declarations',
                    'containers.shipping_line',
                    'containers.exposures',
                    'parties',
                ]);
            }]);

        if (filled($this->search)) {
            $search = trim($this->search);
            $jobs->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                    ->orWhere('customer_reference', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('fromDestination', fn ($q2) => $q2->where('city', 'like', "%{$search}%"))
                    ->orWhereHas('toDestination', fn ($q2) => $q2->where('city', 'like', "%{$search}%"));
            });
        }

        if (filled($this->filter_status)) {
            $jobs->where('status', $this->filter_status);
        }

        $authorization = $this->lockedAuthorization ?: $this->filter_authorization;
        if (filled($authorization)) {
            $jobs->where('authorization', $authorization);
        }

        if (filled($this->filter_freight_service_type_id)) {
            $jobs->where('freight_service_type_id', $this->filter_freight_service_type_id);
        }

        if (filled($this->filter_customer_id)) {
            $jobs->where('customer_id', $this->filter_customer_id);
        }

        // Approved / Rejected lists show the latest decisions first.
        if (in_array($this->lockedAuthorization, ['approved', 'rejected'], true)) {
            $jobs->orderByDesc('authorization_date');
        }
        $jobs->latest();

        $jobs = $jobs->paginate($this->perPage);
        $this->pageIds = $jobs->getCollection()->where('authorization', 'pending')->pluck('id')->all();

        return view('livewire.freight.jobs.index', [
            'jobs' => $jobs,
            'pendingAuthorizationCount' => FreightJob::where('authorization', 'pending')->count(),
        ]);
    }
}
