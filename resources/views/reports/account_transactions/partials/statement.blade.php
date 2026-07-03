{{--
    Account Transactions (General Ledger) body. Params: account_name, from,
    to, opening_balance, closing_balance, total_debit, total_credit,
    transactions, currencyCode, compact
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>{{ $account_name }}</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }}<br>to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

@include('reports.shared._metric', [
    'label' => 'Opening Balance',
    'value' => $opening_balance,
    'currencyCode' => $currencyCode,
])

@if ($compact)
<div style="margin-top: 18px;">
    <table class="ps-table">
        <tr class="ps-row">
            <td>Total Debit</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($total_debit, $currencyCode) }}</td>
        </tr>
        <tr class="ps-row">
            <td>Total Credit</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($total_credit, $currencyCode) }}</td>
        </tr>
    </table>
</div>
@else
<div style="margin-top: 18px;">
    <table class="ps-table">
        <tr class="ps-row" style="font-weight: bold;">
            <td>Date</td>
            <td>Journal #</td>
            <td>Description</td>
            <td class="ps-right">Debit</td>
            <td class="ps-right">Credit</td>
            <td class="ps-right">Balance</td>
        </tr>
        @foreach ($transactions as $t)
        <tr class="ps-row">
            <td>{{ \Carbon\Carbon::parse($t['date'])->format('M d, Y') }}</td>
            <td>{{ $t['journal_number'] }}</td>
            <td>{{ $t['description'] }}</td>
            <td class="ps-right">{{ $t['debit'] != 0 ? \App\Services\ReportFormatter::waveMoney($t['debit'], $currencyCode) : '' }}</td>
            <td class="ps-right">{{ $t['credit'] != 0 ? \App\Services\ReportFormatter::waveMoney($t['credit'], $currencyCode) : '' }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($t['running_balance'], $currencyCode) }}</td>
        </tr>
        @endforeach
        @if (empty($transactions))
        <tr class="ps-row">
            <td colspan="6">No transactions in this period.</td>
        </tr>
        @endif
    </table>
</div>
@endif

@include('reports.shared._metric', [
    'label' => 'Closing Balance',
    'value' => $closing_balance,
    'currencyCode' => $currencyCode,
])
