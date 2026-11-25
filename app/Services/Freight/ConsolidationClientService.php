<?php

namespace App\Services\Freight;

use App\Models\Consolidation;
use App\Models\FreightJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Bulk "Add Clients" for an LCL consolidation: each client gets their own
 * freight job (job + LCL shipment + cargo), copying route, mode, currency,
 * team, points and dates from the master job/shipment, then the new
 * shipments are attached as house shipments in one go.
 *
 * Rows for the same client + reference + service type become ONE job with
 * several cargo lines (a client shipping motorbikes and spares together).
 */
class ConsolidationClientService
{
    public function __construct(
        private FreightJobService $jobs,
        private ConsolidationService $consolidations,
        private FreightJobAuthorizationService $authorization,
    ) {
    }

    /**
     * $rows: [{customer_id, customer_reference, freight_service_type_id,
     *          cargo_id, commodity, hs_code, packages, quantity, uom,
     *          gross_weight, cbm, marks_and_numbers}]
     *
     * @return FreightJob[] the jobs created
     */
    public function addClients(Consolidation $consolidation, array $rows): array
    {
        $consolidation->loadMissing('master_shipment.freight_job');
        $master = $consolidation->master_shipment;
        $masterJob = $master?->freight_job;

        $groups = collect($rows)->groupBy(fn ($r) => implode('|', [
            $r['customer_id'], trim((string) ($r['customer_reference'] ?? '')), $r['freight_service_type_id'] ?? '',
        ]));

        return DB::transaction(function () use ($consolidation, $groups, $master, $masterJob) {
            $created = [];
            $shipmentIds = [];

            foreach ($groups as $clientRows) {
                $first = $clientRows->first();

                $jobData = [
                    'user_id' => Auth::id(),
                    'customer_id' => $first['customer_id'],
                    'customer_reference' => trim((string) ($first['customer_reference'] ?? '')) ?: null,
                    'company_id' => $masterJob?->company_id ?? Auth::user()->employee?->company_id,
                    'freight_service_type_id' => $first['freight_service_type_id'] ?: null,
                    'salesperson_id' => $masterJob?->salesperson_id,
                    'operations_officer_id' => $masterJob?->operations_officer_id,
                    'clearing_officer_id' => $masterJob?->clearing_officer_id,
                    'incoterm' => $masterJob?->incoterm,
                    'currency_id' => $masterJob?->currency_id,
                    'import_export_type' => $masterJob?->import_export_type,
                    'primary_transport_mode' => $masterJob?->primary_transport_mode ?? $master?->mode ?? 'sea',
                    'from' => $masterJob?->from,
                    'to' => $masterJob?->to,
                    'notes' => 'Client in consolidation ' . $consolidation->consolidation_number,
                ];

                $shipmentData = [
                    'user_id' => Auth::id(),
                    'mode' => $master?->mode ?? $jobData['primary_transport_mode'],
                    'shipment_type' => 'LCL',
                    'port_of_loading_id' => $master?->port_of_loading_id,
                    'port_of_discharge_id' => $master?->port_of_discharge_id,
                    'loading_point_id' => $master?->loading_point_id,
                    'offloading_point_id' => $master?->offloading_point_id,
                    'place_of_receipt_id' => $master?->place_of_receipt_id,
                    'place_of_delivery_id' => $master?->place_of_delivery_id,
                    'etd' => $master?->etd,
                    'eta' => $master?->eta,
                    'freight_terms' => $master?->freight_terms,
                    'incoterm' => $masterJob?->incoterm,
                ];

                $cargoRows = $clientRows->map(fn ($r) => [
                    'cargo_id' => $r['cargo_id'] ?: null,
                    'commodity' => trim((string) ($r['commodity'] ?? '')) ?: null,
                    'hs_code' => trim((string) ($r['hs_code'] ?? '')) ?: null,
                    'packages' => $this->num($r['packages'] ?? null),
                    'quantity' => $this->num($r['quantity'] ?? null),
                    'uom' => ($r['uom'] ?? null) ?: null,
                    'gross_weight' => $this->num($r['gross_weight'] ?? null),
                    'cbm' => $this->num($r['cbm'] ?? null),
                    'marks_and_numbers' => trim((string) ($r['marks_and_numbers'] ?? '')) ?: null,
                ])->values()->all();

                $job = $this->jobs->create($jobData, $shipmentData, $cargoRows);
                $this->authorization->notifyAuthorizers($job);

                $created[] = $job;
                $shipmentIds[] = $job->shipments()->value('id');
            }

            $this->consolidations->attachShipments($consolidation, array_filter($shipmentIds));

            return $created;
        });
    }

    private function num($value)
    {
        return ($value === null || $value === '') ? null : $value;
    }
}
