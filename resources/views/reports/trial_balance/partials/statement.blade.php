{{--
    Trial Balance body. Params: date_from, date_to, grouped_lines
    (Collection keyed by account_type_group_name), totals, is_balanced,
    currencyCode
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>ACCOUNTS</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($date_from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($date_to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

<div style="margin-bottom: 14px;">
    <span class="ps-badge {{ $is_balanced ? 'ps-badge-ok' : 'ps-badge-bad' }}">
        {{ $is_balanced ? 'Balanced' : 'Out of Balance' }}
    </span>
</div>

@foreach ($grouped_lines as $groupName => $lines)
<div style="margin-top: 18px;">
    <table class="ps-table">
        <tr class="ps-section-head">
            <td colspan="4">{{ $groupName }}</td>
        </tr>
    </table>
    <table class="ps-table">
        <tr class="ps-row" style="font-weight: bold;">
            <td>Account</td>
            <td class="ps-right">Debit</td>
            <td class="ps-right">Credit</td>
            <td class="ps-right">Balance</td>
        </tr>
        @foreach ($lines as $line)
        <tr class="ps-row">
            <td>{{ $line->account_name }}</td>
            <td class="ps-right">{{ $line->total_debit > 0 ? \App\Services\ReportFormatter::waveMoney($line->total_debit, $currencyCode) : '' }}</td>
            <td class="ps-right">{{ $line->total_credit > 0 ? \App\Services\ReportFormatter::waveMoney($line->total_credit, $currencyCode) : '' }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($line->balance, $currencyCode) }}</td>
        </tr>
        @endforeach
        <tr class="ps-total-row">
            <td>{{ $groupName }} Total</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($lines->sum('total_debit'), $currencyCode) }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($lines->sum('total_credit'), $currencyCode) }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($lines->sum('total_debit') - $lines->sum('total_credit'), $currencyCode) }}</td>
        </tr>
    </table>
</div>
@endforeach

@include('reports.shared._metric', [
    'label' => 'Grand Total',
    'value' => $totals['debit'] - $totals['credit'],
    'currencyCode' => $currencyCode,
    'subtext' => 'Debit ' . \App\Services\ReportFormatter::waveMoney($totals['debit'], $currencyCode) . ' / Credit ' . \App\Services\ReportFormatter::waveMoney($totals['credit'], $currencyCode),
])
