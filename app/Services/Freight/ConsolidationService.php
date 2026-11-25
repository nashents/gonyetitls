<?php

namespace App\Services\Freight;

use App\Models\Consolidation;
use App\Models\Shipment;
use App\Services\NumberGeneratorService;
use Illuminate\Support\Facades\DB;

class ConsolidationService
{
    public function __construct(
        private NumberGeneratorService $numbers,
        private FreightCostingService $costing,
    ) {
    }

    public function create(array $data): Consolidation
    {
        return DB::transaction(function () use ($data) {
            $data['consolidation_number'] = $this->numbers->generate('consolidation', 'CN');
            $data['status'] = $data['status'] ?? 'draft';

            return Consolidation::create($data);
        });
    }

    public function attachShipment(Consolidation $consolidation, int $shipmentId, array $pivotData = []): void
    {
        DB::transaction(function () use ($consolidation, $shipmentId, $pivotData) {
            $consolidation->house_shipments()->syncWithoutDetaching([
                $shipmentId => $pivotData,
            ]);

            $this->resplit($consolidation);
        });
    }

    /** Attach several house shipments, re-splitting costs once at the end. */
    public function attachShipments(Consolidation $consolidation, array $shipmentIds): void
    {
        if (!$shipmentIds) {
            return;
        }

        DB::transaction(function () use ($consolidation, $shipmentIds) {
            $consolidation->house_shipments()->syncWithoutDetaching(
                collect($shipmentIds)->mapWithKeys(fn ($id) => [$id => ['allocation_value' => null]])->all()
            );

            $this->resplit($consolidation);
        });
    }

    public function updateAllocationValue(Consolidation $consolidation, int $shipmentId, $value): void
    {
        DB::transaction(function () use ($consolidation, $shipmentId, $value) {
            $consolidation->house_shipments()->updateExistingPivot($shipmentId, [
                'allocation_value' => is_numeric($value) ? $value : null,
            ]);

            $this->resplit($consolidation);
        });
    }

    public function updateBasis(Consolidation $consolidation, ?string $basis): void
    {
        DB::transaction(function () use ($consolidation, $basis) {
            $consolidation->update(['cost_allocation_basis' => $basis ?: null]);
            $this->resplit($consolidation);
        });
    }

    public function detachShipment(Consolidation $consolidation, int $shipmentId): void
    {
        DB::transaction(function () use ($consolidation, $shipmentId) {
            // Capture the jobs first so the detached job's share is cleared too.
            $jobIds = $this->jobIds($consolidation);
            $consolidation->house_shipments()->detach($shipmentId);
            $this->costing->recalculateJobs($jobIds);
        });
    }

    private function resplit(Consolidation $consolidation): void
    {
        $this->costing->recalculateJobs($this->jobIds($consolidation));
    }

    private function jobIds(Consolidation $consolidation): array
    {
        $shipmentIds = $consolidation->house_shipments()->pluck('shipments.id')
            ->push($consolidation->master_shipment_id);

        return Shipment::whereIn('id', $shipmentIds)->pluck('freight_job_id')->filter()->unique()->values()->all();
    }
}
