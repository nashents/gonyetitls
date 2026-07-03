{{--
    Account Balances body. Params: from, to, groups (keyed by
    account_type_group_name => ['rows'=>..., 'grandTotals'=>...]),
    currencyCode, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>ACCOUNTS</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@foreach ($groups as $groupName => $group)
<div style="margin-top: 18px;">
    <table class="ps-table">
        <tr class="ps-section-head">
            <td colspan="4">{{ $groupName }}</td>
        </tr>
    </table>
</div>

@include('reports.shared._aging_table', [
    'entityLabel' => 'Account',
    'bucketLabels' => ['Opening Balance', 'Activity'],
    'totalLabel' => 'Closing Balance',
    'rows' => $group['rows'],
    'grandTotals' => $group['grandTotals'],
    'currencyCode' => $currencyCode,
    'compact' => $compact,
])
@endforeach
