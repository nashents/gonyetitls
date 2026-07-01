{{--
    Direct-method Cash Flow Statement body. Params:
    cash_received_from_customers, cash_paid_to_vendors, other_movements,
    net_operating_cash_flow, net_increase_in_cash, beginning_cash_balance,
    ending_cash_balance, currencyCode, compact, from, to
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>CASH FLOW</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._line_items', [
    'sectionTitle' => 'Cash Flows from Operating Activities',
    'totalLabel' => 'Net Cash from Operating Activities',
    'items' => [
        ['label' => 'Cash received from customers', 'amount' => $cash_received_from_customers],
        ['label' => 'Cash paid to vendors and suppliers', 'amount' => $cash_paid_to_vendors],
    ],
    'total' => $net_operating_cash_flow,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@if ($other_movements != 0)
@include('reports.shared._line_items', [
    'items' => [],
    'totalLabel' => 'Other/Unclassified Cash Movements',
    'total' => $other_movements,
    'currencyCode' => $currencyCode,
    'compact' => true,
])
@endif

@include('reports.shared._metric', [
    'label' => 'Net Increase/(Decrease) in Cash',
    'value' => $net_increase_in_cash,
    'currencyCode' => $currencyCode,
])

@include('reports.shared._metric', [
    'label' => 'Cash at Beginning of Period',
    'value' => $beginning_cash_balance,
    'currencyCode' => $currencyCode,
])

@include('reports.shared._metric', [
    'label' => 'Cash at End of Period',
    'value' => $ending_cash_balance,
    'currencyCode' => $currencyCode,
])
