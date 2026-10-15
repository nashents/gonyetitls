<?php

namespace App\Integrations\Contracts;

use App\Models\FiscalDocument;

/**
 * A virtual fiscalisation provider (Fiscal Harmony, Revmax, ...). Every
 * provider is configured once as a company integration of type "fiscal";
 * FiscalisationService builds one provider-neutral document and hands it to
 * whichever fiscal integration is active, so templates/previews never know
 * which provider is behind them.
 *
 * Neutral document (built by FiscalisationService):
 *   type             invoice|credit_note
 *   externalId       our GUID for this attempt (FH InvoiceId/CreditNoteId)
 *   number           invoice / credit note number
 *   reference        PO/SO reference (invoice) or the credit reason (credit note)
 *   date             ISO-8601 CAT
 *   currencyCode     e.g. USD, ZWG
 *   paymentMethod    Cash|Card|MobileWallet|Coupon|Credit|BankTransfer|Other
 *   buyer            [name, tin, vatNumber, phone, email, address?[houseNo, street, city, province]]
 *   lineItems        [[description, taxCode, taxRate, taxExempt, productCode, unitAmount, quantity, lineAmount]]
 *                    (tax-exclusive, positive amounts — drivers apply credit-note signs)
 *   subTotal, totalTax, total, isRetry
 *   credit notes:    original => [externalId, number, provider, companyIntegrationId,
 *                                 deviceId, fiscalDay, raInvoiceNumber]
 *
 * Submit/status methods return the usual ['success','status','data','error']
 * shape where `data` is an outcome:
 *   state               approved|pending|failed|not_found (status lookup: provider has no record)
 *   request_id          provider tracking id (async providers), else null
 *   qr_code_url, verification_code, fiscal_day, device_id,
 *   ra_invoice_number, fiscal_invoice_pdf, error, raw
 * `success` = false means the call itself failed (transport/auth/validation
 * rejected before a fiscal outcome); the error is in `error`.
 */
interface FiscalDriver extends IntegrationDriver
{
    /** Human name for messages, e.g. "Fiscal Harmony". */
    public function providerName(): string;

    public function submitInvoice(array $document): array;

    public function submitCreditNote(array $document): array;

    /**
     * Latest outcome for a document already sent. Returns success=true with
     * state "pending" when the provider has not finished, and success=false
     * when it cannot tell (e.g. nothing to look it up by).
     */
    public function fetchStatus(FiscalDocument $doc): array;

    /** Provider-generated fiscal PDF bytes, or null if the provider has none. */
    public function downloadPdf(string $file): ?string;

    /** True when the device's fiscal day is opened/closed by us (Revmax), false when the provider runs it (FH). */
    public function managesFiscalDay(): bool;

    /** Device / fiscal day status: data = [summary => string, details => array]. */
    public function deviceStatus(): array;

    public function openFiscalDay(): array;

    public function closeFiscalDay(): array;
}
