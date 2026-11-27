<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Http\Livewire\Freight\Jobs\Concerns\AuthorizesFreightJobs;
use App\Models\FreightJob;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

class Show extends Component
{
    use AuthorizesFreightJobs, FreightEmbeddedPagination;

    public $cargoPage = 1;
    public $partiesPage = 1;

    public $job;

    protected $listeners = ['shipmentAdded' => 'refreshJob', 'jobStatusChanged' => 'refreshJob'];

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
        $cargoRows = $this->job->shipments->flatMap(fn ($s) => $s->cargo_items->sortBy('id')->map(fn ($item) => ['shipment' => $s, 'item' => $item]));
        $partyRows = $this->job->shipments->flatMap(fn ($s) => $s->parties->sortBy('id')->map(fn ($party) => ['shipment' => $s, 'party' => $party]));

        return view('livewire.freight.jobs.show', [
            'cargoRows' => $this->paginateRows($cargoRows, 'cargoPage'),
            'partyRows' => $this->paginateRows($partyRows, 'partiesPage'),
        ]);
    }
}
