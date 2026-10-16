{{--
    Excel layout for FinancialReportExport: a plain-text header (no logo)
    followed by the report's own statement partial.
    Params: partial, header (title, asOfDate|from+to, selectedType), plus the report data.
--}}
<table>
    <tr><td><b>{{ $header['title'] }}</b></td></tr>
    <tr><td>{{ $company->name ?? '' }}</td></tr>
    @if (!empty($header['asOfDate']))
    <tr><td>As of {{ \Carbon\Carbon::parse($header['asOfDate'])->format('M d, Y') }}</td></tr>
    @elseif (!empty($header['from']) && !empty($header['to']))
    <tr><td>Date Range: {{ \Carbon\Carbon::parse($header['from'])->format('M d, Y') }} to {{ \Carbon\Carbon::parse($header['to'])->format('M d, Y') }}</td></tr>
    @endif
    @if (!empty($header['selectedType']))
    <tr><td>Report Type: {{ $header['selectedType'] }}</td></tr>
    @endif
    @if (!empty($currencyCode))
    <tr><td>Currency: {{ $currencyCode }}</td></tr>
    @endif
    <tr><td></td></tr>
</table>

@include($partial)
