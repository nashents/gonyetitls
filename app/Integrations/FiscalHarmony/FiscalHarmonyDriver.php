<?php

namespace App\Integrations\FiscalHarmony;

use App\Integrations\Contracts\FiscalDriver;
use App\Models\CompanyIntegration;
use App\Models\FiscalDocument;
use App\Services\FiscalHarmonyService;
use Throwable;

/**
 * Fiscal Harmony (docs v0.2.11) behind the provider-neutral FiscalDriver.
 * HTTP/signing stays in FiscalHarmonyService; this maps the neutral document
 * onto FH's payload and FH's StatusRequest onto a neutral outcome.
 *
 * FH is asynchronous: a submit returns a request ID and the result arrives
 * via POST /status (or the webhook). FH also opens/closes the fiscal day
 * itself, so day control is not ours.
 *
 * Credentials (company integration): base_url (incl. /api), api_key,
 * api_secret, app_name (X-Application), app_station (X-App-Station).
 * Config: app_version, payment_method, timeout. Missing values fall back to
 * the FISCAL_HARMONY_* .env settings.
 */
class FiscalHarmonyDriver implements FiscalDriver
{
    protected FiscalHarmonyService $api;

    public function __construct(CompanyIntegration $integration)
    {
        $credentials = $integration->credentials ?? [];
        $config      = $integration->config ?? [];

        $this->api = new FiscalHarmonyService([
            'api_url'     => $credentials['base_url'] ?? null,
            'api_key'     => $credentials['api_key'] ?? null,
            'api_secret'  => $credentials['api_secret'] ?? null,
            'app_name'    => $credentials['app_name'] ?? null,
            'app_station' => $credentials['app_station'] ?? null,
            'app_version' => $config['app_version'] ?? null,
            'timeout'     => $config['timeout'] ?? 30,
        ]);
    }

    public function providerName(): string
    {
        return 'Fiscal Harmony';
    }

    /** The underlying client, for FH-only tooling (fiscalharmony:check, webhook signatures). */
    public function api(): FiscalHarmonyService
    {
        return $this->api;
    }

    public function testConnection(): array
    {
        if (! $this->api->hasCredentials()) {
            return $this->fail('Fiscal Harmony base URL, API key and API secret are required.');
        }

        $result = $this->api->test();

        return $result['success']
            ? ['success' => true, 'status' => $result['status'], 'data' => $result['data'], 'error' => null]
            : $this->fail($result['error'] ?? 'Fiscal Harmony test failed.', $result['status'] ?? null);
    }

    // ─────────────────────────────────────────────────────────────
    // SUBMIT
    // ─────────────────────────────────────────────────────────────

    public function submitInvoice(array $document): array
    {
        return $this->submitted(fn () => $this->api->submitInvoice([
            'invoiceId'      => $document['externalId'],
            'invoiceNumber'  => $document['number'],
            'reference'      => $document['reference'] ?: null,
            'isTaxInclusive' => false,
            'date'           => $document['date'],
            'currencyCode'   => $document['currencyCode'],
            'paymentMethod'  => $document['paymentMethod'],
            'buyer'          => $document['buyer'],
            'lineItems'      => $this->lineItems($document['lineItems']),
            'subTotal'       => $document['subTotal'],
            'totalTax'       => $document['totalTax'],
            'total'          => $document['total'],
            'isRetry'        => $document['isRetry'],
        ]));
    }

    public function submitCreditNote(array $document): array
    {
        $original = $document['original'] ?? [];

        // FH credits against the InvoiceId it was given, so the invoice must have gone through FH.
        if (($original['provider'] ?? 'fiscal_harmony') !== 'fiscal_harmony' || empty($original['externalId'])) {
            return $this->fail("Invoice {$original['number']} was not fiscalised through Fiscal Harmony, so Fiscal Harmony cannot credit it.");
        }

        return $this->submitted(fn () => $this->api->submitCreditNote([
            'creditNoteId'      => $document['externalId'],
            'originalInvoiceId' => $original['externalId'],
            'creditNoteNumber'  => $document['number'],
            'reference'         => $document['reference'],
            'isTaxInclusive'    => false,
            'date'              => $document['date'],
            'currencyCode'      => $document['currencyCode'],
            'paymentMethod'     => $document['paymentMethod'],
            'buyer'             => $document['buyer'],
            'lineItems'         => $this->lineItems($document['lineItems']),
            'subTotal'          => $document['subTotal'],
            'totalTax'          => $document['totalTax'],
            'total'             => $document['total'],
            'isDiscounted'      => false,
            'isRetry'           => $document['isRetry'],
        ]));
    }

    /** Success body is a plain-text request ID; the outcome is pending until /status says otherwise. */
    protected function submitted(callable $send): array
    {
        try {
            $result = $send();
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }

        if (! $result['success']) {
            return $this->fail($result['error'] ?? ('HTTP ' . $result['status']), $result['status'] ?? null);
        }

        $requestId = is_string($result['data']) ? trim($result['data'], "\" \n\r\t") : null;

        if (! $requestId) {
            return $this->fail('Fiscal Harmony returned no request ID.', $result['status']);
        }

        return $this->outcome('pending', ['request_id' => $requestId, 'raw' => ['requestId' => $requestId]]);
    }

    // ─────────────────────────────────────────────────────────────
    // STATUS
    // ─────────────────────────────────────────────────────────────

    public function fetchStatus(FiscalDocument $doc): array
    {
        if (! $doc->request_id) {
            return $this->fail('No request ID found. Please fiscalise first.');
        }

        $result = $this->api->checkStatus([$doc->request_id]);

        if (! $result['success']) {
            return $this->fail('Could not retrieve status: ' . ($result['error'] ?? 'unknown error'), $result['status'] ?? null);
        }

        $item = collect($result['data'] ?: [])->first(
            fn ($row) => is_array($row) && ($row['requestId'] ?? null) === $doc->request_id
        );

        // /status only knows processed transactions from the last 7 days.
        return $item
            ? $this->fromStatusItem($item, $doc->request_id)
            : $this->outcome('pending', ['request_id' => $doc->request_id]);
    }

    /**
     * Neutral outcome from a camelCase StatusRequest item (from /status or a
     * webhook). IsActionable is deprecated (09 Mar 2026) so it is not used.
     */
    public function fromStatusItem(array $item, ?string $requestId = null): array
    {
        $requestId ??= $item['requestId'] ?? null;

        if (filter_var($item['success'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $qr = is_array($item['qrData'] ?? null) ? $item['qrData'] : [];

            return $this->outcome('approved', [
                'request_id'         => $requestId,
                'qr_code_url'        => $qr['qrCodeUrl'] ?? null,
                'verification_code'  => $qr['verificationCode'] ?? null,
                'fiscal_day'         => $qr['fiscalDay'] ?? null,
                'device_id'          => $qr['deviceId'] ?? null,
                'ra_invoice_number'  => isset($qr['invoiceNumber']) ? (string) $qr['invoiceNumber'] : null,
                'fiscal_invoice_pdf' => $item['fiscalInvoicePdf'] ?? null,
                'raw'                => $item,
            ]);
        }

        $error = $item['error'] ?? null;

        return $this->outcome('failed', [
            'request_id' => $requestId,
            'error'      => (is_array($error) ? json_encode($error) : $error) ?: 'Unknown fiscal error.',
            'raw'        => $item,
        ]);
    }

    public function downloadPdf(string $file): ?string
    {
        return $this->api->downloadPdf($file);
    }

    // ─────────────────────────────────────────────────────────────
    // DEVICE (FH runs the fiscal day)
    // ─────────────────────────────────────────────────────────────

    public function managesFiscalDay(): bool
    {
        return false;
    }

    public function deviceStatus(): array
    {
        $device = $this->api->getFiscalDevice();
        if (! $device['success']) {
            return $this->fail($device['error'] ?? 'Could not read the fiscal device.', $device['status'] ?? null);
        }

        $d = FiscalHarmonyService::camelKeys((array) $device['data']);

        return [
            'success' => true,
            'status'  => $device['status'],
            'data'    => [
                'summary' => trim(($d['taxpayerName'] ?? '') . ' — device ' . ($d['deviceId'] ?? '?') . ' (' . ($d['serialNo'] ?? '?') . '). Fiscal days are opened and closed by Fiscal Harmony.'),
                'details' => $d,
            ],
            'error'   => null,
        ];
    }

    public function openFiscalDay(): array
    {
        return $this->fail('Fiscal Harmony opens and closes the fiscal day itself.');
    }

    public function closeFiscalDay(): array
    {
        return $this->fail('Fiscal Harmony opens and closes the fiscal day itself.');
    }

    // ─────────────────────────────────────────────────────────────
    // INTERNALS
    // ─────────────────────────────────────────────────────────────

    /**
     * TaxCode is our tax abbreviation (e.g. "VAT 15.5%"), which must have a
     * TaxMapping on FH — see `php artisan fiscalharmony:check`.
     */
    protected function lineItems(array $lines): array
    {
        return array_map(fn (array $line) => [
            'description' => $line['description'],
            'taxCode'     => $line['taxCode'],
            'productCode' => $line['productCode'],
            'unitAmount'  => $line['unitAmount'],
            'quantity'    => $line['quantity'],
            'lineAmount'  => $line['lineAmount'],
        ], $lines);
    }

    protected function outcome(string $state, array $fields = []): array
    {
        return [
            'success' => true,
            'status'  => null,
            'data'    => array_merge([
                'state'              => $state,
                'request_id'         => null,
                'qr_code_url'        => null,
                'verification_code'  => null,
                'fiscal_day'         => null,
                'device_id'          => null,
                'ra_invoice_number'  => null,
                'fiscal_invoice_pdf' => null,
                'error'              => null,
                'raw'                => null,
            ], $fields),
            'error'   => null,
        ];
    }

    protected function fail(string $error, $status = null): array
    {
        return ['success' => false, 'status' => $status, 'data' => null, 'error' => $error];
    }
}
