{{--
    Freight Job Profitability body. Params: from, to, rows, grand_totals,
    compact (summary vs details), currencyCode
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>{{ ($compact ?? true) ? 'CUSTOMERS' : 'JOBS' }}</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._metric', ['label' => 'Total Revenue', 'value' => $grand_totals['revenue'] ?? 0, 'currencyCode' => $currencyCode])
@include('reports.shared._metric', ['label' => 'Total Cost', 'value' => $grand_totals['cost'] ?? 0, 'currencyCode' => $currencyCode])
@include('reports.shared._metric', ['label' => 'Gross Margin', 'value' => $grand_totals['margin'] ?? 0, 'currencyCode' => $currencyCode, 'percentage' => $grand_totals['marginPct'] ?? 0])

<div style="margin-top: 18px;">
    <table class="ps-table">
        @if ($compact ?? true)
            <tr class="ps-row" style="font-weight: bold;">
                <td>Customer</td>
                <td class="ps-right">Jobs</td>
                <td class="ps-right">Revenue</td>
                <td class="ps-right">Cost</td>
                <td class="ps-right">Margin</td>
                <td class="ps-right">Margin %</td>
            </tr>
            @foreach ($rows as $row)
            <tr class="ps-row">
                <td>{{ $row['label'] }}</td>
                <td class="ps-right">{{ $row['jobCount'] }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['revenue'], $currencyCode) }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['cost'], $currencyCode) }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['margin'], $currencyCode) }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::percent($row['marginPct']) }}</td>
            </tr>
            @endforeach
        @else
            <tr class="ps-row" style="font-weight: bold;">
                <td>Job #</td>
                <td>Customer</td>
                <td>Status</td>
                <td>Opened</td>
                <td class="ps-right">Revenue</td>
                <td class="ps-right">Cost</td>
                <td class="ps-right">Margin</td>
                <td class="ps-right">Margin %</td>
            </tr>
            @foreach ($rows as $row)
            <tr class="ps-row">
                <td>{{ $row['job_number'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $row['status'])) }}</td>
                <td>{{ $row['opened_at'] }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['revenue'], $currencyCode) }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['cost'], $currencyCode) }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($row['margin'], $currencyCode) }}</td>
                <td class="ps-right">{{ \App\Services\ReportFormatter::percent($row['marginPct']) }}</td>
            </tr>
            @endforeach
        @endif
        <tr class="ps-total-row">
            <td colspan="{{ ($compact ?? true) ? 1 : 4 }}">Total ({{ $grand_totals['jobCount'] ?? 0 }} jobs)</td>
            @if ($compact ?? true)
            <td></td>
            @endif
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($grand_totals['revenue'] ?? 0, $currencyCode) }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($grand_totals['cost'] ?? 0, $currencyCode) }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($grand_totals['margin'] ?? 0, $currencyCode) }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::percent($grand_totals['marginPct'] ?? 0) }}</td>
        </tr>
    </table>
</div>
