<?php

namespace App\Http\Livewire\Freight\Consolidations;

use App\Models\Consolidation;
use App\Models\Shipment;
use App\Services\Freight\ConsolidationCostAllocator;
use App\Services\Freight\ConsolidationService;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

class Show extends Component
{
    use FreightEmbeddedPagination;

    public $clientsPage = 1;

    public $consolidation;
    public $availableShipments;

    public $house_shipment_id;
    public $allocation_value;

    public $cost_allocation_basis;
    public $allocation_values = [];

    public function mount($id)
    {
        $this->consolidation = Consolidation::findOrFail($id);
        $this->refresh();
    }

    private function refresh()
    {
        $this->consolidation = Consolidation::with([
            'master_shipment.freight_job.customer',
            'master_shipment.containers',
            'master_transport_document',
            'house_shipments.freight_job.customer',
            'house_shipments.cargo_items',
        ])->findOrFail($this->consolidation->id);

        $this->cost_allocation_basis = ConsolidationCostAllocator::basisOf($this->consolidation);
        $this->allocation_values = $this->consolidation->house_shipments
            ->mapWithKeys(fn ($s) => [$s->id => $s->pivot->allocation_value !== null ? (float) $s->pivot->allocation_value : null])
            ->all();

        $excludeIds = $this->consolidation->house_shipments->pluck('id')
            ->push($this->consolidation->master_shipment_id);

        $this->availableShipments = Shipment::with('freight_job.customer')
            ->whereNotIn('id', $excludeIds)
            ->orderBy('shipment_number', 'desc')
            ->get();
    }

    public function attachShipment(ConsolidationService $service)
    {
        $this->validate([
            'house_shipment_id' => 'required|integer|exists:shipments,id',
            'allocation_value' => 'nullable|numeric|min:0',
        ]);

        $service->attachShipment($this->consolidation, $this->house_shipment_id, [
            'allocation_value' => is_numeric($this->allocation_value) ? $this->allocation_value : null,
        ]);

        $this->reset(['house_shipment_id', 'allocation_value']);
        $this->refresh();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'House shipment attached - master costs re-split.']);
    }

    public function updatedCostAllocationBasis($value)
    {
        if (!array_key_exists($value, ConsolidationCostAllocator::BASES)) {
            return;
        }

        app(ConsolidationService::class)->updateBasis($this->consolidation, $value);
        $this->refresh();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Allocation basis updated - master costs re-split.']);
    }

    public function saveAllocationValue($shipmentId, ConsolidationService $service)
    {
        $value = $this->allocation_values[$shipmentId] ?? null;

        if ($value !== null && $value !== '' && (!is_numeric($value) || $value < 0)) {
            $this->addError('allocation_values.' . $shipmentId, 'Enter a number of 0 or more.');
            return;
        }

        $service->updateAllocationValue($this->consolidation, (int) $shipmentId, $value);
        $this->refresh();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Allocation value saved - master costs re-split.']);
    }

    public function detachShipment($shipmentId, ConsolidationService $service)
    {
        $service->detachShipment($this->consolidation, $shipmentId);
        $this->refresh();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'House shipment detached - master costs re-split.']);
    }

    public function render(ConsolidationCostAllocator $allocator)
    {
        return view('livewire.freight.consolidations.show', [
            'breakdown' => $allocator->breakdown($this->consolidation),
            'houseRows' => $this->paginateRows($this->consolidation->house_shipments->sortBy('id'), 'clientsPage'),
            'bases' => ConsolidationCostAllocator::BASES,
            'usesValue' => ConsolidationCostAllocator::usesAllocationValue($this->cost_allocation_basis),
            'valueHint' => ConsolidationCostAllocator::VALUE_HINTS[$this->cost_allocation_basis] ?? null,
        ]);
    }
}
