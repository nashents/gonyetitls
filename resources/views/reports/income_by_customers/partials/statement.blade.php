{{--
    Income by Customer body. Params: from, to, customer_items,
    total_income, currencyCode
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>CUSTOMERS</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._line_items', [
    'totalLabel' => 'Total Income',
    'items' => $customer_items,
    'total' => $total_income,
    'currencyCode' => $currencyCode,
    'compact' => false,
])
