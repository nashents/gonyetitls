<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\Driver;
use App\Models\Horse;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentLeg;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\Freight\ShipmentLegService;
use Livewire\Component;

class Legs extends Component
{
    public $shipment;
    public $vendors;
    public $locations;
    public $transporters;

    // Add/Edit Leg form
    public $editing_leg_id;
    public $transport_mode;
    public $carrier_vendor_id;
    public $carrier_name;
    public $carrier_reference;
    public $origin_location_id;
    public $destination_location_id;
    public $planned_departure;
    public $planned_arrival;
    public $estimated_departure;
    public $estimated_arrival;

    public $expanded_leg_id;
    public $ad_hoc_milestone_code;

    // Dispatch via Own Fleet form
    public $dispatching_leg_id;
    public $dispatch_transporter_id;
    public $dispatch_horse_id;
    public $dispatch_vehicle_id;
    public $dispatch_driver_id;
    public $dispatch_horses = [];
    public $dispatch_vehicles = [];
    public $dispatch_drivers = [];
    public $dispatch_from;
    public $dispatch_to;
    public $dispatch_status = 'Scheduled';
    public $dispatch_start_date;

    protected $listeners = ['refreshJob' => '$refresh'];

    public function mount($shipmentId)
    {
        $this->shipment = Shipment::findOrFail($shipmentId);
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->locations = Location::orderBy('name', 'asc')->get();
        $this->transporters = Transporter::orderBy('name', 'asc')->get();
        $this->refreshShipment();
    }

    private function refreshShipment()
    {
        $this->shipment = Shipment::with([
            'legs.carrier_vendor', 'legs.origin_location', 'legs.destination_location',
            'legs.trip', 'legs.milestones', 'freight_job',
        ])->findOrFail($this->shipment->id);
    }

    protected function rules()
    {
        return [
            'transport_mode' => 'nullable|string|max:255',
            'carrier_reference' => 'nullable|string|max:255',
        ];
    }

    public function edit($legId)
    {
        $leg = ShipmentLeg::findOrFail($legId);
        $this->editing_leg_id = $leg->id;
        $this->transport_mode = $leg->transport_mode;
        $this->carrier_vendor_id = $leg->carrier_vendor_id;
        $this->carrier_name = $leg->carrier_name;
        $this->carrier_reference = $leg->carrier_reference;
        $this->origin_location_id = $leg->origin_location_id;
        $this->destination_location_id = $leg->destination_location_id;
        $this->planned_departure = optional($leg->planned_departure)->format('Y-m-d\TH:i');
        $this->planned_arrival = optional($leg->planned_arrival)->format('Y-m-d\TH:i');
        $this->estimated_departure = optional($leg->estimated_departure)->format('Y-m-d\TH:i');
        $this->estimated_arrival = optional($leg->estimated_arrival)->format('Y-m-d\TH:i');

        $this->dispatchBrowserEvent('show-addLegModal-' . $this->shipment->id);
    }

    public function store(ShipmentLegService $service)
    {
        $this->validate();

        $data = [
            'shipment_id' => $this->shipment->id,
            'transport_mode' => $this->transport_mode ?: null,
            'carrier_vendor_id' => $this->carrier_vendor_id ?: null,
            'carrier_name' => $this->carrier_name,
            'carrier_reference' => $this->carrier_reference,
            'origin_location_id' => $this->origin_location_id ?: null,
            'destination_location_id' => $this->destination_location_id ?: null,
            'planned_departure' => $this->planned_departure ?: null,
            'planned_arrival' => $this->planned_arrival ?: null,
            'estimated_departure' => $this->estimated_departure ?: null,
            'estimated_arrival' => $this->estimated_arrival ?: null,
        ];

        if ($this->editing_leg_id) {
            $service->update(ShipmentLeg::findOrFail($this->editing_leg_id), $data);
        } else {
            $service->create($data);
        }

        $this->resetLegForm();
        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-addLegModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Leg saved.']);
    }

    private function resetLegForm()
    {
        $this->reset([
            'editing_leg_id', 'transport_mode', 'carrier_vendor_id', 'carrier_name', 'carrier_reference',
            'origin_location_id', 'destination_location_id', 'planned_departure', 'planned_arrival',
            'estimated_departure', 'estimated_arrival',
        ]);
    }

    public function delete($legId, ShipmentLegService $service)
    {
        $service->delete(ShipmentLeg::findOrFail($legId));
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Leg removed.']);
    }

    public function advanceStage($legId, ShipmentLegService $service)
    {
        $leg = ShipmentLeg::findOrFail($legId);
        $next = $leg->nextLifecycleStage();

        if ($next) {
            $service->transitionStatus($leg, $next);
            $this->refreshShipment();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Leg moved to ' . ShipmentLeg::LIFECYCLE_STAGES[$next]]);
        }
    }

    public function cancel($legId, ShipmentLegService $service)
    {
        $service->transitionStatus(ShipmentLeg::findOrFail($legId), 'cancelled');
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Leg cancelled.']);
    }

    public function hold($legId, ShipmentLegService $service)
    {
        $service->transitionStatus(ShipmentLeg::findOrFail($legId), 'on_hold');
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Leg put on hold.']);
    }

    public function recordAdHocMilestone($legId, ShipmentLegService $service)
    {
        if (!$this->ad_hoc_milestone_code) {
            return;
        }

        $service->transitionStatus(ShipmentLeg::findOrFail($legId), $this->ad_hoc_milestone_code);

        $this->reset(['ad_hoc_milestone_code']);
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Milestone recorded.']);
    }

    public function toggleExpand($legId)
    {
        $this->expanded_leg_id = $this->expanded_leg_id == $legId ? null : $legId;
    }

    public function openDispatch($legId)
    {
        $leg = ShipmentLeg::with('origin_location', 'destination_location')->findOrFail($legId);

        $this->dispatching_leg_id = $leg->id;
        $this->dispatch_transporter_id = Transporter::where('default', true)->value('id');
        $this->dispatch_horse_id = null;
        $this->dispatch_vehicle_id = null;
        $this->dispatch_driver_id = null;
        $this->dispatch_horses = [];
        $this->dispatch_vehicles = [];
        $this->dispatch_drivers = [];
        $this->dispatch_from = null;
        $this->dispatch_to = null;
        $this->dispatch_status = 'Scheduled';
        $this->dispatch_start_date = optional($leg->planned_departure)->format('Y-m-d');

        if ($this->dispatch_transporter_id) {
            $this->updatedDispatchTransporterId($this->dispatch_transporter_id);
        }

        $this->dispatchBrowserEvent('show-dispatchModal-' . $this->shipment->id);
    }

    public function updatedDispatchTransporterId($id)
    {
        if (!$id) {
            $this->dispatch_horses = [];
            $this->dispatch_vehicles = [];
            $this->dispatch_drivers = [];
            return;
        }

        $this->dispatch_horses = Horse::where('transporter_id', $id)
            ->where('status', 1)->where('service', 0)->where('archive', 0)
            ->orderBy('registration_number', 'asc')->get();
        $this->dispatch_vehicles = Vehicle::where('transporter_id', $id)
            ->where('status', 1)->where('service', 0)->where('archive', 0)
            ->orderBy('registration_number', 'asc')->get();
        $this->dispatch_drivers = Driver::with('employee:id,name,surname')
            ->where('transporter_id', $id)->where('archive', 0)->get();
    }

    public function dispatch(ShipmentLegService $service)
    {
        $this->validate([
            'dispatch_transporter_id' => 'required',
            'dispatch_driver_id' => 'required',
        ]);

        $leg = ShipmentLeg::findOrFail($this->dispatching_leg_id);
        $job = $this->shipment->freight_job;

        $trip = $service->dispatchViaOwnFleet($leg, [
            'company_id' => $job?->company_id,
            'customer_id' => $job?->customer_id,
            'currency_id' => $job?->currency_id,
            'transporter_id' => $this->dispatch_transporter_id,
            'horse_id' => $this->dispatch_horse_id ?: null,
            'vehicle_id' => $this->dispatch_vehicle_id ?: null,
            'driver_id' => $this->dispatch_driver_id,
            'from' => $this->dispatch_from,
            'to' => $this->dispatch_to,
            'cargo_details' => $this->shipment->cargo_description,
            'start_date' => $this->dispatch_start_date ?: null,
            'trip_status' => $this->dispatch_status,
        ]);

        $this->reset([
            'dispatching_leg_id', 'dispatch_transporter_id', 'dispatch_horse_id', 'dispatch_vehicle_id',
            'dispatch_driver_id', 'dispatch_horses', 'dispatch_vehicles', 'dispatch_drivers',
            'dispatch_from', 'dispatch_to', 'dispatch_start_date',
        ]);
        $this->dispatch_status = 'Scheduled';

        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-dispatchModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Trip ' . $trip->trip_number . ' created and linked to this leg.']);
    }

    public function render(ShipmentLegService $service)
    {
        foreach ($this->shipment->legs->whereNotNull('trip_id') as $leg) {
            $service->syncFromTrip($leg);
        }
        $this->refreshShipment();

        return view('livewire.freight.shipments.legs', [
            'lifecycleStages' => ShipmentLeg::LIFECYCLE_STAGES,
            'tripStatuses' => ['Scheduled', 'Started', 'Loading Point', 'Loaded', 'InTransit', 'Offloading Point', 'Offloaded', 'OnHold', 'Cancelled'],
        ]);
    }
}
