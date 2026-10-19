<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Thin HTTP client for the Fiscal Harmony API (docs v0.2.11,
 * https://docs.fiscalharmony.co.zw/). Orchestration — payload building,
 * fiscal_documents bookkeeping — lives in FiscalisationService.
 */
class FiscalHarmonyService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $appName;
    protected string $appStation;
    protected string $appVersion;
    protected int $timeout;

    /**
     * $settings comes from the company's Fiscal Harmony integration (see
     * FiscalHarmonyDriver); any key left out falls back to config/fiscalharmony.php
     * (.env), which is also what a container-resolved instance uses.
     */
    public function __construct(array $settings = [])
    {
        $get = fn (string $key, $default = '') => ($settings[$key] ?? '') !== '' && ($settings[$key] ?? null) !== null
            ? $settings[$key]
            : config("fiscalharmony.{$key}", $default);

        $this->apiUrl     = rtrim((string) $get('api_url'), '/');
        $this->apiKey     = (string) $get('api_key');
        $this->apiSecret  = (string) $get('api_secret');
        $this->appName    = (string) $get('app_name');
        $this->appStation = (string) $get('app_station');
        $this->appVersion = (string) $get('app_version', '1');
        $this->timeout    = (int) ($settings['timeout'] ?? 30) ?: 30;
    }

    public function apiUrl(): string
    {
        return $this->apiUrl;
    }

    public function hasCredentials(): bool
    {
        return $this->apiUrl !== '' && $this->apiKey !== '' && $this->apiSecret !== '';
    }

    // ─────────────────────────────────────────────────────────────
    // HEADERS / SIGNING
    // ─────────────────────────────────────────────────────────────

    /**
     * Build required headers. For GET requests pass null as payload.
     */
    protected function headers(?string $jsonPayload = null): array
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'X-Api-Key'     => $this->apiKey,
            'X-Application' => $this->appName,
            'X-App-Station' => $this->appStation,
            'X-App-Version' => $this->appVersion,
        ];

        if ($jsonPayload !== null) {
            $headers['X-Api-Signature'] = $this->sign($jsonPayload);
        }

        return $headers;
    }

    /**
     * HMACSHA256 + Base64 signature of the JSON payload, keyed with the API
     * secret string exactly as issued — NOT Base64-decoded, even though it
     * looks like Base64. Verified against apitest 2026-10-19: decoded key → 401.
     * (/test and /status don't enforce signatures; /taxmapping etc. do.)
     */
    protected function sign(string $payload): string
    {
        $hash = hash_hmac('sha256', $payload, $this->apiSecret, true);
        return base64_encode($hash);
    }

    /**
     * Verify an inbound webhook's X-Api-Signature against the raw body.
     * FH signs with the API secret as-is (as we sign outbound); the decoded
     * form is still accepted in case webhooks differ.
     */
    public function verifySignature(string $rawBody, ?string $signature): bool
    {
        if (! $signature || $this->apiSecret === '') {
            return false;
        }

        foreach ([$this->apiSecret, base64_decode($this->apiSecret, true)] as $key) {
            if ($key === false || $key === '') {
                continue;
            }
            $expected = base64_encode(hash_hmac('sha256', $rawBody, $key, true));
            if (hash_equals($expected, trim($signature))) {
                return true;
            }
        }

        return false;
    }

    /**
     * FH responds in PascalCase (RequestId, QrData, FiscalInvoicePdf…).
     * Normalise keys recursively to camelCase so callers have one shape.
     */
    public static function camelKeys($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        $out = [];
        foreach ($data as $key => $value) {
            $out[is_string($key) ? lcfirst($key) : $key] = static::camelKeys($value);
        }

        return $out;
    }

    // ─────────────────────────────────────────────────────────────
    // HTTP HELPERS
    // ─────────────────────────────────────────────────────────────

    protected function get(string $path): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout($this->timeout)
            ->get("{$this->apiUrl}{$path}");

        return $this->handle($response, 'GET', $path);
    }

    /**
     * The exact signed bytes are sent as the body so the signature always
     * matches what FH receives.
     */
    protected function post(string $path, array $data): array
    {
        $json     = json_encode($data);
        $response = Http::withHeaders($this->headers($json))
            ->timeout($this->timeout)
            ->withBody($json, 'application/json')
            ->post("{$this->apiUrl}{$path}");

        return $this->handle($response, 'POST', $path);
    }

    protected function put(string $path, array $data): array
    {
        $json     = json_encode($data);
        $response = Http::withHeaders($this->headers($json))
            ->timeout($this->timeout)
            ->withBody($json, 'application/json')
            ->put("{$this->apiUrl}{$path}");

        return $this->handle($response, 'PUT', $path);
    }

    protected function delete(string $path): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout($this->timeout)
            ->delete("{$this->apiUrl}{$path}");

        return $this->handle($response, 'DELETE', $path);
    }

    protected function handle($response, string $method, string $path): array
    {
        $status = $response->status();

        if ($response->successful()) {
            $body = $response->body();
            return [
                'success' => true,
                'status'  => $status,
                'data'    => $response->json() ?? $body,
            ];
        }

        $error = $response->json('Message') ?? $response->json('message') ?? $response->body();

        // ASP.NET validation problem: {"title": ..., "errors": {"Field": ["msg"]}}
        $fieldErrors = $response->json('errors');
        if (is_array($fieldErrors) && $fieldErrors) {
            $error = collect($fieldErrors)
                ->map(fn ($messages, $field) => implode(' ', (array) $messages))
                ->implode('; ');
        }

        if (is_array($error)) {
            $error = json_encode($error);
        }
        if ($error === '' || $error === null) {
            $error = $this->describeStatus($status);
        }
        Log::error("FiscalHarmony [{$method} {$path}] HTTP {$status}: {$error}");

        return [
            'success' => false,
            'status'  => $status,
            'error'   => $error,
            'data'    => null,
        ];
    }

    /** Meanings from the docs' error table, for empty-bodied failures. */
    protected function describeStatus(int $status): string
    {
        return match (true) {
            $status === 400 => 'HTTP 400: invalid data sent',
            $status === 401 => 'HTTP 401: wrong API key or request not signed correctly',
            $status === 403 => 'HTTP 403: invalid application name (X-Application) or rate-limit ban',
            $status === 404 => 'HTTP 404: resource unavailable',
            $status === 429 => 'HTTP 429: rate limited — try again shortly',
            $status === 522 => 'HTTP 522: Fiscal Harmony is down or under maintenance',
            $status >= 500  => "HTTP {$status}: revenue authority (ZIMRA) not reachable",
            default         => "HTTP {$status}",
        };
    }

    // ─────────────────────────────────────────────────────────────
    // ACCOUNT / DEVICE
    // ─────────────────────────────────────────────────────────────

    /** POST /test — checks the credentials before real POSTs. */
    public function test(): array
    {
        return $this->post('/test', ['Date' => now('Africa/Harare')->format('Y-m-d\TH:i:s')]);
    }

    /**
     * Prove our signing on an endpoint that enforces it. /test does NOT check
     * signatures, so POST an empty TaxMapping instead: the signature is
     * checked before validation, so a good signature gets 400 (empty
     * TaxCode, nothing created) and a bad one gets 401.
     *
     * @return array{verified: ?bool, ours: int, wrong: int}  verified null = inconclusive
     */
    public function testSignatureEnforcement(): array
    {
        $json = '{}';

        // Bypasses handle(): the expected 400/401 are not errors worth logging.
        $send = fn (string $signature) => Http::withHeaders(array_merge($this->headers($json), ['X-Api-Signature' => $signature]))
            ->timeout($this->timeout)
            ->withBody($json, 'application/json')
            ->post("{$this->apiUrl}/taxmapping")
            ->status();

        $wrong = $send(base64_encode(hash_hmac('sha256', $json, 'deliberately-wrong-secret', true)));
        $ours  = $send($this->sign($json));

        $verified = null;
        if ($wrong === 401) {
            $verified = $ours === 400 ? true : ($ours === 401 ? false : null);
        }

        return ['verified' => $verified, 'ours' => $ours, 'wrong' => $wrong];
    }

    public function getProfile(): array
    {
        return $this->get('/profile');
    }

    public function getFiscalDevice(): array
    {
        return $this->get('/fiscaldevice');
    }

    public function getSubscription(): array
    {
        return $this->get('/subscription');
    }

    /**
     * Fetch applicable taxes from the fiscal device's CurrentConfig.
     * Returns the applicableTaxes array or an empty array on failure.
     */
    public function getApplicableTaxes(): array
    {
        $result = $this->getFiscalDevice();

        if (! $result['success']) {
            return [];
        }

        $currentConfig = $result['data']['CurrentConfig'] ?? $result['data']['currentConfig'] ?? null;

        if (is_string($currentConfig)) {
            $config = json_decode($currentConfig, true);
        } elseif (is_array($currentConfig)) {
            $config = $currentConfig;
        } else {
            return [];
        }

        return static::camelKeys($config)['applicableTaxes'] ?? [];
    }

    // ─────────────────────────────────────────────────────────────
    // TAX MAPPINGS
    // ─────────────────────────────────────────────────────────────

    public function getTaxMappings(): array
    {
        return $this->get('/taxmapping');
    }

    public function getTaxMapping(int $id): array
    {
        return $this->get("/taxmapping/{$id}");
    }

    public function createTaxMapping(string $taxCode, int $destinationTaxId, string $taxName = ''): array
    {
        $payload = [
            'TaxCode'          => $taxCode,
            'DestinationTaxId' => $destinationTaxId,
        ];
        if ($taxName) {
            $payload['TaxName'] = $taxName;
        }
        return $this->post('/taxmapping', $payload);
    }

    public function updateTaxMapping(int $id, string $taxCode, int $destinationTaxId, string $taxName = ''): array
    {
        $payload = [
            'Id'               => $id,
            'TaxCode'          => $taxCode,
            'DestinationTaxId' => $destinationTaxId,
        ];
        if ($taxName) {
            $payload['TaxName'] = $taxName;
        }
        return $this->put("/taxmapping/{$id}", $payload);
    }

    public function deleteTaxMapping(int $id): array
    {
        return $this->delete("/taxmapping/{$id}");
    }

    // ─────────────────────────────────────────────────────────────
    // CURRENCY MAPPINGS
    // ─────────────────────────────────────────────────────────────

    public function getSupportedCurrencies(): array
    {
        return $this->get('/currencymapping/supported-currencies');
    }

    public function getCurrencyMappings(): array
    {
        return $this->get('/currencymapping');
    }

    public function getCurrencyMapping(int $id): array
    {
        return $this->get("/currencymapping/{$id}");
    }

    public function createCurrencyMapping(string $sourceCurrency, string $destinationCurrency): array
    {
        return $this->post('/currencymapping', [
            'SourceCurrency'      => $sourceCurrency,
            'DestinationCurrency' => $destinationCurrency,
        ]);
    }

    public function updateCurrencyMapping(int $id, string $sourceCurrency, string $destinationCurrency): array
    {
        return $this->put("/currencymapping/{$id}", [
            'Id'                  => $id,
            'SourceCurrency'      => $sourceCurrency,
            'DestinationCurrency' => $destinationCurrency,
        ]);
    }

    public function deleteCurrencyMapping(int $id): array
    {
        return $this->delete("/currencymapping/{$id}");
    }

    // ─────────────────────────────────────────────────────────────
    // SUBMIT INVOICE
    // ─────────────────────────────────────────────────────────────

    /**
     * Submit a fiscalised invoice.
     *
     * @param  array  $invoice  {
     *   invoiceId:       string (GUID),
     *   invoiceNumber:   string,
     *   isTaxInclusive:  bool,
     *   date:            string  ISO-8601 CAT e.g. "2025-04-01T10:00:00+02:00",
     *   currencyCode:    string  e.g. "USD",
     *   paymentMethod:   string  Cash|Card|MobileWallet|Coupon|Credit|BankTransfer|Other,
     *   buyer?: {
     *     name?, tradeName?, tin?, vatNumber?, phone?, email?,
     *     address?: { houseNo, street, city, province }   (all four required if present)
     *   },
     *   lineItems: [{
     *     description:  string,
     *     taxCode:      string  (must have a TaxMapping on FH),
     *     productCode:  string  (HsCode – mandatory since 01 Apr 2025),
     *     unitAmount:   float,
     *     quantity:     float,
     *     lineAmount:   float,
     *     discountAmount?: float
     *   }],
     *   subTotal?, totalTax?, total?: float,
     *   isDiscounted?, isRetry?: bool,
     *   reference?:     string
     * }
     * @return array  { success, status, data (requestId string) }
     */
    public function submitInvoice(array $invoice): array
    {
        $payload = $this->buildInvoicePayload($invoice);
        return $this->post('/invoice', $payload);
    }

    // ─────────────────────────────────────────────────────────────
    // SUBMIT CREDIT NOTE
    // ─────────────────────────────────────────────────────────────

    /**
     * Submit a credit note.
     *
     * @param  array  $creditNote  {
     *   creditNoteId:       string (GUID),
     *   originalInvoiceId:  string  (the InvoiceId sent when the invoice was fiscalised),
     *   creditNoteNumber:   string,
     *   reference:          string,
     *   isTaxInclusive:     bool,
     *   date:               string  ISO-8601,
     *   currencyCode:       string,
     *   paymentMethod:      string,
     *   buyer?:             array  (same structure as invoice),
     *   lineItems:          array  (same structure as invoice),
     *   subTotal?, totalTax?, total?: float,
     *   isDiscounted?, isRetry?: bool
     * }
     */
    public function submitCreditNote(array $creditNote): array
    {
        $payload = [
            'CreditNoteId'      => $creditNote['creditNoteId'],
            'OriginalInvoiceId' => $creditNote['originalInvoiceId'],
            'CreditNoteNumber'  => $creditNote['creditNoteNumber'],
            'Reference'         => $creditNote['reference'],
            'IsTaxInclusive'    => $creditNote['isTaxInclusive'],
            'BuyerContact'      => $this->buildBuyerContact($creditNote['buyer'] ?? []),
            'Date'              => $creditNote['date'],
            'LineItems'         => $this->buildLineItems($creditNote['lineItems']),
            'PaymentMethod'     => $creditNote['paymentMethod'],
            'CurrencyCode'      => $creditNote['currencyCode'],
        ];

        foreach (['subTotal' => 'SubTotal', 'totalTax' => 'TotalTax', 'total' => 'Total', 'isDiscounted' => 'IsDiscounted', 'isRetry' => 'IsRetry'] as $k => $v) {
            if (isset($creditNote[$k])) {
                $payload[$v] = $creditNote[$k];
            }
        }

        return $this->post('/creditnote', $payload);
    }

    // ─────────────────────────────────────────────────────────────
    // STATUS CHECK
    // ─────────────────────────────────────────────────────────────

    /**
     * Check transaction statuses by requestId list (max 100, last 7 days only).
     * `data` comes back as a list of camelCase StatusRequest items:
     * requestId, success, qrData{qrCodeUrl, verificationCode, fiscalDay, deviceId,
     * invoiceNumber, invoiceCounter}, error, fiscalInvoicePdf.
     *
     * @param  string[]  $requestIds
     */
    public function checkStatus(array $requestIds): array
    {
        $result = $this->post('/status', array_values(array_slice($requestIds, 0, 100)));

        if ($result['success'] && is_array($result['data'])) {
            $result['data'] = static::camelKeys($result['data']);
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────
    // DOWNLOAD PDF
    // ─────────────────────────────────────────────────────────────

    /**
     * Download a fiscal PDF. Returns raw PDF bytes or null on failure.
     */
    public function downloadPdf(string $filename): ?string
    {
        $response = Http::withHeaders($this->headers())
            ->timeout($this->timeout)
            ->get("{$this->apiUrl}/download/{$filename}");

        if ($response->successful()) {
            return $response->body();
        }

        Log::error("FiscalHarmony PDF download failed [{$filename}]: HTTP {$response->status()}");
        return null;
    }

    // ─────────────────────────────────────────────────────────────
    // CUSTOMISATION
    // (no longer in the v0.2.11 docs, but still live on the API)
    // ─────────────────────────────────────────────────────────────

    public function getCustomisation(): array
    {
        return $this->get('/customisation');
    }

    public function saveCustomisation(array $data): array
    {
        return $this->post('/customisation', $data);
    }

    public function deleteCustomisation(): array
    {
        return $this->delete('/customisation');
    }

    public function getPrintLayouts(): array
    {
        return $this->get('/customisation/layout');
    }

    // ─────────────────────────────────────────────────────────────
    // INTERNAL BUILDERS
    // ─────────────────────────────────────────────────────────────

    protected function buildInvoicePayload(array $invoice): array
    {
        $payload = [
            'InvoiceId'      => $invoice['invoiceId'],
            'InvoiceNumber'  => $invoice['invoiceNumber'],
            'IsTaxInclusive' => $invoice['isTaxInclusive'],
            'BuyerContact'   => $this->buildBuyerContact($invoice['buyer'] ?? []),
            'Date'           => $invoice['date'],
            'LineItems'      => $this->buildLineItems($invoice['lineItems']),
            'PaymentMethod'  => $invoice['paymentMethod'],
            'CurrencyCode'   => $invoice['currencyCode'],
        ];

        $optionals = [
            'reference'    => 'Reference',
            'subTotal'     => 'SubTotal',
            'totalTax'     => 'TotalTax',
            'total'        => 'Total',
            'isDiscounted' => 'IsDiscounted',
            'isRetry'      => 'IsRetry',
        ];

        foreach ($optionals as $key => $apiKey) {
            if (isset($invoice[$key])) {
                $payload[$apiKey] = $invoice[$key];
            }
        }

        return $payload;
    }

    /**
     * Since 11 Jun 2026 every BuyerContact field is optional. Address may be
     * omitted, but when present all four of its fields are required.
     */
    protected function buildBuyerContact(array $buyer): ?array
    {
        $contact = [];

        $optionals = [
            'name'      => 'Name',
            'tradeName' => 'TradeName',
            'tin'       => 'Tin',
            'vatNumber' => 'VatNumber',
            'phone'     => 'Phone',
            'email'     => 'Email',
        ];

        foreach ($optionals as $key => $apiKey) {
            if (! empty($buyer[$key])) {
                $contact[$apiKey] = $buyer[$key];
            }
        }

        if (! empty($buyer['address'])) {
            $contact['Address'] = [
                'HouseNo'  => $buyer['address']['houseNo']  ?? '',
                'Street'   => $buyer['address']['street']   ?? '',
                'City'     => $buyer['address']['city']     ?? '',
                'Province' => $buyer['address']['province'] ?? '',
            ];
        }

        return $contact ?: null;
    }

    protected function buildLineItems(array $lineItems): array
    {
        return array_map(function (array $item) {
            $line = [
                'Description' => $item['description'],
                'TaxCode'     => $item['taxCode'],
                'ProductCode' => $item['productCode'],   // HsCode – mandatory since 01 Apr 2025
                'UnitAmount'  => $item['unitAmount'],
                'Quantity'    => $item['quantity'],
                'LineAmount'  => $item['lineAmount'],
            ];

            if (isset($item['discountAmount']) && $item['discountAmount'] > 0) {
                $line['DiscountAmount'] = $item['discountAmount'];
            }

            return $line;
        }, array_values($lineItems));
    }
}
