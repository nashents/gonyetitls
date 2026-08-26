<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Models\FreightJob;
use Livewire\Component;

class Show extends Component
{
    public $job;

    protected $listeners = ['shipmentAdded' => 'refreshJob'];

    public function mount($id)
    {
        $this->job = FreightJob::findOrFail($id);
        $this->refreshJob();
    }

    public function refreshJob()
    {
        $this->job = FreightJob::with([
            'customer',
            'freight_service_type',
            'salesperson',
            'operations_officer',
            'clearing_officer',
            'origin_country',
            'destination_country',
            'currency',
            'quotation',
            'shipments.legs',
            'shipments.cargo_items',
            'shipments.parties',
            'shipments.containers',
            'shipments.transport_documents',
            'shipments.customs_declarations',
        ])->findOrFail($this->job->id);
    }

    public function render()
    {
        return view('livewire.freight.jobs.show');
    }
}
