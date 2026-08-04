{{--
    Bank Reconciliation Statement body. Params: reconciliation, bank_account,
    statement_balance, outstanding_items, outstanding_total,
    adjusted_bank_balance, book_balance_before_adjustments, adjustment_items,
    adjustment_total, adjusted_book_balance, difference, is_balanced, currencyCode
--}}
<table class="ps-table ps-columns-head">
    <tr>
        <td>{{ $bank_account->name ?? '' }}</td>
        <td class="ps-right">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</td>
    </tr>
</table>
<hr class="ps-rule">

<div style="margin-bottom: 14px;">
    <span class="ps-badge {{ $reconciliation->status === 'completed' ? ($is_balanced ? 'ps-badge-ok' : 'ps-badge-bad') : 'ps-badge-bad' }}">
        {{ $reconciliation->status === 'completed' ? ($is_balanced ? 'Balanced' : 'Out of Balance') : 'In Progress - Not Yet Completed' }}
    </span>
</div>

@include('reports.shared._metric', [
    'label' => 'Balance per Bank Statement',
    'value' => $statement_balance,
    'currencyCode' => $currencyCode,
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Outstanding Items (deposits in transit / unpresented payments)',
    'totalLabel' => 'Total Outstanding Items',
    'items' => $outstanding_items,
    'total' => $outstanding_total,
    'currencyCode' => $currencyCode,
])

@if($reconciliation->status === 'completed')
@include('reports.shared._metric', [
    'label' => 'Adjusted Bank Balance',
    'value' => $adjusted_bank_balance,
    'currencyCode' => $currencyCode,
])
@endif

@include('reports.shared._metric', [
    'label' => 'Balance per Books (before adjustments)',
    'value' => $book_balance_before_adjustments,
    'currencyCode' => $currencyCode,
])

@include('reports.shared._line_items', [
    'sectionTitle' => 'Adjustments Recorded (bank charges, interest, etc.)',
    'totalLabel' => 'Total Adjustments',
    'items' => $adjustment_items,
    'total' => $adjustment_total,
    'currencyCode' => $currencyCode,
])

@if($reconciliation->status === 'completed')
@include('reports.shared._metric', [
    'label' => 'Adjusted Book Balance',
    'value' => $adjusted_book_balance,
    'currencyCode' => $currencyCode,
])

@include('reports.shared._metric', [
    'label' => 'Difference (Adjusted Bank - Adjusted Book)',
    'value' => $difference,
    'currencyCode' => $currencyCode,
])
@endif
