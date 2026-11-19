{{-- Standard label / value table. Needs $rows (label => value); empty values are skipped. --}}
<table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;margin:6px 0 18px 0;">
    @foreach ($rows as $label => $value)
        @if ($value !== null && $value !== '')
            <tr>
                <td style="padding:7px 10px;border-bottom:1px solid #eef0f3;color:#6b7480;white-space:nowrap;vertical-align:top;width:38%;">{{ $label }}</td>
                <td style="padding:7px 10px;border-bottom:1px solid #eef0f3;color:#153643;">{{ $value }}</td>
            </tr>
        @endif
    @endforeach
</table>
