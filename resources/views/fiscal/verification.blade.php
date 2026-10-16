{{--
    ZIMRA FDMS verification block for printed / PDF invoices and credit notes.
    With a device provider (Revmax) our own document is the fiscal document,
    so it must carry the QR code and verification code. Renders nothing until
    the document is fiscalised.
    Usage: @include('fiscal.verification', ['fiscalDocument' => $doc])
--}}
@if (! empty($fiscalDocument) && $fiscalDocument->success && ($fiscalDocument->qr_code_url || $fiscalDocument->verification_code))
    @php $fiscalQr = \App\Support\FiscalQr::dataUri($fiscalDocument->qr_code_url); @endphp
    <table style="width:100%; margin-top:10px; border:1px solid #ccc;">
        <tr>
            @if ($fiscalQr)
                <td style="width:130px; vertical-align:top; padding:6px; border:none; background:#fff;">
                    <img src="{{ $fiscalQr }}" alt="Fiscal QR Code" width="110" height="110">
                </td>
            @endif
            <td style="vertical-align:top; padding:6px; border:none; text-align:left; background:#fff; font-size:11px;">
                <div><strong>FISCAL VERIFICATION</strong></div>
                @if ($fiscalDocument->verification_code)
                    <div><strong>Verification Code:</strong> {{ $fiscalDocument->verification_code }}</div>
                @endif
                @if ($fiscalDocument->ra_invoice_number)
                    <div><strong>Fiscal Receipt No:</strong> {{ $fiscalDocument->ra_invoice_number }}</div>
                @endif
                @if ($fiscalDocument->fiscal_day)
                    <div><strong>Fiscal Day:</strong> {{ $fiscalDocument->fiscal_day }}</div>
                @endif
                @if ($fiscalDocument->device_id)
                    <div><strong>Fiscal Device ID:</strong> {{ $fiscalDocument->device_id }}</div>
                @endif
                @if ($fiscalDocument->fiscalized_at)
                    <div><strong>Fiscalised:</strong> {{ $fiscalDocument->fiscalized_at->format('d M Y H:i') }}</div>
                @endif
                @if ($fiscalDocument->qr_code_url)
                    <div style="word-break:break-all;">Verify at: {{ $fiscalDocument->qr_code_url }}</div>
                @endif
            </td>
        </tr>
    </table>
@endif
