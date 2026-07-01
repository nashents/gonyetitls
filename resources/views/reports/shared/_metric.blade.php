{{--
    A single bold gray metric bar (Gross Profit, Net Profit, Net Increase
    in Cash, etc.), with an optional gray sub-line under the label and/or
    the value - e.g. "As a percentage of Total Income" or a percentage.

    Params: label, value, currencyCode, subtext (optional), percentage (optional)
--}}
<div style="margin-top: 18px;">
    <table class="ps-table">
        <tr class="ps-metric">
            <td>
                {{ $label }}
                @isset($subtext)
                <div class="ps-subtext">{{ $subtext }}</div>
                @endisset
            </td>
            <td class="ps-right">
                {{ \App\Services\ReportFormatter::waveMoney($value, $currencyCode) }}
                @isset($percentage)
                <div class="ps-subtext">{{ \App\Services\ReportFormatter::percent($percentage) }}</div>
                @endisset
            </td>
        </tr>
    </table>
</div>
