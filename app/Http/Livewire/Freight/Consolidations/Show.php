<?php

namespace App\Http\Livewire\Freight\Consolidations;

use App\Models\Consolidation;
use App\Models\Shipment;
use App\Services\Freight\ConsolidationService;
use Livewire\Component;

class Show extends Component
{
    public $consolidation;
    public $availableShipments;

    public $house_shipment_id;
    public $allocation_value;

    public function mount($id)
    {
        $this->consolidation = Consolidation::findOrFail($id);
        $this->refresh();
    }

    private function refresh()
    {
        $this->consolidation = Consolidation::with([
            'master_shipment.freight_job.customer',
            'master_transport_document',
            'house_shipments.freight_job.customer',
        ])->findOrFail($this->consolidation->id);

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
        ]);

        $service->attachShipment($this->consolidation, $this->house_shipment_id, [
            'allocation_value' => $this->allocation_value ?: null,
        ]);

        $this->reset(['house_shipment_id', 'allocation_value']);
        $this->refresh();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'House shipment attached.']);
    }

    public function detachShipment($shipmentId, ConsolidationService $service)
    {
        $service->detachShipment($this->consolidation, $shipmentId);
        $this->refresh();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'House shipment detached.']);
    }

    public function render()
    {
        return view('livewire.freight.consolidations.show');
    }
}
