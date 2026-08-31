{{--
    Customs Turnaround Time body. Params: from, to, rows, overall, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>{{ ($compact ?? true) ? 'CLEARING OFFICERS' : 'DECLARATIONS' }}</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._metric', ['label' => 'Average Turnaround (days)', 'value' => $overall['avgDays'] ?? 0, 'currencyCode' => ''])

<div style="margin-top: 18px;">
    <table class="ps-table">
        @if ($compact ?? true)
            <tr class="ps-row" style="font-weight: bold;">
                <td>Clearing Officer</td>
                <td class="ps-right">Declarations</td>
                <td class="ps-right">Avg Days</td>
                <td class="ps-right">Median Days</td>
            </tr>
            @foreach ($rows as $row)
            <tr class="ps-row">
                <td>{{ $row['label'] }}</td>
                <td class="ps-right">{{ $row['count'] }}</td>
                <td class="ps-right">{{ $row['avgDays'] }}</td>
                <td class="ps-right">{{ $row['medianDays'] }}</td>
            </tr>
            @endforeach
            <tr class="ps-total-row">
                <td>Overall</td>
                <td class="ps-right">{{ $overall['count'] ?? 0 }}</td>
                <td class="ps-right">{{ $overall['avgDays'] ?? 0 }}</td>
                <td class="ps-right">{{ $overall['medianDays'] ?? 0 }}</td>
            </tr>
        @else
            <tr class="ps-row" style="font-weight: bold;">
                <td>Declaration #</td>
                <td>Clearing Officer</td>
                <td>Submitted</td>
                <td>Cleared</td>
                <td class="ps-right">Days</td>
            </tr>
            @foreach ($rows as $row)
            <tr class="ps-row">
                <td>{{ $row['declaration_number'] }}</td>
                <td>{{ $row['clearing_officer'] }}</td>
                <td>{{ $row['submission_date'] }}</td>
                <td>{{ $row['clearance_date'] }}</td>
                <td class="ps-right">{{ $row['days'] }}</td>
            </tr>
            @endforeach
        @endif
    </table>
</div>
