<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;font-family:Arial,sans-serif;">
    <tr>
        <td style="padding:20px;">
            <h3 style="font-size:16px;margin:0 0 12px 0;">Unrecognized Advance Invoices</h3>
            <p style="margin:0 0 16px 0;font-size:14px;line-height:20px;">
                The following approved Advance invoices have been outstanding for {{ $agingDays }}+ days without their linked trip(s) reaching Offloaded status — their revenue is still parked in Customer Advances rather than Sales. Please follow up: chase the trip's delivery, or review whether the invoice needs manual attention.
            </p>
            <table role="presentation" style="width:100%;border-collapse:collapse;border:1px solid #cccccc;font-size:13px;">
                <tr style="background:#f2f2f2;">
                    <th style="padding:8px;border:1px solid #cccccc;text-align:left;">Invoice #</th>
                    <th style="padding:8px;border:1px solid #cccccc;text-align:left;">Customer</th>
                    <th style="padding:8px;border:1px solid #cccccc;text-align:left;">Total</th>
                    <th style="padding:8px;border:1px solid #cccccc;text-align:left;">Invoice Date</th>
                    <th style="padding:8px;border:1px solid #cccccc;text-align:left;">Days Outstanding</th>
                </tr>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td style="padding:8px;border:1px solid #cccccc;">{{ $invoice->invoice_number }}</td>
                        <td style="padding:8px;border:1px solid #cccccc;">{{ $invoice->customer?->name }}</td>
                        <td style="padding:8px;border:1px solid #cccccc;">{{ $invoice->currency?->symbol }}{{ number_format($invoice->total, 2) }}</td>
                        <td style="padding:8px;border:1px solid #cccccc;">{{ $invoice->date }}</td>
                        <td style="padding:8px;border:1px solid #cccccc;">{{ \Carbon\Carbon::parse($invoice->date)->diffInDays(now()) }}</td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
