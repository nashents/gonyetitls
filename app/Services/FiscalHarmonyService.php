<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FiscalHarmonyService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $appName;
    protected string $appStation;

    public function __construct()
    {
        $this->apiUrl     = rtrim(config('fiscalharmony.api_url'), '/');
        $this->apiKey     = config('fiscalharmony.api_key');
        $this->apiSecret  = config('fiscalharmony.api_secret');
        $this->appName    = config('fiscalharmony.app_name');
        $this->appStation = config('fiscalharmony.app_station');
    }

    // ─────────────────────────────────────────────────────────────
    // HEADERS
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
        ];

        if ($jsonPayload !== null) {
            $headers['X-Api-Signature'] = $this->sign($jsonPayload);
        }

        return $headers;
    }

    /**
     * HMACSHA256 + Base64 signature of the JSON payload.
     */
    protected function sign(string $payload): string
    {
        $hash = hash_hmac('sha256', $payload, base64_decode($this->apiSecret), true);
        return base64_encode($hash);
    }

    // ─────────────────────────────────────────────────────────────
    // HTTP HELPERS
    // ─────────────────────────────────────────────────────────────

    protected function get(string $path): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(15)
            ->get("{$this->apiUrl}{$path}");

        return $this->handle($response, 'GET', $path);
    }

    protected function post(string $path, array $data): array
    {
        $json     = json_encode($data);
        $response = Http::withHeaders($this->headers($json))
            ->timeout(15)
            ->post("{$this->apiUrl}{$path}", $data);

        return $this->handle($response, 'POST', $path);
    }

    protected function put(string $path, array $data): array
    {
        $json     = json_encode($data);
        $response = Http::withHeaders($this->headers($json))
            ->timeout(15)
            ->put("{$this->apiUrl}{$path}", $data);

        return $this->handle($response, 'PUT', $path);
    }

    protected function delete(string $path): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(15)
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

        $error = $response->json('message') ?? $response->body();
        Log::error("FiscalHarmony [{$method} {$path}] HTTP {$status}: {$error}");

        return [
            'success' => false,
            'status'  => $status,
            'error'   => $error,
            'data'    => null,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // ACCOUNT / DEVICE
    // ─────────────────────────────────────────────────────────────

    public function getProfile(): array
    {
        return $this->get('/profile');
    }

    public function getFiscalDevice(): array
    {
        return $this->get('/fiscaldevice');
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

        $currentConfig = $result['data']['currentConfig'] ?? null;

        if (is_string($currentConfig)) {
            $config = json_decode($currentConfig, true);
        } elseif (is_array($currentConfig)) {
            $config = $currentConfig;
        } else {
            return [];
        }

        return $config['applicableTaxes'] ?? [];
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
     *   date:            string  ISO-8601 e.g. "2025-04-01T10:00:00+02:00",
     *   currencyCode:    string  e.g. "USD",
     *   paymentMethod:   string  Cash|Card|MobileWallet|Coupon|Credit|BankTransfer|Other,
     *   buyer: {
     *     name:       string,
     *     tradeName?: string,
     *     tin?:       string,
     *     vatNumber?: string,
     *     phone?:     string,
     *     email?:     string,
     *     address?: { houseNo, street, city, province }
     *   },
     *   lineItems: [{
     *     description:  string,
     *     taxCode:      string,
     *     productCode:  string  (HsCode – mandatory as of v0.2.6),
     *     unitAmount:   float,
     *     quantity:     float,
     *     lineAmount:   float,
     *     discountAmount?: float
     *   }],
     *   subTotal?:      float,
     *   totalTax?:      float,
     *   total?:         float,
     *   isDiscounted?:  bool,
     *   isRetry?:       bool,
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
     *   originalInvoiceId:  string,
     *   creditNoteNumber:   string,
     *   reference:          string,
     *   isTaxInclusive:     bool,
     *   date:               string  ISO-8601,
     *   currencyCode:       string,
     *   paymentMethod:      string,
     *   buyer:              array  (same structure as invoice),
     *   lineItems:          array  (same structure as invoice),
     *   subTotal?:          float,
     *   totalTax?:          float,
     *   total?:             float,
     *   isRetry?:           bool
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
            'BuyerContact'      => $this->buildBuyerContact($creditNote['buyer']),
            'Date'              => $creditNote['date'],
            'LineItems'         => $this->buildLineItems($creditNote['lineItems']),
            'PaymentMethod'     => $creditNote['paymentMethod'],
            'CurrencyCode'      => $creditNote['currencyCode'],
        ];

        foreach (['subTotal' => 'SubTotal', 'totalTax' => 'TotalTax', 'total' => 'Total', 'isRetry' => 'IsRetry'] as $k => $v) {
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
     * Check transaction statuses by requestId list (max 100).
     *
     * @param  string[]  $requestIds
     */
    public function checkStatus(array $requestIds): array
    {
        return $this->post('/status', $requestIds);
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
            ->timeout(30)
            ->get("{$this->apiUrl}/download/{$filename}");

        if ($response->successful()) {
            return $response->body();
        }

        Log::error("FiscalHarmony PDF download failed [{$filename}]: HTTP {$response->status()}");
        return null;
    }

    // ─────────────────────────────────────────────────────────────
    // CUSTOMISATION
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
            'BuyerContact'   => $this->buildBuyerContact($invoice['buyer']),
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

    protected function buildBuyerContact(array $buyer): array
    {
        $contact = ['Name' => $buyer['name']];

        $optionals = [
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

        return $contact;
    }

    protected function buildLineItems(array $lineItems): array
    {
        return array_map(function (array $item) {
            $line = [
                'Description' => $item['description'],
                'TaxCode'     => $item['taxCode'],
                'ProductCode' => $item['productCode'],   // HsCode – mandatory v0.2.6+
                'UnitAmount'  => $item['unitAmount'],
                'Quantity'    => $item['quantity'],
                'LineAmount'  => $item['lineAmount'],
            ];

            if (isset($item['discountAmount']) && $item['discountAmount'] > 0) {
                $line['DiscountAmount'] = $item['discountAmount'];
            }

            return $line;
        }, $lineItems);
    }
}
