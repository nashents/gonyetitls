<?php

namespace App\Integrations\SageIntacct;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Throwable;

/**
 * Sage Intacct XML Web Services driver (legacy API — the proven default).
 *
 * Auth is session-less per call: the sender (web services partner) credentials
 * go in <control>, the company/user credentials in <operation><authentication>.
 * Sage processes the <create>/<update> in the same request, so no separate
 * login round-trip is needed.
 *
 * Secrets are NEVER logged: only parsed error descriptions and status codes are.
 */
class SageXmlDriver implements SageDriver
{
    protected array $credentials;
    protected array $config;
    protected string $endpoint;
    protected int $timeout;

    public function __construct(CompanyIntegration $integration)
    {
        // credentials is an encrypted:array cast on the model.
        $this->credentials = $integration->credentials ?? [];
        $this->config      = $integration->config ?? [];

        // Endpoint may be overridden per company via config; else global default.
        $this->endpoint = $this->config['base_url']
            ?? config('sageintacct.xml.endpoint');

        $this->timeout = (int) ($this->config['timeout'] ?? config('sageintacct.timeout', 30));
    }

    // ─────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────

    public function testConnection(): array
    {
        // A lightweight read proves the credentials are valid. getAPISession is
        // the canonical "am I authenticated" call.
        return $this->send('<getAPISession/>', 'test');
    }

    public function createCustomer(array $customer): array
    {
        $fn = '<create>' . $this->buildContact('CUSTOMER', 'CUSTOMERID', $customer, true) . '</create>';
        return $this->send($fn, 'create', 'CUSTOMER');
    }

    public function updateCustomer(string $sageId, array $customer): array
    {
        $customer['id'] = $sageId; // ensure the key element targets the record
        $fn = '<update>' . $this->buildContact('CUSTOMER', 'CUSTOMERID', $customer, false) . '</update>';
        return $this->send($fn, 'update', 'CUSTOMER');
    }

    public function createVendor(array $vendor): array
    {
        $fn = '<create>' . $this->buildContact('VENDOR', 'VENDORID', $vendor, true) . '</create>';
        return $this->send($fn, 'create', 'VENDOR');
    }

    public function updateVendor(string $sageId, array $vendor): array
    {
        $vendor['id'] = $sageId;
        $fn = '<update>' . $this->buildContact('VENDOR', 'VENDORID', $vendor, false) . '</update>';
        return $this->send($fn, 'update', 'VENDOR');
    }

    // ─────────────────────────────────────────────────────────────
    // PAYLOAD BUILDERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Build a CUSTOMER / VENDOR element from the service's generic field array.
     * Customers and vendors share the DISPLAYCONTACT structure, so this is reused.
     *
     * @param  bool  $isCreate  on create, NAME + the id element are always sent.
     */
    protected function buildContact(string $object, string $idField, array $data, bool $isCreate): string
    {
        $fields = '';

        // The unique id. On create it may be omitted to let Sage document
        // sequencing assign one, but Gonyeti always supplies its own.
        if (! empty($data['id'])) {
            $fields .= $this->el($idField, $data['id']);
        }

        if ($isCreate || isset($data['name'])) {
            $fields .= $this->el('NAME', $data['name'] ?? '');
        }

        // DISPLAYCONTACT (PRINTAS is mandatory on the contact).
        $contact  = $this->el('PRINTAS', $data['name'] ?? '');
        $contact .= $this->elIf('EMAIL1', $data['email'] ?? null);
        $contact .= $this->elIf('PHONE1', $data['phone'] ?? null);

        $address  = $this->elIf('ADDRESS1', $data['address1'] ?? null);
        $address .= $this->elIf('CITY', $data['city'] ?? null);
        $address .= $this->elIf('COUNTRY', $data['country'] ?? null);
        if ($address !== '') {
            $contact .= '<MAILADDRESS>' . $address . '</MAILADDRESS>';
        }
        $fields .= '<DISPLAYCONTACT>' . $contact . '</DISPLAYCONTACT>';

        // ADJUST: map these to the client's Sage configuration as needed.
        $fields .= $this->elIf('TAXID', $data['taxid'] ?? null);
        $fields .= $this->elIf('CURRENCY', $data['currency'] ?? null);
        $fields .= $this->elIf('STATUS', $data['status'] ?? null);

        return "<{$object}>{$fields}</{$object}>";
    }

    // ─────────────────────────────────────────────────────────────
    // HTTP + ENVELOPE
    // ─────────────────────────────────────────────────────────────

    /**
     * Wrap a function body in the full request envelope, POST it, and parse.
     */
    protected function send(string $functionBody, string $action, ?string $object = null): array
    {
        $controlId = (config('sageintacct.xml.control_id', 'gonyeti')) . '-' . uniqid();

        $xml = $this->envelope($functionBody, $controlId);

        try {
            $response = Http::timeout($this->timeout)
                ->withBody($xml, 'application/xml')
                ->post($this->endpoint);
        } catch (Throwable $e) {
            // Network / transport failure — log without any payload (no secrets).
            Log::error("SageIntacct XML [{$action} {$object}] transport error: " . $e->getMessage());
            return $this->fail(null, 'Could not reach Sage Intacct: ' . $e->getMessage());
        }

        return $this->parse($response->body(), $response->status(), $action, $object);
    }

    protected function envelope(string $functionBody, string $controlId): string
    {
        $senderId   = $this->credentials['sender_id'] ?? '';
        $senderPass = $this->credentials['sender_password'] ?? '';
        $userId     = $this->credentials['user_id'] ?? '';
        $companyId  = $this->credentials['company_id'] ?? '';
        $userPass   = $this->credentials['user_password'] ?? '';
        $dtd        = config('sageintacct.xml.dtd_version', '3.0');

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<request>'
            .   '<control>'
            .     $this->el('senderid', $senderId)
            .     $this->el('password', $senderPass)
            .     $this->el('controlid', $controlId)
            .     '<uniqueid>false</uniqueid>'
            .     $this->el('dtdversion', $dtd)
            .     '<includewhitespace>false</includewhitespace>'
            .   '</control>'
            .   '<operation>'
            .     '<authentication><login>'
            .       $this->el('userid', $userId)
            .       $this->el('companyid', $companyId)
            .       $this->el('password', $userPass)
            .     '</login></authentication>'
            .     '<content><function controlid="f1">' . $functionBody . '</function></content>'
            .   '</operation>'
            . '</request>';
    }

    /**
     * Parse a Sage XML response into the normalised result shape.
     */
    protected function parse(string $body, int $httpStatus, string $action, ?string $object): array
    {
        if ($body === '') {
            return $this->fail($httpStatus, 'Empty response from Sage Intacct.');
        }

        try {
            $xml = new SimpleXMLElement($body);
        } catch (Throwable $e) {
            Log::error("SageIntacct XML [{$action} {$object}] unparseable response (HTTP {$httpStatus}).");
            return $this->fail($httpStatus, 'Unexpected response from Sage Intacct.');
        }

        // Control-level failure (bad sender credentials / malformed request).
        $controlStatus = (string) ($xml->control->status ?? '');
        if ($controlStatus === 'failure') {
            return $this->fail($httpStatus, $this->extractError($xml));
        }

        // Authentication failure (bad company/user credentials).
        $authStatus = (string) ($xml->operation->authentication->status ?? '');
        if ($authStatus === 'failure') {
            return $this->fail($httpStatus, $this->extractError($xml));
        }

        $result       = $xml->operation->result ?? null;
        $resultStatus = $result ? (string) $result->status : '';

        if ($resultStatus !== 'success') {
            return $this->fail($httpStatus, $this->extractError($xml));
        }

        // Success — <key> holds the record id (CUSTOMERID / VENDORID).
        $key = $result && isset($result->key) ? (string) $result->key : null;

        return [
            'success' => true,
            'status'  => $httpStatus,
            'data'    => ['id' => $key],
            'error'   => null,
        ];
    }

    /**
     * Pull the most useful, human-readable error text out of any Sage response.
     */
    protected function extractError(SimpleXMLElement $xml): string
    {
        $messages = [];

        foreach ($xml->xpath('//errormessage/error') ?: [] as $error) {
            $desc  = trim((string) ($error->description ?? ''));
            $desc2 = trim((string) ($error->description2 ?? ''));
            $line  = trim($desc . ' ' . $desc2);
            if ($line !== '') {
                $messages[] = $line;
            }
        }

        return $messages ? implode('; ', array_unique($messages)) : 'Sage Intacct rejected the request.';
    }

    protected function fail($status, string $error): array
    {
        return [
            'success' => false,
            'status'  => $status,
            'data'    => null,
            'error'   => $error,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // XML ELEMENT HELPERS (escape values safely)
    // ─────────────────────────────────────────────────────────────

    /** A single element with the value XML-escaped. */
    protected function el(string $tag, $value): string
    {
        return "<{$tag}>" . htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</{$tag}>";
    }

    /** Same as el() but omitted entirely when the value is null/empty. */
    protected function elIf(string $tag, $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        return $this->el($tag, $value);
    }
}
