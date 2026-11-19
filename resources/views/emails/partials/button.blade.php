{{-- Standard email call-to-action button. Needs $url, $label. --}}
@php $btnColor = (isset($company) && !empty($company->color)) ? $company->color : '#123bdc'; @endphp
<table role="presentation" style="border-collapse:collapse;margin:18px 0 6px 0;">
    <tr>
        <td style="border-radius:4px;background:{{ $btnColor }};">
            <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:12px 24px;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;font-family:Arial,sans-serif;">{{ $label }}</a>
        </td>
    </tr>
</table>
<p style="margin:6px 0 0 0;font-size:12px;line-height:18px;color:#777777;word-break:break-all;">
    Or open: <a href="{{ $url }}" target="_blank" style="color:#1a56db;">{{ $url }}</a>
</p>
