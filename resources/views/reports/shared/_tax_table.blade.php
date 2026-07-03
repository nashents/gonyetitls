{{--
    A tax breakdown table: Tax | Taxable Amount | Tax Amount, followed by
    two bordered total rows. Used for the Sales Tax Report's Output Tax
    (sales) and Input Tax (purchases) sections.

    Params:
    - title: gray section header, e.g. "Sales (Output Tax)"
    - rows: array of ['label' => string, 'taxable' => float, 'tax' => float]
    - totalTaxableLabel, totalTaxable
    - totalTaxLabel, totalTax
    - currencyCode
    - compact: bool - when true, skip the per-tax breakdown and only show totals
--}}
@php
    $nonZeroRows = collect($rows ?? [])->filter(fn ($r) => ($r['taxable'] ?? 0) != 0 || ($r['tax'] ?? 0) != 0)->values();
    $showRows = !($compact ?? false) && $nonZeroRows->isNotEmpty();
@endphp

<div style="margin-top: 18px;">
    @if (!empty($title))
    <table class="ps-table">
        <tr class="ps-section-head">
            <td colspan="3">{{ $title }}</td>
        </tr>
    </table>
    @endif

    @if ($showRows)
    <table class="ps-table">
        <tr class="ps-row" style="font-weight: bold;">
            <td>Tax</td>
            <td class="ps-right">Taxable Amount</td>
            <td class="ps-right">Tax Amount</td>
        </tr>
        @foreach ($nonZeroRows as $row)
        <tr class="ps-row">
            <td>{{ $row['label'] }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['taxable'], $currencyCode) }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['tax'], $currencyCode) }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <table class="ps-table">
        <tr class="ps-total-row">
            <td>{{ $totalTaxableLabel }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($totalTaxable, $currencyCode) }}</td>
        </tr>
        <tr class="ps-total-row">
            <td>{{ $totalTaxLabel }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($totalTax, $currencyCode) }}</td>
        </tr>
    </table>
</div>
