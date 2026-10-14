{{--
    ZIMRA sales & purchases schedule body, shared by the live Livewire
    preview and the print view so they never lay the numbers out
    differently.
    Params: groups (ZimraScheduleCalculator::build()), periodLabel,
    links (bool, link document numbers to their records).
--}}
@php
    $money = fn ($v) => \App\Services\ReportFormatter::money($v);
    $links = $links ?? false;
@endphp

@forelse ($groups as $group)
    @php
        $cur = $group['currency']?->name ?? 'NO CURRENCY';
        $sym = $group['currency']?->symbol ?? '';
    @endphp

    <table class="zs-table zs-summary">
        <tr>
            <th colspan="4" class="zs-group-title">{{ $cur }} VAT SUMMARY &mdash; {{ $periodLabel }}</th>
        </tr>
        <tr>
            <td>Output VAT (Sales)<br><strong>{{ $sym }}{{ $money($group['summary']['output_vat']) }}</strong></td>
            <td>Less: Credit Note VAT<br><strong>{{ $sym }}{{ $money($group['summary']['credit_vat']) }}</strong></td>
            <td>Less: Input VAT (Purchases)<br><strong>{{ $sym }}{{ $money($group['summary']['input_vat']) }}</strong></td>
            <td>
                {{ $group['summary']['net_vat'] < 0 ? 'Net VAT Refundable' : 'Net VAT Payable' }}<br>
                <strong class="{{ $group['summary']['net_vat'] < 0 ? 'zs-neg' : '' }}">{{ $sym }}{{ $money($group['summary']['net_vat']) }}</strong>
            </td>
        </tr>
    </table>

    @foreach ($group['sections'] as $key => $section)
        @php
            $isPurchase = in_array($key, [\App\Services\ZimraScheduleCalculator::PURCHASES_VAT, \App\Services\ZimraScheduleCalculator::PURCHASES_NO_VAT]);
            $isCredit = $key === \App\Services\ZimraScheduleCalculator::CREDIT_NOTES;
            $cols = $isPurchase ? 9 : 7;
        @endphp
        <table class="zs-table">
            <thead>
                <tr>
                    <th colspan="{{ $cols }}" class="zs-section-title">{{ $periodLabel }} {{ $cur }} {{ $section['title'] }}</th>
                </tr>
                <tr class="zs-head">
                    <th style="width:90px">DATE</th>
                    <th>DETAILS</th>
                    <th>{{ $isPurchase ? 'VAT NUMBER' : 'VAT / TIN' }}</th>
                    <th>{{ $isCredit ? 'CREDIT NOTE No' : 'INVOICE NUMBER' }}</th>
                    @if ($isPurchase)
                        <th>BILL #</th>
                        <th>DESCRIPTIONS</th>
                    @endif
                    <th class="zs-num">{{ $isPurchase ? 'SUB AMOUNT' : 'AMOUNT BF VAT' }}</th>
                    <th class="zs-num">{{ $isPurchase ? 'VAT AMOUNT' : 'VAT' }}</th>
                    <th class="zs-num">TOTAL AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($section['rows'] as $row)
                    <tr>
                        <td>{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : '' }}</td>
                        <td>{{ strtoupper($row['party']) }}</td>
                        <td>{{ $row['vat_tin'] ?: 'N/A' }}</td>
                        <td>
                            @if ($links && !$isPurchase)
                                <a href="{{ $row['url'] }}" target="_blank">{{ $row['number'] }}</a>
                            @else
                                {{ $row['number'] ?: 'N/A' }}
                            @endif
                        </td>
                        @if ($isPurchase)
                            <td>
                                @if ($links)
                                    <a href="{{ $row['url'] }}" target="_blank">{{ $row['reference'] }}</a>
                                @else
                                    {{ $row['reference'] }}
                                @endif
                            </td>
                            <td>{{ $row['description'] }}</td>
                        @endif
                        <td class="zs-num">{{ $money($row['sub']) }}</td>
                        <td class="zs-num">{{ $money($row['vat']) }}</td>
                        <td class="zs-num">{{ $money($row['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $cols }}" class="zs-empty">No approved records in this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="zs-total">
                    <td></td>
                    <td colspan="{{ $cols - 4 }}">TOTAL {{ $cur }} AMOUNT ({{ count($section['rows']) }})</td>
                    <td class="zs-num">{{ $money($section['totals']['sub']) }}</td>
                    <td class="zs-num">{{ $money($section['totals']['vat']) }}</td>
                    <td class="zs-num">{{ $money($section['totals']['total']) }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach
@empty
    <p class="zs-empty">No approved invoices, credit notes or bills found for {{ $periodLabel }}.</p>
@endforelse
