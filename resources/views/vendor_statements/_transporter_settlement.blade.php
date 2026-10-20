{{--
    Transporter "Trip Settlement" statement (TransporterSettlementService::build).
    Shared by the screen, preview, print and PDF views - keep it dompdf-safe (tables, inline styles, no flex).
    Expects: $settlement, $from, $to; optional $links (bool) for clickable bill/trip numbers.
--}}
@php
    $links = $links ?? false;
    $money = fn ($symbol, $v) => ($v < 0 ? '-' : '').$symbol.number_format(abs($v), 2);
    $th = 'style="border:1px solid #ccc; padding:5px; background:#f2f2f2; text-align:center; font-weight:bold;"';
    $td = 'border:1px solid #ccc; padding:5px;';
@endphp

@if (empty($settlement))
    <p style="text-align:center; padding:20px;">No transporter activity between {{ date('F j, Y', strtotime($from)) }} and {{ date('F j, Y', strtotime($to)) }}.</p>
@endif

@foreach ($settlement as $block)
    @php
        $c = $block['currency'];
        $s = $c->symbol ?? '';
        $t = $block['totals'];
    @endphp

    <h4 style="margin:18px 0 6px;">Trip Settlement &mdash; {{ $c->fullname ?? '' }} {{ $c->name }}</h4>
    <div style="margin-bottom:8px; color:#555;">{{ date('F j, Y', strtotime($from)) }} to {{ date('F j, Y', strtotime($to)) }}</div>

    {{-- Summary --}}
    <table style="width:100%; max-width:620px; border-collapse:collapse; margin-bottom:14px;">
        <tr><td style="{{ $td }}">Opening balance due to transporter ({{ date('j M Y', strtotime($from)) }})</td><td style="{{ $td }} text-align:right;">{{ $money($s, $t['opening']) }}</td></tr>
        <tr><td style="{{ $td }}">Add: transporter freight on trips offloaded in the period</td><td style="{{ $td }} text-align:right;">{{ $money($s, $t['freight']) }}</td></tr>
        <tr><td style="{{ $td }}">Less: expenses recovered (deducted from trip payments)</td><td style="{{ $td }} text-align:right;">{{ $money($s, -$t['deducted']) }}</td></tr>
        @if (abs($t['other_bills']) > 0.009)
        <tr><td style="{{ $td }}">Add: other bills for the transporter</td><td style="{{ $td }} text-align:right;">{{ $money($s, $t['other_bills']) }}</td></tr>
        @endif
        <tr><td style="{{ $td }}">Less: payments made</td><td style="{{ $td }} text-align:right;">{{ $money($s, -$t['payments']) }}</td></tr>
        <tr style="font-weight:bold;"><td style="{{ $td }} background:#eef5fb;">Closing balance due to transporter ({{ date('j M Y', strtotime($to)) }})</td><td style="{{ $td }} text-align:right; background:#eef5fb;">{{ $money($s, $t['closing']) }}</td></tr>
        @if (abs($t['invoices_due']) > 0.009)
        <tr><td style="{{ $td }}">Less: invoices to transporter still unpaid</td><td style="{{ $td }} text-align:right;">{{ $money($s, -$t['invoices_due']) }}</td></tr>
        <tr style="font-weight:bold;"><td style="{{ $td }} background:#eef5fb;">Net position ({{ $t['net_position'] >= 0 ? 'we owe the transporter' : 'transporter owes us' }})</td><td style="{{ $td }} text-align:right; background:#eef5fb;">{{ $money($s, abs($t['net_position'])) }}</td></tr>
        @endif
        @if ($t['unrecovered'] > 0.009)
        <tr style="color:#b00;"><td style="{{ $td }}">Note: expenses paid for the transporter not yet recovered</td><td style="{{ $td }} text-align:right;">{{ $money($s, $t['unrecovered']) }}</td></tr>
        @endif
        @if ($block['in_transit']->isNotEmpty())
        <tr style="color:#555;"><td style="{{ $td }}">Note: trips in transit, payable once offloaded (not in balance)</td><td style="{{ $td }} text-align:right;">{{ $money($s, $t['in_transit_net']) }}</td></tr>
        @endif
    </table>

    {{-- Trips --}}
    <h5 style="margin:12px 0 4px;">Trips offloaded in the period</h5>
    <table style="width:100%; border-collapse:collapse; margin-bottom:14px; font-size:12px;">
        <thead>
            <tr>
                <th {!! $th !!}>Trip</th>
                <th {!! $th !!}>Offloaded</th>
                <th {!! $th !!}>Route</th>
                <th {!! $th !!}>Transporter Freight</th>
                <th {!! $th !!}>Paid For Transporter</th>
                <th {!! $th !!}>Deducted</th>
                <th {!! $th !!}>Invoiced</th>
                <th {!! $th !!}>Net Payable</th>
                <th {!! $th !!}>Paid</th>
                <th {!! $th !!}>Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($block['trips'] as $row)
                <tr>
                    <td style="{{ $td }}">
                        @if ($links && $row['trip'])
                            <a href="{{ route('trips.show', $row['trip']->id) }}" target="_blank" style="color:blue">{{ $row['trip_number'] }}</a>
                        @else
                            {{ $row['trip_number'] }}
                        @endif
                        <br><small>
                        @if ($links)
                            <a href="{{ route('bills.show', $row['bill']->id) }}" target="_blank" style="color:blue">Bill# {{ $row['bill']->bill_number }}</a>
                        @else
                            Bill# {{ $row['bill']->bill_number }}
                        @endif
                        </small>
                    </td>
                    <td style="{{ $td }} text-align:center;">{{ $row['date'] ? date('j M Y', strtotime($row['date'])) : '' }}</td>
                    <td style="{{ $td }}">{{ $row['route'] }}</td>
                    <td style="{{ $td }} text-align:right;">{{ $money($s, $row['freight']) }}</td>
                    <td style="{{ $td }} text-align:right;">{{ $row['recoverables_total'] > 0 ? $money($s, $row['recoverables_total']) : '-' }}</td>
                    <td style="{{ $td }} text-align:right;">{{ $row['deducted'] > 0 ? $money($s, -$row['deducted']) : '-' }}</td>
                    <td style="{{ $td }} text-align:right;">{{ $row['invoiced'] > 0 ? $money($s, $row['invoiced']) : '-' }}</td>
                    <td style="{{ $td }} text-align:right; font-weight:bold;">{{ $money($s, $row['net']) }}</td>
                    <td style="{{ $td }} text-align:right;">{{ $money($s, $row['paid']) }}</td>
                    <td style="{{ $td }} text-align:right;">{{ $money($s, $row['due']) }}</td>
                </tr>
                @foreach ($row['items'] as $item)
                    <tr style="color:#555;">
                        <td style="{{ $td }}"></td>
                        <td style="{{ $td }} text-align:center;">{{ $item['date'] ? date('j M Y', strtotime($item['date'])) : '' }}</td>
                        <td style="{{ $td }} padding-left:14px;" colspan="2">- {{ $item['name'] }} <small>(paid by {{ $item['funded'] }})</small></td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $item['amount']) }}</td>
                        <td style="{{ $td }}" colspan="5"></td>
                    </tr>
                @endforeach
                @if ($row['unrecovered'] > 0.009)
                    <tr style="color:#b00;">
                        <td style="{{ $td }}"></td>
                        <td style="{{ $td }}" colspan="3">Not yet recovered on this trip</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $row['unrecovered']) }}</td>
                        <td style="{{ $td }}" colspan="5"></td>
                    </tr>
                @endif
            @empty
                <tr><td style="{{ $td }} text-align:center;" colspan="10">No trips offloaded in this period.</td></tr>
            @endforelse
        </tbody>
        @if ($block['trips']->isNotEmpty())
        <tfoot>
            <tr style="font-weight:bold;">
                <td style="{{ $td }}" colspan="3">Totals</td>
                <td style="{{ $td }} text-align:right;">{{ $money($s, $t['freight']) }}</td>
                <td style="{{ $td }} text-align:right;">{{ $money($s, $t['recoverables']) }}</td>
                <td style="{{ $td }} text-align:right;">{{ $money($s, -$t['deducted']) }}</td>
                <td style="{{ $td }} text-align:right;">{{ $money($s, $t['invoiced']) }}</td>
                <td style="{{ $td }} text-align:right;">{{ $money($s, $t['net_trip_bills']) }}</td>
                <td style="{{ $td }} text-align:right;">{{ $money($s, $block['trips']->sum('paid')) }}</td>
                <td style="{{ $td }} text-align:right;">{{ $money($s, $block['trips']->sum('due')) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Payments --}}
    <h5 style="margin:12px 0 4px;">Payments made in the period</h5>
    <table style="width:100%; border-collapse:collapse; margin-bottom:14px; font-size:12px;">
        <thead><tr><th {!! $th !!}>Date</th><th {!! $th !!}>Payment</th><th {!! $th !!}>Notes</th><th {!! $th !!}>Amount</th></tr></thead>
        <tbody>
            @forelse ($block['payments'] as $p)
                <tr>
                    <td style="{{ $td }} text-align:center;">{{ date('j M Y', strtotime($p['date'])) }}</td>
                    <td style="{{ $td }}">
                        @if ($links)<a href="{{ route('payments.show', $p['payment']->id) }}" target="_blank" style="color:blue">{{ $p['number'] }}</a>@else{{ $p['number'] }}@endif
                    </td>
                    <td style="{{ $td }}">{{ $p['notes'] }}</td>
                    <td style="{{ $td }} text-align:right;">{{ $money($s, $p['amount']) }}</td>
                </tr>
            @empty
                <tr><td style="{{ $td }} text-align:center;" colspan="4">No payments in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($block['other']->isNotEmpty())
        <h5 style="margin:12px 0 4px;">Other bills for the transporter</h5>
        <table style="width:100%; border-collapse:collapse; margin-bottom:14px; font-size:12px;">
            <thead><tr><th {!! $th !!}>Date</th><th {!! $th !!}>Bill</th><th {!! $th !!}>Description</th><th {!! $th !!}>Total</th><th {!! $th !!}>Paid</th><th {!! $th !!}>Due</th></tr></thead>
            <tbody>
                @foreach ($block['other'] as $b)
                    <tr>
                        <td style="{{ $td }} text-align:center;">{{ date('j M Y', strtotime($b['date'])) }}</td>
                        <td style="{{ $td }}">
                            @if ($links)<a href="{{ route('bills.show', $b['bill']->id) }}" target="_blank" style="color:blue">{{ $b['number'] }}</a>@else{{ $b['number'] }}@endif
                        </td>
                        <td style="{{ $td }}">{{ $b['description'] }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $b['total']) }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $b['paid']) }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $b['due']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($block['invoices']->isNotEmpty())
        <h5 style="margin:12px 0 4px;">Invoices to the transporter (recovered expenses)</h5>
        <table style="width:100%; border-collapse:collapse; margin-bottom:14px; font-size:12px;">
            <thead><tr><th {!! $th !!}>Date</th><th {!! $th !!}>Invoice</th><th {!! $th !!}>Trips</th><th {!! $th !!}>Total</th><th {!! $th !!}>Received</th><th {!! $th !!}>Unpaid</th></tr></thead>
            <tbody>
                @foreach ($block['invoices'] as $i)
                    <tr>
                        <td style="{{ $td }} text-align:center;">{{ date('j M Y', strtotime($i['date'])) }}</td>
                        <td style="{{ $td }}">
                            @if ($links)<a href="{{ route('invoices.show', $i['invoice']->id) }}" target="_blank" style="color:blue">{{ $i['number'] }}</a>@else{{ $i['number'] }}@endif
                        </td>
                        <td style="{{ $td }} text-align:center;">{{ $i['trips'] }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $i['total']) }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $i['paid']) }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $i['due']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($block['in_transit']->isNotEmpty())
        <h5 style="margin:12px 0 4px;">Trips in transit (payable once offloaded &mdash; not in the balance)</h5>
        <table style="width:100%; border-collapse:collapse; margin-bottom:14px; font-size:12px;">
            <thead><tr><th {!! $th !!}>Trip</th><th {!! $th !!}>Status</th><th {!! $th !!}>Route</th><th {!! $th !!}>Transporter Freight</th><th {!! $th !!}>Paid For Transporter</th><th {!! $th !!}>Expected Net</th></tr></thead>
            <tbody>
                @foreach ($block['in_transit'] as $row)
                    <tr>
                        <td style="{{ $td }}">
                            @if ($links && $row['trip'])<a href="{{ route('trips.show', $row['trip']->id) }}" target="_blank" style="color:blue">{{ $row['trip_number'] }}</a>@else{{ $row['trip_number'] }}@endif
                        </td>
                        <td style="{{ $td }} text-align:center;">{{ $row['status'] }}</td>
                        <td style="{{ $td }}">{{ $row['route'] }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $row['freight']) }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $row['recoverables_total'] > 0 ? $money($s, $row['recoverables_total']) : '-' }}</td>
                        <td style="{{ $td }} text-align:right;">{{ $money($s, $row['net']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach
