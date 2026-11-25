<?php

namespace App\Http\Livewire\Portal;

use App\Models\Document;
use App\Models\FreightJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JobShow extends Component
{
    public $job;

    public function mount($jobId)
    {
        // Re-scoped by customer_id here too - defense in depth alongside
        // the controller's abort_unless ownership check.
        $this->job = FreightJob::where('customer_id', Auth::guard('customer')->id())
            ->with([
                'shipments.milestones' => function ($q) {
                    $q->where('is_customer_visible', true)
                        ->orderByRaw('COALESCE(actual_at, planned_at, estimated_at) desc');
                },
                // LCL: the shared container is tracked on the consolidation's
                // master shipment - show its customer-visible milestones and
                // container stage (never the master job or other clients).
                'shipments.consolidations.master_shipment.containers',
                'shipments.consolidations.master_shipment.milestones' => function ($q) {
                    $q->where('is_customer_visible', true);
                },
            ])
            ->findOrFail($jobId);
    }

    public function render()
    {
        $shipmentIds = $this->job->shipments->pluck('id');

        $documents = Document::query()
            ->where(function ($q) {
                $q->where('category', 'freight_job')->where('freight_job_id', $this->job->id);
            })
            ->orWhere(function ($q) use ($shipmentIds) {
                $q->where('category', 'shipment')->whereIn('shipment_id', $shipmentIds);
            })
            ->get();

        return view('livewire.portal.job-show', ['documents' => $documents]);
    }
}
