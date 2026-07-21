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

    // ── CLASS (Transporter / Horse / Trailer) ────────────────────

    public function createClass(array $class): array
    {
        return $this->send('<create>' . $this->buildClass($class, true) . '</create>', 'create', 'CLASS');
    }

    public function updateClass(string $classId, array $class): array
    {
        $class['id'] = $classId;
        return $this->send('<update>' . $this->buildClass($class, false) . '</update>', 'update', 'CLASS');
    }

    // ── PROJECT (Trip) ───────────────────────────────────────────

    public function createProject(array $project): array
    {
        return $this->send('<create>' . $this->buildProject($project, true) . '</create>', 'create', 'PROJECT');
    }

    public function updateProject(string $projectId, array $project): array
    {
        $project['id'] = $projectId;
        return $this->send('<update>' . $this->buildProject($project, false) . '</update>', 'update', 'PROJECT');
    }

    // ── READ (existence checks / pull) ───────────────────────────

    /**
     * Run a readByQuery. On success `data` is a list of associative rows
     * (field name => value). Used to de-dup classes by NAME before creating.
     */
    public function readByQuery(string $object, array $fields, string $query, int $pageSize = 200): array
    {
        $fn = '<readByQuery>'
            . $this->el('object', $object)
            . $this->el('fields', implode(',', $fields))
            . $this->el('query', $query)
            . $this->el('pagesize', $pageSize)
            . '</readByQuery>';

        $t = $this->transport($fn);

        if (! $t['ok']) {
            return $this->fail(null, $t['error']) + ['request' => $fn, 'response' => null];
        }

        return $this->parseQuery($t['body'], $t['status']) + ['request' => $fn, 'response' => $t['body']];
    }

    // ─────────────────────────────────────────────────────────────
    // PAYLOAD BUILDERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Build a CLASS element. Required CLASSID + NAME; optional DESCRIPTION,
     * PARENTID (hierarchy), STATUS.
     */
    protected function buildClass(array $data, bool $isCreate): string
    {
        $fields = '';
        if (! empty($data['id'])) {
            $fields .= $this->el('CLASSID', $data['id']);
        }
        if ($isCreate || isset($data['name'])) {
            $fields .= $this->el('NAME', $data['name'] ?? '');
        }
        $fields .= $this->elIf('DESCRIPTION', $data['description'] ?? null);
        $fields .= $this->elIf('PARENTID', $data['parentid'] ?? null);
        $fields .= $this->elIf('STATUS', $data['status'] ?? null);

        return '<CLASS>' . $fields . '</CLASS>';
    }

    /**
     * Build a PROJECT element. Required NAME + PROJECTCATEGORY (on create);
     * optional PROJECTID, CUSTOMERID, CLASSID, dates, CURRENCY, STATUS, DESCRIPTION.
     */
    protected function buildProject(array $data, bool $isCreate): string
    {
        $fields = '';
        if (! empty($data['id'])) {
            $fields .= $this->el('PROJECTID', $data['id']);
        }
        if ($isCreate || isset($data['name'])) {
            $fields .= $this->el('NAME', $data['name'] ?? '');
        }
        // PROJECTCATEGORY is required by Sage on create.
        $fields .= $this->elIf('PROJECTCATEGORY', $data['category'] ?? null);
        $fields .= $this->elIf('DESCRIPTION', $data['description'] ?? null);
        $fields .= $this->elIf('CUSTOMERID', $data['customerid'] ?? null);
        $fields .= $this->elIf('CLASSID', $data['classid'] ?? null);
        // Dates must be mm/dd/yyyy — the mapper formats them.
        $fields .= $this->elIf('BEGINDATE', $data['begindate'] ?? null);
        $fields .= $this->elIf('ENDDATE', $data['enddate'] ?? null);
        $fields .= $this->elIf('CURRENCY', $data['currency'] ?? null);
        $fields .= $this->elIf('STATUS', $data['status'] ?? null);

        return '<PROJECT>' . $fields . '</PROJECT>';
    }

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
     * POST a function body inside the full envelope. Returns the raw transport
     * outcome (never parsed) so different callers can parse it their own way.
     * The auth envelope (with credentials) is built here and NOT returned — only
     * the credential-free function body is echoed back for safe logging.
     */
    protected function transport(string $functionBody): array
    {
        $controlId = (config('sageintacct.xml.control_id', 'gonyeti')) . '-' . uniqid();
        $xml       = $this->envelope($functionBody, $controlId);

        try {
            $response = Http::timeout($this->timeout)
                ->withBody($xml, 'application/xml')
                ->post($this->endpoint);
        } catch (Throwable $e) {
            Log::error('SageIntacct XML transport error: ' . $e->getMessage());
            return ['ok' => false, 'status' => null, 'body' => null, 'error' => 'Could not reach Sage Intacct: ' . $e->getMessage(), 'request' => $functionBody];
        }

        return ['ok' => true, 'status' => $response->status(), 'body' => $response->body(), 'error' => null, 'request' => $functionBody];
    }

    /**
     * Send a create/update function and parse its single-record result.
     * The result carries `request` (credential-free function XML) + `response`
     * (raw body) for troubleshooting/audit payload storage.
     */
    protected function send(string $functionBody, string $action, ?string $object = null): array
    {
        $t = $this->transport($functionBody);

        if (! $t['ok']) {
            return $this->fail(null, $t['error']) + ['request' => $functionBody, 'response' => null];
        }

        $result             = $this->parse($t['body'], $t['status'], $action, $object);
        $result['request']  = $functionBody;
        $result['response'] = $t['body'];

        return $result;
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
     * Parse a readByQuery response into a list of associative rows.
     */
    protected function parseQuery(string $body, int $httpStatus): array
    {
        if ($body === '') {
            return $this->fail($httpStatus, 'Empty response from Sage Intacct.');
        }

        try {
            $xml = new SimpleXMLElement($body);
        } catch (Throwable $e) {
            return $this->fail($httpStatus, 'Unexpected response from Sage Intacct.');
        }

        if ((string) ($xml->control->status ?? '') === 'failure'
            || (string) ($xml->operation->authentication->status ?? '') === 'failure') {
            return $this->fail($httpStatus, $this->extractError($xml));
        }

        $result = $xml->operation->result ?? null;
        if (! $result || (string) $result->status !== 'success') {
            return $this->fail($httpStatus, $this->extractError($xml));
        }

        $records = [];
        if (isset($result->data)) {
            foreach ($result->data->children() as $record) {
                $row = [];
                foreach ($record->children() as $field) {
                    $row[$field->getName()] = (string) $field;
                }
                $records[] = $row;
            }
        }

        return ['success' => true, 'status' => $httpStatus, 'data' => $records, 'error' => null];
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
