{{--
    Sales Tax Report body. Params: from, to, output_tax_rows,
    total_taxable_sales, total_tax_collected, input_tax_rows,
    total_taxable_purchases, total_tax_paid, net_tax_payable,
    currencyCode, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>ACCOUNTS</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._tax_table', [
    'title' => 'Sales (Output Tax)',
    'rows' => $output_tax_rows,
    'totalTaxableLabel' => 'Total Taxable Sales',
    'totalTaxable' => $total_taxable_sales,
    'totalTaxLabel' => 'Total Tax Collected',
    'totalTax' => $total_tax_collected,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._tax_table', [
    'title' => 'Purchases (Input Tax)',
    'rows' => $input_tax_rows,
    'totalTaxableLabel' => 'Total Taxable Purchases',
    'totalTaxable' => $total_taxable_purchases,
    'totalTaxLabel' => 'Total Tax Paid',
    'totalTax' => $total_tax_paid,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._metric', [
    'label' => 'Net Tax Payable',
    'value' => $net_tax_payable,
    'currencyCode' => $currencyCode,
    'subtext' => 'Tax Collected less Tax Paid',
])
