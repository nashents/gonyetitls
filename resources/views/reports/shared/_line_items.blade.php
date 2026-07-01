{{--
    One report section: an optional gray section-title bar, a list of
    label/amount rows, and a bordered total row - or, when there's
    nothing to itemize (compact mode, or every item is zero), a single
    gray bar with just the total. This is the core building block reused
    by every Wave-style financial report.

    Params:
    - items: array of ['label' => string, 'amount' => float]
    - totalLabel: e.g. "Total Income"
    - total: float
    - currencyCode: string
    - sectionTitle: string|null - gray header row shown above the list
      when there are items to show (omit to skip straight to the list)
    - compact: bool - when true, always collapse to the single total bar
--}}
@php
    $nonZero = collect($items ?? [])->filter(fn ($item) => ($item['amount'] ?? 0) != 0)->values();
    $showItems = !($compact ?? false) && $nonZero->isNotEmpty();
@endphp

<div style="margin-top: 18px;">
@if ($showItems)
    @if (!empty($sectionTitle))
    <table class="ps-table">
        <tr class="ps-section-head">
            <td colspan="2">{{ $sectionTitle }}</td>
        </tr>
    </table>
    @endif
    <table class="ps-table">
        @foreach ($nonZero as $item)
        <tr class="ps-row">
            <td>{{ $item['label'] }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($item['amount'], $currencyCode) }}</td>
        </tr>
        @endforeach
        <tr class="ps-total-row">
            <td>{{ $totalLabel }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($total, $currencyCode) }}</td>
        </tr>
    </table>
@else
    <table class="ps-table">
        <tr class="ps-section-head">
            <td>{{ $totalLabel }}</td>
            <td class="ps-right">{{ \App\Services\ReportFormatter::waveMoney($total, $currencyCode) }}</td>
        </tr>
    </table>
@endif
</div>
