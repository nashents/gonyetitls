{{--
    Aged Payables body. Params: as_of_date, vendor_rows, grand_totals,
    currencyCode, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>VENDORS</td>
        <td class="ps-right">As of {{ \Carbon\Carbon::parse($as_of_date)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._aging_table', [
    'entityLabel' => 'Vendor',
    'bucketLabels' => \App\Services\AgedPayablesCalculator::BUCKETS,
    'rows' => $vendor_rows,
    'grandTotals' => $grand_totals,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])
