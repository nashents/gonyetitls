<?php

namespace App\Http\Livewire\Freight\Consolidations;

use App\Models\Shipment;
use App\Services\Freight\ConsolidationService;
use Livewire\Component;

class Create extends Component
{
    public $shipments;

    public $master_shipment_id;
    public $cost_allocation_basis;
    public $notes;

    public function mount()
    {
        $this->shipments = Shipment::with('freight_job.customer')->orderBy('shipment_number', 'desc')->get();
    }

    protected function rules()
    {
        return [
            'master_shipment_id' => 'required|integer|exists:shipments,id',
        ];
    }

    public function store(ConsolidationService $service)
    {
        $this->validate();

        $consolidation = $service->create([
            'master_shipment_id' => $this->master_shipment_id,
            'cost_allocation_basis' => $this->cost_allocation_basis,
            'notes' => $this->notes,
        ]);

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Consolidation ' . $consolidation->consolidation_number . ' created successfully!']);

        return redirect()->route('freight.consolidations.show', $consolidation->id);
    }

    public function render()
    {
        return view('livewire.freight.consolidations.create');
    }
}
