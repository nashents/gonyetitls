{{--
    Port & Demurrage/Detention Exposure body. Params: as_of_date,
    vendor_rows, grand_totals, status_breakdown, currencyCode, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>SHIPPING LINES</td>
        <td class="ps-right">As of {{ \Carbon\Carbon::parse($as_of_date)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._metric', [
    'label' => 'Total Estimated Exposure (Open)',
    'value' => $grand_totals['total'] ?? 0,
    'currencyCode' => $currencyCode,
])

@include('reports.shared._metric', [
    'label' => 'Total Actual Charged (Open)',
    'value' => $grand_totals['actual_total'] ?? 0,
    'currencyCode' => $currencyCode,
])

@include('reports.shared._aging_table', [
    'entityLabel' => 'Shipping Line',
    'bucketLabels' => \App\Models\ContainerChargeExposure::CHARGE_TYPES,
    'rows' => $vendor_rows,
    'grandTotals' => $grand_totals,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])
