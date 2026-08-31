<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\FreightJob;
use App\Models\ShipmentMilestone;
use App\Services\Freight\ShipmentMilestoneService;
use Livewire\Component;

class Milestones extends Component
{
    public $job;

    public $shipment_id;
    public $milestone_code;
    public $milestone_name;
    public $planned_at;
    public $is_customer_visible = true;

    public function mount($jobId)
    {
        $this->job = FreightJob::findOrFail($jobId);
        $this->refreshJob();
    }

    private function refreshJob()
    {
        $this->job = FreightJob::with(['shipments.milestones' => function ($q) {
            $q->orderByRaw('COALESCE(actual_at, planned_at, estimated_at) desc');
        }])->findOrFail($this->job->id);
    }

    protected function rules()
    {
        return [
            'shipment_id' => 'required|integer|exists:shipments,id',
            'milestone_code' => 'required|string|max:255',
            'milestone_name' => 'required|string|max:255',
        ];
    }

    public function store(ShipmentMilestoneService $service)
    {
        $this->validate();

        $service->record([
            'shipment_id' => $this->shipment_id,
            'milestone_code' => $this->milestone_code,
            'milestone_name' => $this->milestone_name,
            'planned_at' => $this->planned_at ?: null,
            'is_customer_visible' => $this->is_customer_visible,
        ]);

        $this->reset(['shipment_id', 'milestone_code', 'milestone_name', 'planned_at']);
        $this->is_customer_visible = true;
        $this->refreshJob();
        $this->dispatchBrowserEvent('hide-addMilestoneModal-' . $this->job->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Milestone recorded.']);
    }

    public function complete($milestoneId, ShipmentMilestoneService $service)
    {
        $milestone = ShipmentMilestone::findOrFail($milestoneId);
        $service->complete($milestone);

        $this->refreshJob();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Milestone marked complete.']);
    }

    public function toggleVisibility($milestoneId)
    {
        $milestone = ShipmentMilestone::findOrFail($milestoneId);
        $milestone->update(['is_customer_visible' => ! $milestone->is_customer_visible]);

        $this->refreshJob();
    }

    public function render()
    {
        $milestones = $this->job->shipments->flatMap->milestones
            ->sortByDesc(fn ($m) => $m->actual_at ?? $m->planned_at ?? $m->estimated_at ?? $m->created_at);

        return view('livewire.freight.shipments.milestones', [
            'milestones' => $milestones,
        ]);
    }
}
