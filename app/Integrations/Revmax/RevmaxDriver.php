<?php

namespace App\Integrations\Revmax;

use App\Integrations\Contracts\FiscalDriver;
use App\Models\CompanyIntegration;
use App\Models\FiscalDocument;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Revmax — Axis Solutions' virtual fiscal device web service (Axis.VirtualDevice,
 * docs "Virtual Webservice v1.0.0" + VirtualDeviceLib v2.3.0). One web service
 * instance = one ZIMRA virtual device, so there is no device id to send.
 *
 * Confirmed against the dev instances 2026-10-15 (swagger at {base}/swagger/v1/swagger.json):
 *  - Auth is optional per deployment. :10005 is open; :10007 needs a Bearer JWT
 *    from POST /api/Auth/login {email,password} → {"token": "..."} (24h).
 *    A bad login is HTTP 401 with a plain-text body; a missing token is a bare 401.
 *  - Every call answers HTTP 200 with {Code: "1"|"0", Message, QRCode,
 *    FDMSInvoiceNo, FiscalDayNo, VerificationCode, DeviceID, Data}; failure is
 *    Code "0", so the body decides success, not the status code.
 *  - GET /GetInvoice/{no} for an unknown number returned a DIFFERENT receipt
 *    with Code "1", so every lookup is checked against Data.receipt.invoiceNo.
 *  - Submission is synchronous: the device signs the receipt and queues it
 *    for FDMS ("Fiscal Invoice Queued"), returning the QR/verification code.
 *  - Credit notes are tax-exclusive with negative price/total and positive
 *    quantity (a live CreditNote on the test device was sent that way).
 *  - The day is ours to run: OpenFiscalDay / CloseFiscalDay are GETs.
 *
 * Credentials: base_url (e.g. http://host:10007), optional email + password.
 * Config: payment_method, receipt_print_form, tax codes, auto day handling, timeout.
 * Secrets are never logged.
 */
class RevmaxDriver implements FiscalDriver
{
    protected const API = '/api/VirtualDevice';

    protected array $credentials;
    protected array $config;
    protected string $baseUrl;
    protected int $timeout;
    protected ?int $integrationId;

    public function __construct(CompanyIntegration $integration)
    {
        $this->credentials   = $integration->credentials ?? [];
        $this->config        = $integration->config ?? [];
        $this->integrationId = $integration->id;
        $this->timeout       = (int) ($this->config['timeout'] ?? 30) ?: 30;

        // Accept the swagger URL or a trailing /api — we only want the host root.
        $this->baseUrl = preg_replace('#/(swagger.*|api)?/*$#i', '', trim((string) ($this->credentials['base_url'] ?? '')));
    }

    public function providerName(): string
    {
        return 'Revmax';
    }

    public function testConnection(): array
    {
        return $this->deviceStatus();
    }

    // ─────────────────────────────────────────────────────────────
    // SUBMIT
    // ─────────────────────────────────────────────────────────────

    public function submitInvoice(array $document): array
    {
        $payload = $this->receipt($document, 'FiscalInvoice', 1);
        $payload['referenceNumber'] = '';
        $payload['receiptNotes']    = $document['reference']
            ? "Invoice {$document['number']} — Ref {$document['reference']}"
            : "Invoice {$document['number']}";

        return $this->submit('/SubmitReceipt', $payload, $document['number']);
    }

    public function submitCreditNote(array $document): array
    {
        $original = $document['original'] ?? [];

        if (empty($original['number'])) {
            return $this->fail('The credited invoice has no fiscal record to reference.');
        }

        $payload = $this->receipt($document, 'CreditNote', -1);
        $payload['referenceNumber'] = (string) $original['number'];
        // Mandatory for credit/debit notes.
        $payload['receiptNotes'] = Str::limit((string) ($document['reference'] ?: "Credit for invoice {$original['number']}"), 250, '');

        // Invoice fiscalised on another device (another integration / provider):
        // SubmitReceiptExt references it by device, global receipt no and fiscal day.
        $sameDevice = ($original['provider'] ?? null) === 'revmax'
            && (int) ($original['companyIntegrationId'] ?? 0) === (int) $this->integrationId;

        if (! $sameDevice) {
            if (empty($original['deviceId']) || empty($original['raInvoiceNumber']) || empty($original['fiscalDay'])) {
                return $this->fail("Invoice {$original['number']} was fiscalised on another device and its device ID, receipt number or fiscal day is unknown, so it cannot be credited here.");
            }

            $payload += [
                'refDeviceID'            => (string) $original['deviceId'],
                'refReceiptGlobalnumber' => (string) $original['raInvoiceNumber'],
                'refFiscalDay'           => (string) $original['fiscalDay'],
            ];

            return $this->submit('/SubmitReceiptExt', $payload, $document['number']);
        }

        return $this->submit('/SubmitReceipt', $payload, $document['number']);
    }

    /**
     * Submit, healing the two fiscal-day states the device rejects with
     * (closed / past taxPayerDayMaxHrs) when the config allows, and recovering
     * a receipt that a previous, lost response already recorded.
     */
    protected function submit(string $path, array $payload, string $number, bool $healed = false): array
    {
        $result = $this->request('POST', self::API . $path, $payload);

        if ($result['success']) {
            return $this->outcomeFromEnvelope($result['data'], $number);
        }

        $error = (string) $result['error'];

        if (! $healed && stripos($error, 'already exists') !== false) {
            $found = $this->lookup($number);
            if ($found && $found['success'] && $found['data']['state'] === 'approved') {
                return $found;
            }
        }

        if (! $healed && stripos($error, 'Fiscal Day is currently closed') !== false && $this->flag('auto_open_fiscal_day')) {
            $open = $this->openFiscalDay();
            return $open['success']
                ? $this->submit($path, $payload, $number, true)
                : $this->fail("{$error} Opening a new fiscal day also failed: {$open['error']}");
        }

        if (! $healed && stripos($error, 'maximum hours') !== false && $this->flag('auto_rollover_fiscal_day')) {
            $close = $this->closeFiscalDay();
            $open  = $close['success'] ? $this->openFiscalDay() : $close;
            return $open['success']
                ? $this->submit($path, $payload, $number, true)
                : $this->fail("{$error} Rolling the fiscal day over failed: {$open['error']}");
        }

        return $result;
    }

    /** SubmitReceipt body; $sign is -1 for credit notes. */
    protected function receipt(array $document, string $type, int $sign): array
    {
        $buyer   = $document['buyer'] ?? [];
        $address = $buyer['address'] ?? [];

        $lines = [];
        foreach (array_values($document['lineItems']) as $i => $line) {
            [$taxCode, $taxPercent] = $this->taxFor($line);

            $lines[] = [
                'receiptLineType'     => 'Sale',
                'receiptLineNo'       => $i + 1,
                'receiptLineHSCode'   => (string) $line['productCode'],
                'receiptLineName'     => Str::limit((string) $line['description'], 200, ''),
                'receiptLinePrice'    => round($sign * (float) $line['unitAmount'], 2),
                'receiptLineQuantity' => (float) $line['quantity'],
                'receiptLineTotal'    => round($sign * (float) $line['lineAmount'], 2),
                'taxCode'             => $taxCode,
                'taxPercent'          => $taxPercent,
            ];
        }

        return [
            'receiptType'              => $type,
            'receiptCurrency'          => $document['currencyCode'],
            'invoiceNo'                => Str::limit((string) $document['number'], 50, ''),
            // Lines are tax-exclusive, so the device expects lines + tax here.
            'invoiceAmount'            => round($sign * (float) $document['total'], 2),
            'invoiceTaxAmount'         => round($sign * (float) $document['totalTax'], 2),
            'receiptLinesTaxInclusive' => false,
            'moneyTypeCode'            => $this->config['payment_method'] ?? $document['paymentMethod'] ?? 'Credit',
            'receiptPrintForm'         => $this->config['receipt_print_form'] ?? 'InvoiceA4',
            'buyerRegisterName'        => Str::limit((string) ($buyer['name'] ?? ''), 250, ''),
            'buyerTradeName'           => Str::limit((string) ($buyer['tradeName'] ?? $buyer['name'] ?? ''), 250, ''),
            'vatNumber'                => (string) ($buyer['vatNumber'] ?? ''),
            'buyerTIN'                 => (string) ($buyer['tin'] ?? ''),
            'buyerPhoneNo'             => (string) ($buyer['phone'] ?? ''),
            'buyerEmail'               => (string) ($buyer['email'] ?? ''),
            'buyerProvince'            => (string) ($address['province'] ?? ''),
            'buyerStreet'              => (string) ($address['street'] ?? ''),
            'buyerHouseNo'             => (string) ($address['houseNo'] ?? ''),
            'buyerCity'                => (string) ($address['city'] ?? ''),
            'receiptLines'             => $lines,
        ];
    }

    /**
     * Device tax codes: A = VAT-able (taxPercent = our rate, e.g. 15.5),
     * B = zero rated. Exempt's code isn't in the docs — configurable, default C.
     */
    protected function taxFor(array $line): array
    {
        if (! empty($line['taxExempt'])) {
            return [$this->config['tax_code_exempt'] ?? 'C', 0];
        }

        $rate = round((float) ($line['taxRate'] ?? 0), 2);

        return $rate > 0
            ? [$this->config['tax_code_standard'] ?? 'A', $rate]
            : [$this->config['tax_code_zero_rated'] ?? 'B', 0];
    }

    // ─────────────────────────────────────────────────────────────
    // STATUS
    // ─────────────────────────────────────────────────────────────

    public function fetchStatus(FiscalDocument $doc): array
    {
        if (! $doc->document_number) {
            return $this->fail('This document has no number to look up on the fiscal device.');
        }

        return $this->lookup($doc->document_number);
    }

    /** GetInvoice, trusting the result only when it is the receipt we asked for. */
    protected function lookup(string $number): array
    {
        $result = $this->request('GET', self::API . '/GetInvoice/' . rawurlencode($number));

        if (! $result['success']) {
            // Code 0 on an HTTP 200 is the device answering "no such receipt";
            // anything else is a transport/auth failure the caller should see.
            return $result['status'] === 200
                ? $this->outcome('not_found', ['error' => $result['error']])
                : $result;
        }

        $returned = $result['data']['Data']['receipt']['invoiceNo'] ?? null;

        if ($returned === null || strcasecmp(trim((string) $returned), trim($number)) !== 0) {
            return $this->outcome('not_found', ['error' => "Receipt {$number} is not on the fiscal device."]);
        }

        return $this->outcomeFromEnvelope($result['data'], $number);
    }

    protected function outcomeFromEnvelope(array $env, string $number): array
    {
        $qr       = trim((string) ($env['QRCode'] ?? ''));
        $globalNo = trim((string) ($env['FDMSInvoiceNo'] ?? ''));
        $deviceId = trim((string) ($env['DeviceID'] ?? ''));

        // Drop the device signature — it's large and only meaningful to FDMS.
        unset($env['Data']['receipt']['receiptDeviceSignature']);

        if ($qr === '' && $globalNo === '') {
            return $this->outcome('pending', ['request_id' => "revmax:{$number}", 'raw' => $env]);
        }

        return $this->outcome('approved', [
            'request_id'        => $deviceId !== '' && $globalNo !== '' ? "{$deviceId}-{$globalNo}" : "revmax:{$number}",
            'qr_code_url'       => $qr ?: null,
            'verification_code' => trim((string) ($env['VerificationCode'] ?? '')) ?: null,
            'fiscal_day'        => ($env['FiscalDayNo'] ?? '') !== '' ? (int) $env['FiscalDayNo'] : null,
            'device_id'         => $deviceId !== '' ? (int) $deviceId : null,
            'ra_invoice_number' => $globalNo ?: null,
            'raw'               => $env,
        ]);
    }

    /** The device makes our invoice the fiscal document; it has no PDF of its own. */
    public function downloadPdf(string $file): ?string
    {
        return null;
    }

    // ─────────────────────────────────────────────────────────────
    // DEVICE / FISCAL DAY
    // ─────────────────────────────────────────────────────────────

    public function managesFiscalDay(): bool
    {
        return true;
    }

    public function deviceStatus(): array
    {
        $status = $this->request('GET', self::API . '/GetStatus');
        if (! $status['success']) {
            return $status;
        }

        $env  = $status['data'];
        $data = $env['Data'] ?? [];
        $day  = $data['lastFiscalDayNo'] ?? ($env['FiscalDayNo'] ?? '?');

        $summary = sprintf(
            'Device %s (%s): %s, fiscal day %s, last receipt %s.',
            $env['DeviceID'] ?? '?',
            $env['DeviceSerialNumber'] ?? '?',
            $this->humanDayStatus($data['fiscalDayStatus'] ?? 'unknown'),
            $day,
            $data['lastReceiptGlobalNo'] ?? '?'
        );

        $config = $this->request('GET', self::API . '/GetConfig');
        if ($config['success']) {
            $c = $config['data']['Data'] ?? [];
            $summary .= sprintf(' Taxpayer %s, TIN %s, VAT %s.', $c['taxPayerName'] ?? '?', $c['taxPayerTIN'] ?? '?', $c['vatNumber'] ?? '?');
        }

        // Receipts signed by the device but not yet accepted by ZIMRA's FDMS.
        if (is_numeric($day)) {
            $queue = $this->request('GET', self::API . '/GetUnProcessedInvoicesSummary', ['fiscalDayNumber' => (string) $day]);
            $count = $queue['success'] ? ($queue['data']['Data']['TotalUnprocessedTransactions'] ?? null) : null;
            if ($count !== null) {
                $summary .= " {$count} receipt(s) still queued for ZIMRA.";
            }
        }

        return [
            'success' => true,
            'status'  => $status['status'],
            'data'    => [
                'summary' => $summary,
                'details' => [
                    'status' => $data,
                    'config' => $config['success'] ? ($config['data']['Data'] ?? []) : null,
                ],
            ],
            'error'   => null,
        ];
    }

    public function openFiscalDay(): array
    {
        return $this->dayAction('/OpenFiscalDay', 'opened');
    }

    public function closeFiscalDay(): array
    {
        return $this->dayAction('/CloseFiscalDay', 'closed');
    }

    protected function dayAction(string $path, string $verb): array
    {
        $result = $this->request('GET', self::API . $path);
        if (! $result['success']) {
            return $result;
        }

        $data = $result['data']['Data'] ?? [];
        $day  = $data['fiscalDayNo'] ?? $data['FiscalDayNumber'] ?? ($result['data']['FiscalDayNo'] ?? null);

        Log::info("Revmax fiscal day {$verb}", ['integration' => $this->integrationId, 'day' => $day]);

        return [
            'success' => true,
            'status'  => $result['status'],
            'data'    => [
                'summary' => 'Fiscal day ' . ($day ?: '') . " {$verb}. " . ($result['data']['Message'] ?? ''),
                'details' => $data,
            ],
            'error'   => null,
        ];
    }

    protected function humanDayStatus(string $status): string
    {
        return trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $status)) ?: $status;
    }

    // ─────────────────────────────────────────────────────────────
    // HTTP
    // ─────────────────────────────────────────────────────────────

    /** Envelope-aware request: success only when Code is "1". Retries once with a fresh token on 401. */
    protected function request(string $method, string $path, array $data = [], bool $retried = false): array
    {
        if ($this->baseUrl === '') {
            return $this->fail('Revmax base URL is not configured.');
        }

        $token = $this->token();
        if (is_array($token)) {
            return $token; // login failure
        }

        try {
            $http = Http::acceptJson()->timeout($this->timeout);
            if ($token) {
                $http = $http->withToken($token);
            }

            $response = $method === 'POST'
                ? $http->post($this->baseUrl . $path, $data)
                : $http->get($this->baseUrl . $path, $data);
        } catch (Throwable $e) {
            Log::error("Revmax [{$method} {$path}] transport error: {$e->getMessage()}");
            return $this->fail('Could not reach the Revmax virtual device: ' . $e->getMessage());
        }

        if ($response->status() === 401 && $token && ! $retried) {
            $this->forgetToken();
            return $this->request($method, $path, $data, true);
        }

        return $this->parse($response, $method, $path);
    }

    protected function parse(Response $response, string $method, string $path): array
    {
        $body = $response->json();

        if (! is_array($body)) {
            $text  = trim((string) $response->body());
            $error = match ($response->status()) {
                401     => 'Revmax rejected the request — check the email and password (this web service requires a login).',
                404     => "Revmax endpoint not found ({$path}) — check the base URL.",
                default => $text !== '' ? Str::limit($text, 300) : "Revmax request failed (HTTP {$response->status()}).",
            };
            Log::error("Revmax [{$method} {$path}] HTTP {$response->status()}: {$error}");
            return $this->fail($error, $response->status());
        }

        if ((string) ($body['Code'] ?? '') !== '1') {
            $error = (string) ($body['Message'] ?? $body['message'] ?? $body['title'] ?? "Revmax request failed (HTTP {$response->status()}).");
            Log::warning("Revmax [{$method} {$path}] Code " . ($body['Code'] ?? '?') . ": {$error}");
            return $this->fail($error, $response->status());
        }

        return ['success' => true, 'status' => $response->status(), 'data' => $body, 'error' => null];
    }

    /** Bearer token when email/password are configured; null for an open web service; a fail array if login fails. */
    protected function token()
    {
        $email    = trim((string) ($this->credentials['email'] ?? ''));
        $password = (string) ($this->credentials['password'] ?? '');

        if ($email === '' || $password === '') {
            return null;
        }

        if ($cached = Cache::get($this->tokenKey())) {
            return $cached;
        }

        try {
            $response = Http::acceptJson()->timeout($this->timeout)
                ->post($this->baseUrl . '/api/Auth/login', ['email' => $email, 'password' => $password]);
        } catch (Throwable $e) {
            return $this->fail('Could not reach the Revmax login: ' . $e->getMessage());
        }

        $token = $response->json('token') ?? $response->json('Token');

        if (! $response->successful() || ! is_string($token) || $token === '') {
            $text = trim((string) $response->body());
            return $this->fail('Revmax login failed: ' . ($text !== '' && ! is_array($response->json()) ? Str::limit($text, 200) : "HTTP {$response->status()}"), $response->status());
        }

        // Tokens live 24h; refresh a little early.
        Cache::put($this->tokenKey(), $token, now()->addHours(23));

        return $token;
    }

    protected function tokenKey(): string
    {
        return 'revmax:token:' . md5($this->baseUrl . '|' . ($this->credentials['email'] ?? ''));
    }

    protected function forgetToken(): void
    {
        Cache::forget($this->tokenKey());
    }

    protected function flag(string $key): bool
    {
        return filter_var($this->config[$key] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
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
