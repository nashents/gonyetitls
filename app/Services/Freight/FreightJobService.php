<?php

namespace App\Services\Freight;

use App\Models\FreightJob;
use App\Models\Shipment;
use App\Models\ShipmentCargo;
use App\Models\ShipmentParty;
use App\Services\NumberGeneratorService;
use Illuminate\Support\Facades\DB;

class FreightJobService
{
    public function __construct(private NumberGeneratorService $numbers)
    {
    }

    /**
     * Create a FreightJob together with its first Shipment (and that
     * shipment's cargo/party rows), per the approved Phase 1 create flow.
     *
     * $data: FreightJob attributes.
     * $shipment: Shipment attributes.
     * $cargoRows: list of ShipmentCargo attribute arrays.
     * $partyRows: list of ShipmentParty attribute arrays.
     */
    public function create(array $data, array $shipment = [], array $cargoRows = [], array $partyRows = []): FreightJob
    {
        return DB::transaction(function () use ($data, $shipment, $cargoRows, $partyRows) {
            $job = new FreightJob($data);
            $job->job_number = $this->numbers->generate('freight_job', 'FF');
            $job->status = $data['status'] ?? 'draft';
            $job->opened_at = $data['opened_at'] ?? now();
            $job->save();

            if (!empty($shipment)) {
                $this->createShipment($job, $shipment, $cargoRows, $partyRows);
            }

            return $job;
        });
    }

    /**
     * Add another Shipment to an existing FreightJob (Phase 2: a job is
     * never capped at one shipment).
     */
    public function addShipment(FreightJob $job, array $shipmentData, array $cargoRows = [], array $partyRows = []): Shipment
    {
        return DB::transaction(fn () => $this->createShipment($job, $shipmentData, $cargoRows, $partyRows));
    }

    private function createShipment(FreightJob $job, array $shipment, array $cargoRows, array $partyRows): Shipment
    {
        $shipmentModel = new Shipment($shipment);
        $shipmentModel->freight_job_id = $job->id;
        $shipmentModel->shipment_number = $this->numbers->generate('shipment', 'SH');
        $shipmentModel->status = $shipment['status'] ?? 'draft';
        $shipmentModel->save();

        foreach ($cargoRows as $cargoRow) {
            $cargoRow['shipment_id'] = $shipmentModel->id;
            ShipmentCargo::create($cargoRow);
        }

        foreach ($partyRows as $partyRow) {
            $partyRow['shipment_id'] = $shipmentModel->id;
            ShipmentParty::create($partyRow);
        }

        return $shipmentModel;
    }

    public function transitionStatus(FreightJob $job, string $status): FreightJob
    {
        $job->status = $status;

        if ($status === 'closed') {
            $job->closed_at = now();
        }

        if ($status === 'delivered') {
            $job->completed_at = now();
        }

        $job->save();

        return $job;
    }
}
