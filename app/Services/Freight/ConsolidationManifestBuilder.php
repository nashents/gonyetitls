<?php

namespace App\Services\Freight;

use App\Models\Consolidation;
use App\Models\Shipment;

/**
 * LCL / consolidation cargo manifest: the master container's details plus
 * one line per client cargo item (house job, HBL, shipper, consignee,
 * description, packages, weight, CBM, marks). Shared by the printable page
 * and the Excel export so both always agree.
 */
class ConsolidationManifestBuilder
{
    public function build(Consolidation $consolidation): array
    {
        $consolidation->load([
            'master_transport_document',
            'master_shipment.freight_job.fromDestination.country',
            'master_shipment.freight_job.toDestination.country',
            'master_shipment.containers.shipping_line',
            'master_shipment.transport_documents',
            'master_shipment.legs',
            'master_shipment.port_of_loading',
            'master_shipment.port_of_discharge',
            'master_shipment.loading_point',
            'master_shipment.offloading_point',
            'house_shipments.freight_job.customer',
            'house_shipments.cargo_items.cargo',
            'house_shipments.parties',
            'house_shipments.transport_documents',
        ]);

        $master = $consolidation->master_shipment;
        $masterJob = $master?->freight_job;

        $masterBl = $consolidation->master_transport_document
            ?? $master?->transport_documents->firstWhere('document_type', 'master_bill_of_lading')
            ?? $master?->transport_documents->firstWhere('document_type', 'master_air_waybill');

        $mainLeg = $master?->legs->firstWhere('transport_mode', $master->mode) ?? $master?->legs->first();

        $place = fn ($destination) => $destination ? trim(($destination->country->name ?? '') . ' ' . $destination->city) : null;

        $header = [
            'consolidation_number' => $consolidation->consolidation_number,
            'master_job' => $masterJob?->job_number,
            'master_shipment' => $master?->shipment_number,
            'mode' => ucfirst($master?->mode ?? $masterJob?->primary_transport_mode ?? ''),
            'master_bl' => $masterBl?->document_number,
            'carrier' => $masterBl?->carrierLabel() ?? $mainLeg?->carrier_name,
            'voyage' => $mainLeg?->carrier_reference,
            'origin' => $place($masterJob?->fromDestination),
            'destination' => $place($masterJob?->toDestination),
            'port_of_loading' => $master?->port_of_loading?->name ?? $master?->loading_point?->name,
            'port_of_discharge' => $master?->port_of_discharge?->name ?? $master?->offloading_point?->name,
            'etd' => $master?->etd?->format('d M Y'),
            'eta' => $master?->eta?->format('d M Y'),
            'containers' => ($master?->containers ?? collect())->map(fn ($c) => [
                'number' => $c->container_number,
                'type' => $c->container_type,
                'seal' => $c->seal_number,
                'line' => $c->shipping_line?->name ?? $c->shipping_line_name,
            ])->values()->all(),
        ];

        $rows = [];
        $seq = 0;

        foreach ($consolidation->house_shipments->sortBy(fn ($s) => $s->freight_job?->job_number) as $shipment) {
            $job = $shipment->freight_job;
            $hbl = $shipment->transport_documents->whereIn('document_type', ['house_bill_of_lading', 'house_air_waybill', 'sea_waybill'])->first();
            $shipper = $this->partyName($shipment, 'shipper');
            $consignee = $this->partyName($shipment, 'consignee') ?? $job?->customer?->name;

            $items = $shipment->cargo_items->isNotEmpty() ? $shipment->cargo_items : collect([null]);
            $seq++;

            foreach ($items as $n => $cargo) {
                $rows[] = [
                    'seq' => $seq,
                    'first_line' => $n === 0,
                    'house_job' => $job?->job_number,
                    'house_shipment' => $shipment->shipment_number,
                    'hbl' => $hbl?->document_number,
                    'client' => $job?->customer?->name,
                    'client_ref' => $job?->customer_reference,
                    'shipper' => $shipper,
                    'consignee' => $consignee,
                    'description' => $cargo ? trim(implode(' - ', array_filter([$cargo->cargo?->name, $cargo->commodity]))) : null,
                    'hs_code' => $cargo?->hs_code,
                    'packages' => $cargo ? (int) $cargo->packages : 0,
                    'package_type' => $cargo?->package_type,
                    'gross_weight' => $cargo ? (float) $cargo->gross_weight : 0.0,
                    'cbm' => $cargo ? (float) $cargo->cbm : 0.0,
                    'marks' => $cargo?->marks_and_numbers,
                    'dg' => (bool) $cargo?->is_dangerous_goods,
                ];
            }
        }

        $totals = [
            'clients' => $seq,
            'packages' => array_sum(array_column($rows, 'packages')),
            'gross_weight' => array_sum(array_column($rows, 'gross_weight')),
            'cbm' => array_sum(array_column($rows, 'cbm')),
        ];

        return compact('header', 'rows', 'totals');
    }

    private function partyName(Shipment $shipment, string $role): ?string
    {
        $party = $shipment->parties->firstWhere('role', $role);

        return $party ? $party->party()?->name : null;
    }
}
