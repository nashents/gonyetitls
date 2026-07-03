{{--
    A multi-column aging table: Entity | bucket columns... | Total, with a
    bold grand-total row. Shared by Aged Receivables and Aged Payables.

    Params:
    - entityLabel: header for the first column, e.g. "Customer" / "Vendor"
    - bucketLabels: array of column headers, e.g. ['0-30 Days', '31-60 Days', ...]
    - rows: array of ['label' => string, 'buckets' => [bucketLabel => float], 'total' => float]
    - grandTotals: ['buckets' => [bucketLabel => float], 'total' => float]
    - currencyCode
    - compact: bool - when true, skip per-entity rows and only show the grand total row
    - totalLabel: header/row label for the trailing total column (default "Total")
--}}
@php
    $totalLabel = $totalLabel ?? 'Total';
    $nonZeroRows = collect($rows ?? [])->filter(fn ($r) => ($r['total'] ?? 0) != 0)->values();
    $showRows = !($compact ?? false) && $nonZeroRows->isNotEmpty();
@endphp

<div style="margin-top: 18px;">
    <table class="ps-table">
        <tr class="ps-row" style="font-weight: bold;">
            <td>{{ $entityLabel }}</td>
            @foreach ($bucketLabels as $bucketLabel)
            <td class="ps-right">{{ $bucketLabel }}</td>
            @endforeach
            <td class="ps-right">{{ $totalLabel }}</td>
        </tr>

        @if ($showRows)
        @foreach ($nonZeroRows as $row)
        <tr class="ps-row">
            <td>{{ $row['label'] }}</td>
            @foreach ($bucketLabels as $bucketLabel)
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['buckets'][$bucketLabel] ?? 0, $currencyCode) }}</td>
            @endforeach
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['total'], $currencyCode) }}</td>
        </tr>
        @endforeach
        @endif

        <tr class="ps-total-row">
            <td>Total</td>
            @foreach ($bucketLabels as $bucketLabel)
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($grandTotals['buckets'][$bucketLabel] ?? 0, $currencyCode) }}</td>
            @endforeach
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($grandTotals['total'], $currencyCode) }}</td>
        </tr>
    </table>
</div>
