{{--
    Report footer: "{Title} - {Company}" / "Created on ..." and the date
    range (or as-of date) / page number line.
    Params: reportTitle, company, from, to, asOfDate (optional)
--}}
<table class="ps-table ps-footer">
    <tr>
        <td>{{ $reportTitle }} - {{ $company->name ?? '' }}</td>
        <td class="ps-right">Created on {{ \Carbon\Carbon::now()->format('M d, Y') }}</td>
    </tr>
    <tr>
        @if (isset($asOfDate))
        <td>As of {{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}</td>
        @else
        <td>Date Range: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
        @endif
        <td class="ps-right">Page 1 / 1</td>
    </tr>
</table>
