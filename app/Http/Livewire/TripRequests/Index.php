<?php

namespace App\Http\Livewire\TripRequests;

use App\Models\Customer;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Models\Vehicle;
use App\Services\Freight\ShipmentLegService;
use App\Services\GonyetiLink\GonyetiLinkService;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Transport instance inbox for road legs sent by the client's freight
 * forwarding instance. Create a trip for a request (or link one that already
 * exists), or reject it with a reason; the freight side is told either way.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $filterStatus = TripRequest::PENDING;

    public $customers;
    public $transporters;
    public $destinations;

    // Create Trip modal
    public $request_id;
    public $customer_id;
    public $transporter_id;
    public $horse_id;
    public $vehicle_id;
    public $driver_id;
    public $from;
    public $to;
    public $start_date;
    public $trip_status = 'Scheduled';
    public $horses = [];
    public $vehicles = [];
    public $drivers = [];

    // Link existing trip / Reject
    public $link_trip_number;
    public $rejection_reason;

    public function mount()
    {
        $this->customers = Customer::orderBy('name')->get(['id', 'name']);
        $this->transporters = Transporter::orderBy('name')->get(['id', 'name', 'default']);
        $this->destinations = Destination::with('country')->orderBy('city')->get();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }

    private function findRequest($id): TripRequest
    {
        return TripRequest::where('company_id', IntegrationGate::companyIdForUser())->findOrFail($id);
    }

    // ---------------------------------------------------------------
    // Create Trip
    // ---------------------------------------------------------------

    public function openCreate($id)
    {
        $request = $this->findRequest($id);
        $this->resetErrorBag();
        $this->request_id = $request->id;

        // The freight company is normally a customer here.
        $this->customer_id = $request->source_company
            ? Customer::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($request->source_company))])->value('id')
            : null;
        $this->transporter_id = Transporter::where('default', true)->value('id');
        $this->horse_id = $this->vehicle_id = $this->driver_id = null;
        $this->from = $request->origin_id;
        $this->to = $request->destination_id;
        $this->start_date = optional($request->planned_departure)->format('Y-m-d');
        $this->trip_status = 'Scheduled';
        $this->updatedTransporterId($this->transporter_id);

        $this->dispatchBrowserEvent('show-tripRequestCreateModal');
    }

    public function updatedTransporterId($id)
    {
        if (!$id) {
            $this->horses = $this->vehicles = $this->drivers = [];
            return;
        }

        // Same fleet filters as "Dispatch via Own Fleet".
        $this->horses = Horse::selectable()->where('transporter_id', $id)
            ->where('status', 1)->where('service', 0)->where('archive', 0)->orderByIdentifier('asc')->get();
        $this->vehicles = Vehicle::selectable()->where('transporter_id', $id)
            ->where('status', 1)->where('service', 0)->where('archive', 0)->orderByIdentifier('asc')->get();
        $this->drivers = Driver::selectable()->with('employee:id,name,surname')
            ->where('transporter_id', $id)->where('archive', 0)->get();
    }

    public function createTrip(ShipmentLegService $trips, GonyetiLinkService $link)
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'transporter_id' => 'required|exists:transporters,id',
            'driver_id' => 'required',
            'from' => 'required|integer|exists:destinations,id',
            'to' => 'required|integer|exists:destinations,id',
            'start_date' => 'nullable|date',
        ], ['customer_id.required' => 'Select the customer to bill.', 'from.required' => 'Select where the trip starts.', 'to.required' => 'Select where the trip ends.']);

        $request = $this->findRequest($this->request_id);
        if (!$request->isPending()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This request is no longer pending.']);
            return;
        }

        $company = Auth::user()->employee->company ?? Auth::user()->company;
        $trip = $trips->createOperationalTrip([
            'company_id' => $company?->id,
            'customer_id' => $this->customer_id,
            'currency_id' => $company?->currency_id,
            'transporter_id' => $this->transporter_id,
            'horse_id' => $this->horse_id ?: null,
            'vehicle_id' => $this->vehicle_id ?: null,
            'driver_id' => $this->driver_id,
            'from' => $this->from,
            'to' => $this->to,
            'cargo_details' => $this->cargoDetails($request),
            'start_date' => $this->start_date ?: null,
            'trip_status' => $this->trip_status ?: 'Scheduled',
        ]);

        // Keep the destinations the team picked for later updates of this request.
        $request->forceFill(['origin_id' => $this->from, 'destination_id' => $this->to])->save();
        $link->tripLinked($request, $trip);

        $this->dispatchBrowserEvent('hide-tripRequestCreateModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "Trip {$trip->trip_number} created and sent back to " . ($request->source_company ?: 'the freight instance') . '.']);
        $this->reset(['request_id', 'horse_id', 'vehicle_id', 'driver_id']);
    }

    private function cargoDetails(TripRequest $request): string
    {
        return trim(implode("\n", array_filter([
            $request->cargo_description,
            $request->containers ? 'Containers: ' . $request->containers : null,
            'Ref: ' . implode(' / ', array_filter([$request->request_number, $request->job_number, $request->shipment_number])),
        ])));
    }

    // ---------------------------------------------------------------
    // Link an existing trip
    // ---------------------------------------------------------------

    public function openLink($id)
    {
        $this->request_id = $this->findRequest($id)->id;
        $this->link_trip_number = null;
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-tripRequestLinkModal');
    }

    public function linkTrip(GonyetiLinkService $link)
    {
        $this->validate(['link_trip_number' => 'required|string']);

        $trip = Trip::where('trip_number', trim($this->link_trip_number))->first();
        if (!$trip) {
            $this->addError('link_trip_number', 'No trip with that number.');
            return;
        }
        if (TripRequest::where('trip_id', $trip->id)->where('id', '!=', $this->request_id)->where('status', TripRequest::TRIP_CREATED)->exists()) {
            $this->addError('link_trip_number', 'That trip is already linked to another request.');
            return;
        }

        $request = $this->findRequest($this->request_id);
        $link->tripLinked($request, $trip);

        $this->dispatchBrowserEvent('hide-tripRequestLinkModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "Trip {$trip->trip_number} linked to {$request->request_number}."]);
    }

    // ---------------------------------------------------------------
    // Reject
    // ---------------------------------------------------------------

    public function openReject($id)
    {
        $this->request_id = $this->findRequest($id)->id;
        $this->rejection_reason = null;
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-tripRequestRejectModal');
    }

    public function reject(GonyetiLinkService $link)
    {
        $this->validate(['rejection_reason' => 'required|string|max:1000'], ['rejection_reason.required' => 'Give the freight team a reason.']);

        $request = $this->findRequest($this->request_id);
        if (!$request->isPending()) {
            return;
        }
        $link->rejectRequest($request, $this->rejection_reason);

        $this->dispatchBrowserEvent('hide-tripRequestRejectModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "{$request->request_number} rejected; the freight team has been told."]);
    }

    /** Clears the "changed after the trip was created" flag once the trip has been checked. */
    public function acknowledgeChange($id)
    {
        $this->findRequest($id)->forceFill(['changed_since_trip' => false])->save();
    }

    public function retryDelivery(GonyetiLinkService $link)
    {
        $integration = $link->integrationFor(IntegrationGate::companyIdForUser(), GonyetiLinkService::ROLE_TRANSPORT);
        if (!$integration) {
            return;
        }
        $result = $link->deliver($integration);
        $this->dispatchBrowserEvent('alert', [
            'type' => $result['error'] ? 'error' : 'success',
            'message' => $result['error'] ? 'Still not delivered: ' . $result['error'] : "Delivered {$result['sent']} waiting message(s).",
        ]);
    }

    public function render(GonyetiLinkService $link)
    {
        $search = trim((string) $this->search);
        $companyId = IntegrationGate::companyIdForUser();

        $requests = TripRequest::query()
            ->with(['trip:id,trip_number,trip_status', 'origin.country', 'destination.country', 'responder:id,name,surname'])
            ->where('company_id', $companyId)
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($search !== '', function ($q) use ($search) {
                $like = "%{$search}%";
                $q->where(fn ($w) => $w->where('request_number', 'like', $like)->orWhere('job_number', 'like', $like)
                    ->orWhere('shipment_number', 'like', $like)->orWhere('job_customer', 'like', $like)
                    ->orWhere('origin_text', 'like', $like)->orWhere('destination_text', 'like', $like)
                    ->orWhere('containers', 'like', $like)->orWhere('cargo_description', 'like', $like));
            })
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderByDesc('planned_departure')
            ->orderByDesc('id')
            ->paginate(15);

        $integration = $link->integrationFor($companyId, GonyetiLinkService::ROLE_TRANSPORT);
        $waiting = $integration
            ? \App\Models\GonyetiLinkMessage::where('company_integration_id', $integration->id)->where('direction', 'out')->where('status', 'failed')->count()
            : 0;

        return view('livewire.trip-requests.index', [
            'requests' => $requests,
            'statuses' => TripRequest::STATUSES,
            'counts' => TripRequest::where('company_id', $companyId)->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status'),
            'waiting' => $waiting,
            'tripStatuses' => ['Scheduled', 'Started', 'Loading Point', 'Loaded', 'InTransit', 'Offloading Point', 'Offloaded', 'OnHold'],
        ]);
    }
}
