{{--
    Purchase by Vendor body. Params: from, to, vendor_items,
    total_purchases, currencyCode
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>VENDORS</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._line_items', [
    'totalLabel' => 'Total Purchases',
    'items' => $vendor_items,
    'total' => $total_purchases,
    'currencyCode' => $currencyCode,
    'compact' => false,
])
