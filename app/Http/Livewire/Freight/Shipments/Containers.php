<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\Shipment;
use App\Models\ShippingContainer;
use App\Models\Vendor;
use App\Services\Freight\ShippingContainerService;
use Livewire\Component;

class Containers extends Component
{
    public $shipment;
    public $vendors;

    public $container_number;
    public $container_type;
    public $seal_number;
    public $shipping_line_vendor_id;
    public $shipping_line_name;
    public $tare_weight;
    public $gross_weight;
    public $cargo_weight;
    public $vgm;
    public $temperature;
    public $selected_cargo_ids = [];

    public $expanded_container_id;
    public $ad_hoc_milestone_code;

    protected $listeners = ['refreshJob' => '$refresh'];

    public function mount($shipmentId)
    {
        $this->shipment = Shipment::findOrFail($shipmentId);
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->refreshShipment();
    }

    private function refreshShipment()
    {
        $this->shipment = Shipment::with(['containers.shipping_line_vendor', 'containers.milestones', 'containers.cargo_items', 'cargo_items'])
            ->findOrFail($this->shipment->id);
    }

    protected function rules()
    {
        return [
            'container_number' => 'nullable|string|max:255',
            'container_type' => 'nullable|string|max:255',
        ];
    }

    public function store(ShippingContainerService $service)
    {
        $this->validate();

        $cargoLinks = collect($this->selected_cargo_ids)
            ->map(fn ($cargoId) => ['shipment_cargo_id' => $cargoId])
            ->values()
            ->all();

        $service->create([
            'shipment_id' => $this->shipment->id,
            'container_number' => $this->container_number,
            'container_type' => $this->container_type,
            'seal_number' => $this->seal_number,
            'shipping_line_vendor_id' => $this->shipping_line_vendor_id ?: null,
            'shipping_line_name' => $this->shipping_line_name,
            'tare_weight' => $this->tare_weight ?: null,
            'gross_weight' => $this->gross_weight ?: null,
            'cargo_weight' => $this->cargo_weight ?: null,
            'vgm' => $this->vgm ?: null,
            'temperature' => $this->temperature,
        ], $cargoLinks);

        $this->reset(['container_number', 'container_type', 'seal_number', 'shipping_line_vendor_id', 'shipping_line_name', 'tare_weight', 'gross_weight', 'cargo_weight', 'vgm', 'temperature', 'selected_cargo_ids']);

        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-addContainerModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Container added successfully!']);
    }

    public function advanceStage($containerId, ShippingContainerService $service)
    {
        $container = ShippingContainer::findOrFail($containerId);
        $next = $container->nextLifecycleStage();

        if ($next) {
            $service->transitionStatus($container, $next);
            $this->refreshShipment();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Container moved to ' . ShippingContainer::LIFECYCLE_STAGES[$next]]);
        }
    }

    public function recordAdHocMilestone($containerId, ShippingContainerService $service)
    {
        if (!$this->ad_hoc_milestone_code) {
            return;
        }

        $container = ShippingContainer::findOrFail($containerId);
        $service->transitionStatus($container, $this->ad_hoc_milestone_code);

        $this->reset(['ad_hoc_milestone_code']);
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Milestone recorded.']);
    }

    public function toggleExpand($containerId)
    {
        $this->expanded_container_id = $this->expanded_container_id == $containerId ? null : $containerId;
    }

    public function render()
    {
        return view('livewire.freight.shipments.containers', [
            'lifecycleStages' => ShippingContainer::LIFECYCLE_STAGES,
        ]);
    }
}
