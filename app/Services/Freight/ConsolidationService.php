<?php

namespace App\Services\Freight;

use App\Models\Consolidation;
use App\Services\NumberGeneratorService;
use Illuminate\Support\Facades\DB;

class ConsolidationService
{
    public function __construct(private NumberGeneratorService $numbers)
    {
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
        $consolidation->house_shipments()->syncWithoutDetaching([
            $shipmentId => $pivotData,
        ]);
    }

    public function detachShipment(Consolidation $consolidation, int $shipmentId): void
    {
        $consolidation->house_shipments()->detach($shipmentId);
    }
}
