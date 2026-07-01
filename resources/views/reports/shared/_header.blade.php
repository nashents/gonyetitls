{{--
    Report header: title, company name/logo, date range (or a single
    as-of date for point-in-time reports like the Balance Sheet),
    optional report type line.
    Params: title, company, from, to, selectedType (optional), asOfDate (optional)
--}}
<table class="ps-table">
    <tr>
        <td style="vertical-align:top;">
            <h1 class="ps-title">{{ $title }}</h1>
            <div class="ps-company">{{ $company->name ?? '' }}</div>
            @if (isset($asOfDate))
            <div class="ps-meta">As of {{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}</div>
            @else
            <div class="ps-meta">Date Range: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</div>
            @endif
            @isset($selectedType)
            <div class="ps-meta">Report Type: {{ $selectedType }}</div>
            @endisset
        </td>
        <td style="vertical-align:top; width:160px;" class="ps-right">
            @if (!empty($company) && $company->logo)
            <img src="{{ asset('images/uploads/'.$company->logo) }}" class="ps-logo" alt="">
            @endif
        </td>
    </tr>
</table>
