<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\FiscalDocument;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Fiscalises invoices and credit notes through Fiscal Harmony and keeps
 * fiscal_documents in step. Shared by every invoice template, the credit
 * note preview and the FH webhook so the rules live in one place.
 *
 * Every public method returns ['type' => success|warning|error, 'message' => string].
 */
class FiscalisationService
{
    public function __construct(protected FiscalHarmonyService $api)
    {
    }

    // ─────────────────────────────────────────────────────────────
    // INVOICES
    // ─────────────────────────────────────────────────────────────

    public function fiscaliseInvoice(Invoice $invoice): array
    {
        $invoice->loadMissing(['invoice_items.tax', 'invoice_items.product', 'customer', 'currency']);

        $existing = FiscalDocument::forInvoice($invoice->id)->latest()->first();
        if ($guard = $this->guardExisting($existing, 'invoice')) {
            return $guard;
        }

        [$externalId, $isRetry] = $this->attemptIdentity($existing);

        $lines = $invoice->invoice_items->map(fn ($item) => $this->lineItem(
            $item->description ?: ($item->product?->name ?? 'Service'),
            $this->taxFor($item),
            $item->hs_code,
            $item->amount,
            $item->qty,
            $item->subtotal
        ))->all();

        $payload = [
            'invoiceId'      => $externalId,
            'invoiceNumber'  => $invoice->invoice_number,
            'reference'      => $invoice->sales_order_number ?: ($invoice->purchase_order_number ?: null),
            'isTaxInclusive' => false,
            'date'           => $this->fiscalDate($invoice->date),
            'currencyCode'   => $this->currencyCode($invoice->currency?->name),
            'paymentMethod'  => $this->paymentMethod(),
            'buyer'          => $this->buyer($invoice->customer),
            'lineItems'      => $lines,
            'subTotal'       => round((float) $invoice->invoice_items->sum('subtotal'), 2),
            'totalTax'       => round((float) ($invoice->tax_amount ?? 0), 2),
            'total'          => round((float) ($invoice->total ?? 0), 2),
            'isRetry'        => $isRetry,
        ];

        if ($problem = $this->validateLines($lines)) {
            return $this->result('error', $problem);
        }

        $doc = FiscalDocument::updateOrCreate(
            ['document_type' => 'invoice', 'source_id' => $invoice->id],
            $this->pendingAttributes($externalId, $invoice->invoice_number, $payload)
        );

        return $this->submit($doc, fn () => $this->api->submitInvoice($payload), 'Invoice');
    }

    // ─────────────────────────────────────────────────────────────
    // CREDIT NOTES
    // ─────────────────────────────────────────────────────────────

    public function fiscaliseCreditNote(CreditNote $creditNote): array
    {
        $creditNote->loadMissing(['credit_note_items.tax', 'customer', 'currency', 'invoice']);

        if (! $creditNote->invoice) {
            return $this->result('error', 'This credit note is not linked to an invoice, so it cannot be fiscalised.');
        }

        // FH credits against the InvoiceId we sent when the invoice was fiscalised.
        $invoiceDoc = FiscalDocument::forInvoice($creditNote->invoice_id)->latest()->first();
        if (! $invoiceDoc?->isApproved()) {
            return $this->result('error', "Invoice {$creditNote->invoice->invoice_number} must be fiscalised before its credit note.");
        }

        $existing = FiscalDocument::forCreditNote($creditNote->id)->latest()->first();
        if ($guard = $this->guardExisting($existing, 'credit note')) {
            return $guard;
        }

        [$externalId, $isRetry] = $this->attemptIdentity($existing);

        $lines = $creditNote->credit_note_items->map(fn ($item) => $this->lineItem(
            $item->description ?: ($item->item ?: 'Credit'),
            $this->taxFor($item),
            null,
            $item->amount,
            $item->qty,
            $item->subtotal
        ))->all();

        $payload = [
            'creditNoteId'      => $externalId,
            'originalInvoiceId' => $invoiceDoc->external_document_id,
            'creditNoteNumber'  => $creditNote->credit_note_number,
            'reference'         => $creditNote->credit_note_reason
                                    ?: ($creditNote->reason ?: "Credit for invoice {$creditNote->invoice->invoice_number}"),
            'isTaxInclusive'    => false,
            'date'              => $this->fiscalDate($creditNote->date),
            'currencyCode'      => $this->currencyCode($creditNote->currency?->name ?? $creditNote->invoice->currency?->name),
            'paymentMethod'     => $this->paymentMethod(),
            'buyer'             => $this->buyer($creditNote->customer ?? $creditNote->invoice->customer),
            'lineItems'         => $lines,
            'subTotal'          => round((float) $creditNote->credit_note_items->sum('subtotal'), 2),
            'totalTax'          => round((float) ($creditNote->tax_amount ?? 0), 2),
            'total'             => round((float) ($creditNote->total ?? 0), 2),
            'isDiscounted'      => false,
            'isRetry'           => $isRetry,
        ];

        if ($problem = $this->validateLines($lines)) {
            return $this->result('error', $problem);
        }

        $doc = FiscalDocument::updateOrCreate(
            ['document_type' => 'credit_note', 'source_id' => $creditNote->id],
            $this->pendingAttributes($externalId, $creditNote->credit_note_number, $payload)
        );

        return $this->submit($doc, fn () => $this->api->submitCreditNote($payload), 'Credit note');
    }

    // ─────────────────────────────────────────────────────────────
    // STATUS (manual poll + webhook)
    // ─────────────────────────────────────────────────────────────

    /** Pull the latest status for one document from POST /status. */
    public function refresh(FiscalDocument $doc): array
    {
        if (! $doc->request_id) {
            return $this->result('error', 'No request ID found. Please fiscalise first.');
        }

        $result = $this->api->checkStatus([$doc->request_id]);

        if (! $result['success']) {
            return $this->result('warning', 'Could not retrieve status: ' . ($result['error'] ?? 'unknown error') . '. Try again shortly.');
        }

        $item = collect($result['data'] ?: [])->first(
            fn ($row) => is_array($row) && ($row['requestId'] ?? null) === $doc->request_id
        );

        if (! $item) {
            // /status only knows processed transactions from the last 7 days.
            return $this->result('warning', 'Fiscal Harmony has not finished processing this document yet. Check again shortly.');
        }

        return $this->applyStatus($doc, $item);
    }

    /**
     * Record a camelCase StatusRequest item (from /status or a webhook).
     * IsActionable is deprecated (09 Mar 2026) so it is no longer used.
     */
    public function applyStatus(FiscalDocument $doc, array $item): array
    {
        $label = $doc->document_type === 'credit_note' ? 'Credit note' : 'Invoice';

        if (filter_var($item['success'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $qr = is_array($item['qrData'] ?? null) ? $item['qrData'] : [];

            $doc->update([
                'success'            => true,
                'is_actionable'      => null,
                'error_message'      => null,
                'fiscal_invoice_pdf' => $item['fiscalInvoicePdf'] ?? $doc->fiscal_invoice_pdf,
                'qr_code_url'        => $qr['qrCodeUrl']        ?? null,
                'verification_code'  => $qr['verificationCode'] ?? null,
                'fiscal_day'         => $qr['fiscalDay']        ?? null,
                'device_id'          => $qr['deviceId']         ?? null,
                'ra_invoice_number'  => isset($qr['invoiceNumber']) ? (string) $qr['invoiceNumber'] : null,
                'response_payload'   => $item,
                'fiscalized_at'      => $doc->fiscalized_at ?? now(),
            ]);

            if ($doc->document_type === 'invoice') {
                // Heading switches to "FISCAL TAX INVOICE".
                Invoice::where('id', $doc->source_id)->update(['fiscalize' => true]);
            }

            return $this->result('success', "{$label} fiscalised successfully!");
        }

        $error = $item['error'] ?? null;
        $error = is_array($error) ? json_encode($error) : $error;

        $doc->update([
            'success'          => false,
            'is_actionable'    => null,
            'error_message'    => $error ?: 'Unknown fiscal error.',
            'response_payload' => $item,
        ]);

        return $this->result('error', ($error ?: 'Unknown fiscal error.') . ' — correct the document and retry.');
    }

    // ─────────────────────────────────────────────────────────────
    // INTERNALS
    // ─────────────────────────────────────────────────────────────

    protected function submit(FiscalDocument $doc, callable $send, string $label): array
    {
        try {
            $result = $send();
        } catch (\Throwable $e) {
            Log::error("FiscalHarmony submit failed for {$doc->document_type} #{$doc->source_id}: {$e->getMessage()}");
            $doc->update(['success' => false, 'error_message' => $e->getMessage()]);
            return $this->result('error', 'Error: ' . $e->getMessage());
        }

        if (! $result['success']) {
            $err = $result['error'] ?? ('HTTP ' . $result['status']);
            $doc->update(['success' => false, 'error_message' => $err]);
            return $this->result('error', 'Submission failed: ' . $err);
        }

        // Success body is a plain-text request ID.
        $requestId = is_string($result['data']) ? trim($result['data'], "\" \n\r\t") : null;

        if (! $requestId) {
            $doc->update(['success' => false, 'error_message' => 'Fiscal Harmony returned no request ID.', 'response_payload' => ['raw' => $result['data']]]);
            return $this->result('error', 'Fiscal Harmony returned no request ID.');
        }

        $doc->update(['request_id' => $requestId, 'response_payload' => ['requestId' => $requestId]]);

        $status = $this->refresh($doc->fresh());

        return $status['type'] === 'warning'
            ? $this->result('warning', "{$label} submitted to Fiscal Harmony and is being processed. Use Check Fiscal Status in a moment.")
            : $status;
    }

    /** Block double submission; returns a result when we must not submit. */
    protected function guardExisting(?FiscalDocument $existing, string $label): ?array
    {
        if (! $existing) {
            return null;
        }

        if ($existing->isApproved()) {
            return $this->result('warning', "This {$label} has already been fiscalised.");
        }

        if ($existing->isPending()) {
            // Submitted before — resolve it rather than risk a duplicate fiscal document.
            $status = $this->refresh($existing);
            return $status['type'] === 'warning'
                ? $this->result('warning', "This {$label} is still being processed by Fiscal Harmony. Check its status again shortly.")
                : $status;
        }

        return null;
    }

    /**
     * A document FH already accepted (has a request ID) is retried under the
     * same InvoiceId/CreditNoteId with IsRetry; otherwise start a fresh ID.
     */
    protected function attemptIdentity(?FiscalDocument $existing): array
    {
        if ($existing && $existing->isFailed() && $existing->request_id) {
            return [$existing->external_document_id, true];
        }

        return [(string) Str::uuid(), false];
    }

    protected function pendingAttributes(string $externalId, ?string $number, array $payload): array
    {
        return [
            'external_document_id' => $externalId,
            'document_number'      => $number,
            'request_id'           => null,
            'success'              => null,
            'is_actionable'        => null,
            'error_message'        => null,
            'request_payload'      => $payload,
            'response_payload'     => null,
            'fiscalized_at'        => null,
        ];
    }

    /**
     * TaxCode is our tax abbreviation (e.g. "VAT 15.5%"), which must have a
     * TaxMapping on FH — see `php artisan fiscalharmony:check`.
     * HS code: the line's own, else its tax's.
     */
    protected function lineItem(string $description, $tax, ?string $hsCode, $unit, $qty, $lineAmount): array
    {
        return [
            'description' => Str::limit(trim($description), 200, ''),
            'taxCode'     => $tax?->abbreviation ?: '',
            'productCode' => trim((string) ($hsCode ?: $tax?->hs_code)),
            'unitAmount'  => round((float) ($unit ?? 0), 2),
            'quantity'    => (float) ($qty ?: 1),
            'lineAmount'  => round((float) ($lineAmount ?? 0), 2),
        ];
    }

    /**
     * Lines point at the taxes table; older lines stored a Sales Taxes
     * account id instead, which carries the same abbreviation/hs_code fields.
     */
    protected function taxFor($item)
    {
        return $item->tax ?? ($item->tax_id ? Account::find($item->tax_id) : null);
    }

    protected function validateLines(array $lines): ?string
    {
        if (empty($lines)) {
            return 'There are no line items to fiscalise.';
        }

        $missing = [];
        foreach ($lines as $i => $line) {
            $gaps = [];
            if ($line['taxCode'] === '') {
                $gaps[] = 'tax';
            }
            if ($line['productCode'] === '') {
                $gaps[] = 'HS code';
            }
            if ($gaps) {
                $missing[] = 'line ' . ($i + 1) . ' (' . Str::limit($line['description'], 30) . '): no ' . implode(' or ', $gaps);
            }
        }

        return $missing
            ? 'Cannot fiscalise — every line needs a tax and an HS code: ' . implode('; ', $missing) . '.'
            : null;
    }

    /** ISO 8601 in CAT (GMT+2), as the docs require. */
    protected function fiscalDate($date): string
    {
        return Carbon::parse($date ?: now(), 'Africa/Harare')->setTimezone('Africa/Harare')->toIso8601String();
    }

    protected function currencyCode(?string $name): string
    {
        $code    = strtoupper(trim($name ?: 'USD'));
        $aliases = config('fiscalharmony.currency_aliases', []);

        return $aliases[$code] ?? $code;
    }

    protected function paymentMethod(): string
    {
        return config('fiscalharmony.payment_method', 'Credit');
    }

    protected function buyer(?Customer $customer): array
    {
        if (! $customer) {
            return [];
        }

        $buyer = [
            'name'      => $customer->name,
            'tin'       => $customer->tin_number,
            'vatNumber' => $customer->vat_number,
            'phone'     => $customer->phonenumber,
            'email'     => $customer->email,
        ];

        // Address is optional, but if sent all four fields must be filled.
        $streetAddress = trim((string) $customer->street_address);
        $city          = trim((string) $customer->city);

        if ($streetAddress !== '' && $city !== '') {
            // "12 Samora Machel Ave" → house 12, street "Samora Machel Ave".
            if (preg_match('/^\s*(\d+[A-Za-z]?)[\s,]+(.+)$/', $streetAddress, $m)) {
                [$houseNo, $street] = [$m[1], $m[2]];
            } else {
                [$houseNo, $street] = ['N/A', $streetAddress];
            }

            $suburb = trim((string) $customer->suburb);

            $buyer['address'] = [
                'houseNo'  => $houseNo,
                'street'   => trim($street . ($suburb !== '' ? ", {$suburb}" : '')),
                'city'     => $city,
                // Customers have no province field; in Zimbabwe the city is the best available proxy.
                'province' => $city,
            ];
        }

        return $buyer;
    }

    protected function result(string $type, string $message): array
    {
        return ['type' => $type, 'message' => $message];
    }
}
