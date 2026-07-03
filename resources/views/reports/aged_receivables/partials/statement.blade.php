{{--
    Aged Receivables body. Params: as_of_date, customer_rows,
    grand_totals, currencyCode, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>CUSTOMERS</td>
        <td class="ps-right">As of {{ \Carbon\Carbon::parse($as_of_date)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._aging_table', [
    'entityLabel' => 'Customer',
    'bucketLabels' => \App\Services\AgedReceivablesCalculator::BUCKETS,
    'rows' => $customer_rows,
    'grandTotals' => $grand_totals,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])
