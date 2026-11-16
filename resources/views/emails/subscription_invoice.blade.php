<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;font-family:Arial,sans-serif;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 12px 0;font-size:14px;line-height:20px;">Good day{{ $recipientName ? ' ' . $recipientName : '' }},</p>

            @if ($type === 'reminder')
                <p style="margin:0 0 16px 0;font-size:14px;line-height:20px;">
                    @if ($invoice->isOverdue())
                        This is a reminder that invoice <strong>{{ $invoice->invoice_number }}</strong> for your Gonyeti TLS license was due on
                        <strong>{{ $invoice->due_date->format('d M Y') }}</strong> and we have not yet recorded payment in full.
                    @else
                        This is a reminder that invoice <strong>{{ $invoice->invoice_number }}</strong> for your Gonyeti TLS license is due on
                        <strong>{{ $invoice->due_date->format('d M Y') }}</strong>.
                    @endif
                </p>
            @else
                <p style="margin:0 0 16px 0;font-size:14px;line-height:20px;">
                    Please find attached invoice <strong>{{ $invoice->invoice_number }}</strong> for your Gonyeti TLS license,
                    covering {{ $invoice->period_start->format('d M Y') }} to {{ $invoice->period_end->format('d M Y') }}.
                </p>
            @endif

            <table role="presentation" style="border-collapse:collapse;border:1px solid #cccccc;font-size:13px;margin:0 0 16px 0;">
                <tr>
                    <td style="padding:8px 12px;border:1px solid #cccccc;background:#f2f2f2;">Company</td>
                    <td style="padding:8px 12px;border:1px solid #cccccc;">{{ $invoice->company?->name }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 12px;border:1px solid #cccccc;background:#f2f2f2;">Package</td>
                    <td style="padding:8px 12px;border:1px solid #cccccc;">{{ $invoice->company?->plan_label }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 12px;border:1px solid #cccccc;background:#f2f2f2;">Invoice amount</td>
                    <td style="padding:8px 12px;border:1px solid #cccccc;">{{ $invoice->money($invoice->amount) }}</td>
                </tr>
                @if ($invoice->amount_paid > 0)
                    <tr>
                        <td style="padding:8px 12px;border:1px solid #cccccc;background:#f2f2f2;">Paid to date</td>
                        <td style="padding:8px 12px;border:1px solid #cccccc;">{{ $invoice->money($invoice->amount_paid) }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:8px 12px;border:1px solid #cccccc;background:#f2f2f2;"><strong>Amount due</strong></td>
                    <td style="padding:8px 12px;border:1px solid #cccccc;"><strong>{{ $invoice->money($invoice->balance) }}</strong></td>
                </tr>
                <tr>
                    <td style="padding:8px 12px;border:1px solid #cccccc;background:#f2f2f2;">Due date</td>
                    <td style="padding:8px 12px;border:1px solid #cccccc;">{{ $invoice->due_date->format('d M Y') }}</td>
                </tr>
            </table>

            <p style="margin:0 0 16px 0;font-size:14px;line-height:20px;">
                The invoice is attached as a PDF. You can also see all your invoices and payments in Gonyeti TLS under
                Business Settings &gt; Billing &amp; Subscription.
                @if ($type === 'reminder') If you have already paid, please send us the proof of payment so we can update your account. @endif
            </p>

            <p style="margin:0;font-size:14px;line-height:20px;">
                Regards,<br>
                {{ $issuer?->name ?? 'Gonyeti TLS' }}
                @if ($issuer?->phonenumber)<br>{{ $issuer->phonenumber }}@endif
                @if ($issuer?->email)<br>{{ $issuer->email }}@endif
            </p>
        </td>
    </tr>
</table>
