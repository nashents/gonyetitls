{{-- The ACCOUNTS / date-range column heads, then the P&L body itself. --}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>ACCOUNTS</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@php
    $incomeItems = collect($income_accounts)->map(fn ($a) => ['label' => $a->name, 'amount' => $income_by_account[$a->id] ?? 0])->all();
    $cogsItems = collect($cost_of_goods_sold_accounts)->map(fn ($a) => ['label' => $a->name, 'amount' => $cost_of_goods_sold_by_account[$a->id] ?? 0])->all();
    $opexItems = collect($operating_expenses_accounts)->map(fn ($a) => ['label' => $a->name, 'amount' => $operating_expenses_by_account[$a->id] ?? 0])->all();
    $otherIncomeItems = collect($other_income_accounts)->map(fn ($a) => ['label' => $a->name, 'amount' => $other_income_by_account[$a->id] ?? 0])->all();
    $otherExpensesItems = collect($other_expenses_accounts)->map(fn ($a) => ['label' => $a->name, 'amount' => $other_expenses_by_account[$a->id] ?? 0])->all();
@endphp

@include('reports.shared._line_items', [
    'sectionTitle' => 'Income',
    'totalLabel' => 'Total Income',
    'items' => $incomeItems,
    'total' => $total_income,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Cost of Goods Sold',
    'totalLabel' => 'Total Cost of Goods Sold',
    'items' => $cogsItems,
    'total' => $total_cost_of_goods_sold,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._metric', [
    'label' => 'Gross Profit',
    'value' => $gross_profit,
    'percentage' => $gross_profit_percentage,
    'currencyCode' => $currencyCode,
    'subtext' => 'As a percentage of Total Income',
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Operating Expenses',
    'totalLabel' => 'Total Operating Expenses',
    'items' => $opexItems,
    'total' => $total_operating_expenses,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Other Income',
    'totalLabel' => 'Total Other Income',
    'items' => $otherIncomeItems,
    'total' => $total_other_income,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Other Expenses',
    'totalLabel' => 'Total Other Expenses',
    'items' => $otherExpensesItems,
    'total' => $total_other_expenses,
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])

@include('reports.shared._metric', [
    'label' => 'Net Profit',
    'value' => $net_profit,
    'percentage' => $net_profit_percentage,
    'currencyCode' => $currencyCode,
    'subtext' => 'As a percentage of Total Income',
])
