{{--
    Balance Sheet body. Params: as_of_date, assets_items, total_assets,
    liabilities_items, total_liabilities, equity_items, total_equity,
    total_liabilities_and_equity, is_balanced, currencyCode, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>ACCOUNTS</td>
        <td class="ps-right">As of {{ \Carbon\Carbon::parse($as_of_date)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

<div style="margin-bottom: 14px;">
    <span class="ps-badge {{ $is_balanced ? 'ps-badge-ok' : 'ps-badge-bad' }}">
        {{ $is_balanced ? 'Balanced' : 'Out of Balance' }}
    </span>
</div>

@include('reports.shared._line_items', [
    'sectionTitle' => 'Assets',
    'totalLabel' => 'Total Assets',
    'items' => $assets_items,
    'total' => $total_assets,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Liabilities',
    'totalLabel' => 'Total Liabilities',
    'items' => $liabilities_items,
    'total' => $total_liabilities,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Equity',
    'totalLabel' => 'Total Equity',
    'items' => $equity_items,
    'total' => $total_equity,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._metric', [
    'label' => 'Total Liabilities and Equity',
    'value' => $total_liabilities_and_equity,
    'currencyCode' => $currencyCode,
])
