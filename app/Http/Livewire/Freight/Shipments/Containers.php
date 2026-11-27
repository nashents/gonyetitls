<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\ContainerChargeExposure;
use App\Models\ContainerStage;
use App\Models\Shipment;
use App\Models\ShippingContainer;
use App\Models\ShippingLine;
use App\Services\Freight\PortExposureService;
use App\Services\Freight\ShippingContainerService;
use Carbon\Carbon;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

class Containers extends Component
{
    use FreightEmbeddedPagination;

    public $shipment;
    public $shippingLines;

    public $container_number;
    public $container_type;
    public $seal_number;
    public $shipping_line_id;
    public $tare_weight;
    public $gross_weight;
    public $cargo_weight;
    public $vgm;
    public $temperature;
    public $selected_cargo_ids = [];

    public $expanded_container_id;
    public $ad_hoc_milestone_code;
    public $ad_hoc_milestone_date;
    /** Next-stage event date per container id (blank = now). */
    public $stage_dates = [];

    protected $listeners = ['refreshJob' => '$refresh'];

    public function mount($shipmentId)
    {
        $this->shipment = Shipment::findOrFail($shipmentId);
        $this->shippingLines = ShippingLine::where('is_active', true)->orderBy('name', 'asc')->get();
        $this->refreshShipment();
    }

    private function refreshShipment()
    {
        $this->shipment = Shipment::with(['containers.shipping_line', 'containers.milestones.creator', 'containers.cargo_items', 'containers.exposures.currency', 'cargo_items'])
            ->findOrFail($this->shipment->id);
    }

    protected function rules()
    {
        return [
            'container_number' => 'nullable|string|max:255',
            'container_type' => 'nullable|string|max:255',
        ];
    }

    /**
     * LCL house (client) shipment: its cargo rides in the consolidation's
     * master container, recorded on the master job - no containers of its own.
     */
    public function isHouseShipment(): bool
    {
        return $this->shipment->consolidations()->exists();
    }

    public function store(ShippingContainerService $service)
    {
        if ($this->isHouseShipment()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This shipment is consolidated - add the container on the master job.']);
            return;
        }

        $this->validate();

        $cargoLinks = collect($this->selected_cargo_ids)
            ->map(fn ($cargoId) => ['shipment_cargo_id' => $cargoId])
            ->values()
            ->all();

        $jobStatusBefore = $this->jobStatus();
        $service->create([
            'shipment_id' => $this->shipment->id,
            'container_number' => $this->container_number,
            'container_type' => $this->container_type,
            'seal_number' => $this->seal_number,
            'shipping_line_id' => $this->shipping_line_id ?: null,
            'tare_weight' => $this->tare_weight ?: null,
            'gross_weight' => $this->gross_weight ?: null,
            'cargo_weight' => $this->cargo_weight ?: null,
            'vgm' => $this->vgm ?: null,
            'temperature' => $this->temperature,
        ], $cargoLinks);

        $this->reset(['container_number', 'container_type', 'seal_number', 'shipping_line_id', 'tare_weight', 'gross_weight', 'cargo_weight', 'vgm', 'temperature', 'selected_cargo_ids']);

        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-addContainerModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Container added successfully!' . $this->jobStatusNote($jobStatusBefore)]);
    }

    public function advanceStage($containerId, ShippingContainerService $service)
    {
        $container = ShippingContainer::findOrFail($containerId);
        $next = $container->nextLifecycleStage();

        if ($next) {
            $eventAt = $this->eventDate("stage_dates.{$containerId}", $this->stage_dates[$containerId] ?? null);

            $jobStatusBefore = $this->jobStatus();
            $service->transitionStatus($container, $next, ['actual_at' => $eventAt]);
            unset($this->stage_dates[$containerId]);
            $this->refreshShipment();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Container moved to ' . ContainerStage::label($next) . ' on ' . $eventAt->format('d M Y H:i') . '.' . $this->jobStatusNote($jobStatusBefore)]);
        }
    }

    private function jobStatus(): ?string
    {
        return \App\Models\FreightJob::whereKey($this->shipment->freight_job_id)->value('status');
    }

    /** " Job status is now Delivered." when the container stage moved the job (FreightJobStatusSync), and refreshes the job page. */
    private function jobStatusNote(?string $before): string
    {
        $after = $this->jobStatus();
        if ($after === $before) {
            return '';
        }

        $this->emit('jobStatusChanged');

        return ' Job status is now ' . ucwords(str_replace('_', ' ', (string) $after)) . '.';
    }

    public function recordAdHocMilestone($containerId, ShippingContainerService $service)
    {
        if (!$this->ad_hoc_milestone_code) {
            return;
        }

        $eventAt = $this->eventDate('ad_hoc_milestone_date', $this->ad_hoc_milestone_date);

        $container = ShippingContainer::findOrFail($containerId);
        $jobStatusBefore = $this->jobStatus();
        $service->transitionStatus($container, $this->ad_hoc_milestone_code, ['actual_at' => $eventAt]);

        $this->reset(['ad_hoc_milestone_code', 'ad_hoc_milestone_date']);
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Milestone recorded.' . $this->jobStatusNote($jobStatusBefore)]);
    }

    /**
     * When the stage actually happened. Drives the milestone's actual_at and
     * the start/stop date of any free-time clock the stage triggers
     * (PortExposureService::handleContainerTransition). Blank = now.
     */
    private function eventDate(string $field, $value): Carbon
    {
        if ($value === null || $value === '') {
            return now();
        }

        $this->validate(
            [$field => 'date|before_or_equal:now'],
            [$field . '.before_or_equal' => 'The stage date cannot be in the future.'],
            [$field => 'stage date']
        );

        return Carbon::parse($value);
    }

    public function toggleExpand($containerId)
    {
        $this->expanded_container_id = $this->expanded_container_id == $containerId ? null : $containerId;
    }

    public function refresh($category)
    {
        if ($category === 'shipping_lines') {
            $this->shippingLines = ShippingLine::where('is_active', true)->orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Shipping Lines Refreshed Successfully!!.']);
        }
    }

    public function render(PortExposureService $exposureService)
    {
        if ($this->expanded_container_id) {
            $expandedContainer = $this->shipment->containers->firstWhere('id', $this->expanded_container_id);

            if ($expandedContainer) {
                $exposureService->refreshOpenExposures($expandedContainer);
                $expandedContainer->load('exposures.currency');
            }
        }

        return view('livewire.freight.shipments.containers', [
            'containerRows' => $this->paginateRows($this->shipment->containers->sortBy('id')),
            // Labels incl. deactivated custom stages (old statuses/milestones); the dropdown offers active ones only.
            'lifecycleStages' => ShippingContainer::lifecycleStages(true),
            'activeStages' => ShippingContainer::lifecycleStages(),
            'chargeTypes' => ContainerChargeExposure::chargeTypes(true),
            'isHouse' => $this->isHouseShipment(),
        ]);
    }
}
