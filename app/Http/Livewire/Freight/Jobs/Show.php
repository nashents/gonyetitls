<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Http\Livewire\Freight\Jobs\Concerns\AuthorizesFreightJobs;
use App\Models\FreightJob;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesFreightJobs;

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
            'fromDestination.country',
            'toDestination.country',
            'currency',
            'quotation',
            'authorized_by',
            'shipments.legs',
            'shipments.loading_point',
            'shipments.offloading_point',
            'shipments.cargo_items',
            'shipments.parties',
            'shipments.containers',
            'shipments.transport_documents',
            'shipments.customs_declarations',
            // LCL: client jobs see the master container; master sees its clients.
            'shipments.consolidations.master_shipment.freight_job',
            'shipments.consolidations.master_shipment.containers.shipping_line',
            'shipments.consolidations.master_shipment.milestones.shipping_container',
            'shipments.master_consolidation.house_shipments.freight_job.customer',
        ])->findOrFail($this->job->id);
    }

    protected function afterFreightJobAuthorized(): void
    {
        $this->refreshJob();
    }

    public function render()
    {
        return view('livewire.freight.jobs.show');
    }
}
