<?php

namespace App\Services\Freight;

use App\Models\ContainerChargeExposure;

/**
 * Fleet-wide Port & Demurrage/Detention Exposure Report: aggregates every
 * currently-open (stop_date IS NULL) ContainerChargeExposure row, grouped
 * by shipping line vendor with charge_type as the bucket dimension.
 * Point-in-time only - no date filter, since "open exposure right now" is
 * the whole point. Complements (does not duplicate) the per-container
 * expand-row shown on the Containers tab (PortExposureService).
 */
class PortExposureCalculator
{
    public function __construct(private ?int $shippingLineVendorId = null)
    {
    }

    private function openExposures()
    {
        return ContainerChargeExposure::whereNull('stop_date')
            ->when($this->shippingLineVendorId, function ($query) {
                $query->whereHas('shipping_container', function ($q) {
                    $q->where('shipping_line_vendor_id', $this->shippingLineVendorId);
                });
            })
            ->with('shipping_container.shipping_line_vendor');
    }

    /**
     * @return array{
     *     0: array<int, array{label: string, buckets: array<string, float>, total: float, actual_total: float}>,
     *     1: array{buckets: array<string, float>, total: float, actual_total: float}
     * } [rows keyed by shipping_line_vendor_id, grand totals]
     */
    public function byShippingLine(): array
    {
        $bucketLabels = array_values(ContainerChargeExposure::CHARGE_TYPES);

        $rows = [];
        $grandTotals = array_fill_keys($bucketLabels, 0.0);
        $grandTotal = 0.0;
        $grandActualTotal = 0.0;

        foreach ($this->openExposures()->get() as $exposure) {
            $estimated = (float) ($exposure->estimated_exposure ?? 0);
            $actual = (float) ($exposure->actual_charge ?? 0);
            $bucketLabel = ContainerChargeExposure::CHARGE_TYPES[$exposure->charge_type] ?? $exposure->charge_type;
            $vendor = $exposure->shipping_container?->shipping_line_vendor;
            $vendorId = $vendor?->id ?? 0;

            $rows[$vendorId] ??= [
                'label' => $vendor?->name ?? $exposure->shipping_container?->shipping_line_name ?? 'Unassigned Shipping Line',
                'buckets' => array_fill_keys($bucketLabels, 0.0),
                'total' => 0.0,
                'actual_total' => 0.0,
            ];

            $rows[$vendorId]['buckets'][$bucketLabel] += $estimated;
            $rows[$vendorId]['total'] += $estimated;
            $rows[$vendorId]['actual_total'] += $actual;

            $grandTotals[$bucketLabel] += $estimated;
            $grandTotal += $estimated;
            $grandActualTotal += $actual;
        }

        usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [$rows, ['buckets' => $grandTotals, 'total' => $grandTotal, 'actual_total' => $grandActualTotal]];
    }

    /**
     * @return array<string, int> counts of open exposures per status.
     */
    public function statusBreakdown(): array
    {
        return $this->openExposures()->get()
            ->countBy('status')
            ->all();
    }

    /**
     * @return array<int, array{container_number: ?string, vendor: ?string, charge_type: string, status: string, chargeable_days: ?int, last_free_day: ?string, estimated_exposure: float, actual_charge: float}>
     */
    public function details(): array
    {
        return $this->openExposures()
            ->with('shipping_container')
            ->get()
            ->map(fn (ContainerChargeExposure $exposure) => [
                'container_number' => $exposure->shipping_container?->container_number,
                'vendor' => $exposure->shipping_container?->shipping_line_vendor?->name ?? $exposure->shipping_container?->shipping_line_name,
                'charge_type' => ContainerChargeExposure::CHARGE_TYPES[$exposure->charge_type] ?? $exposure->charge_type,
                'status' => $exposure->status,
                'chargeable_days' => $exposure->chargeable_days,
                'last_free_day' => optional($exposure->last_free_day)->format('Y-m-d'),
                'estimated_exposure' => (float) ($exposure->estimated_exposure ?? 0),
                'actual_charge' => (float) ($exposure->actual_charge ?? 0),
            ])
            ->sortByDesc('estimated_exposure')
            ->values()
            ->all();
    }
}
