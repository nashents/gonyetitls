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

    // ── ITEM (from Expense) ──────────────────────────────────────

    public function createItem(array $item): array
    {
        return $this->send('<create>' . $this->buildItem($item, true) . '</create>', 'create', 'ITEM');
    }

    public function updateItem(string $itemId, array $item): array
    {
        $item['id'] = $itemId;
        return $this->send('<update>' . $this->buildItem($item, false) . '</update>', 'update', 'ITEM');
    }

    // ── CONTACT + EMPLOYEE (from Driver) ─────────────────────────

    /** Employees require a real Contact first; create one (CONTACTNAME unique). */
    public function createContact(array $contact): array
    {
        $fn = '<create><CONTACT>'
            . $this->el('CONTACTNAME', $contact['name'])
            . $this->elIf('PRINTAS', $contact['printas'] ?? $contact['name'])
            . $this->elIf('FIRSTNAME', $contact['firstname'] ?? null)
            . $this->elIf('LASTNAME', $contact['lastname'] ?? null)
            . '</CONTACT></create>';
        return $this->send($fn, 'create', 'CONTACT');
    }

    public function createEmployee(array $employee): array
    {
        return $this->send('<create>' . $this->buildEmployee($employee, true) . '</create>', 'create', 'EMPLOYEE');
    }

    public function updateEmployee(string $employeeId, array $employee): array
    {
        $employee['id'] = $employeeId;
        return $this->send('<update>' . $this->buildEmployee($employee, false) . '</update>', 'update', 'EMPLOYEE');
    }

    // ── PURCHASE REQUISITION (create_potransaction) ──────────────

    /**
     * Create a Purchase Requisition. $header keys: transactiontype, datecreated,
     * vendorid, referenceno, datedue, contactname (pay-to/return-to), currency.
     * Each $lines row: itemid, itemdesc, quantity, unit, price, locationid,
     * departmentid, projectid, employeeid, classid. Element ORDER is enforced by
     * the Sage schema — do not reorder without re-checking.
     */
    public function createRequisition(array $header, array $lines): array
    {
        // Scope the create session to the operating entity (login <locationid>) so
        // the requisition is owned by that entity — required for the UI's Convert
        // action. entityid is a session concern, not part of the transaction body.
        return $this->send($this->buildRequisition($header, $lines), 'create', 'REQUISITION', $header['entityid'] ?? null);
    }

    // ── SALES TRANSACTION (create_sotransaction) ─────────────────

    /**
     * Create an Order-Entry (sales) transaction — e.g. a Job Card. $header keys:
     * transactiontype, datecreated, customerid, referenceno, datedue (= ship
     * date), currency, exchratetype, entityid, customfields. Each $lines row:
     * itemid, itemdesc, quantity, unit, price, locationid, departmentid, memo.
     */
    public function createSalesTransaction(array $header, array $lines): array
    {
        return $this->send($this->buildSalesTransaction($header, $lines), 'create', 'SOTRANSACTION', $header['entityid'] ?? null);
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

    /** Fetch the next page of a prior readByQuery, using its resultId. */
    public function readMore(string $resultId): array
    {
        $fn = '<readMore>' . $this->el('resultId', $resultId) . '</readMore>';

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
        // Emit PARENTID whenever the key is present — an empty value CLEARS the
        // parent (horse classes force this to stay top-level → orange); a value
        // sets it (trailer classes → green). Absent key leaves it unchanged.
        if (array_key_exists('parentid', $data)) {
            $fields .= $this->el('PARENTID', $data['parentid']);
        }
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
        $fields .= $this->elIf('PROJECTTYPE', $data['projecttype'] ?? null);
        // PARENTID nests a project (Transporter → Horse → Trip).
        $fields .= $this->elIf('PARENTID', $data['parentid'] ?? null);
        $fields .= $this->elIf('DESCRIPTION', $data['description'] ?? null);
        $fields .= $this->elIf('CUSTOMERID', $data['customerid'] ?? null);
        $fields .= $this->elIf('CLASSID', $data['classid'] ?? null);
        $fields .= $this->elIf('LOCATIONID', $data['locationid'] ?? null);
        $fields .= $this->elIf('DEPARTMENTID', $data['departmentid'] ?? null);
        // Dates must be mm/dd/yyyy — the mapper formats them.
        $fields .= $this->elIf('BEGINDATE', $data['begindate'] ?? null);
        $fields .= $this->elIf('ENDDATE', $data['enddate'] ?? null);
        $fields .= $this->elIf('CURRENCY', $data['currency'] ?? null);
        $fields .= $this->elIf('STATUS', $data['status'] ?? null);

        return '<PROJECT>' . $fields . '</PROJECT>';
    }

    /** Build an ITEM (Gonyeti Expense). Required ITEMID + NAME + ITEMTYPE (on create). */
    protected function buildItem(array $data, bool $isCreate): string
    {
        $fields = '';
        if (! empty($data['id'])) {
            $fields .= $this->el('ITEMID', $data['id']);
        }
        if ($isCreate || isset($data['name'])) {
            $fields .= $this->el('NAME', $data['name'] ?? '');
        }
        if ($isCreate) {
            $fields .= $this->el('ITEMTYPE', $data['type'] ?? 'Non-Inventory');
        }
        $fields .= $this->elIf('TAXABLE', $data['taxable'] ?? null);
        // Item GL Group — drives the item's revenue/COGS/inventory GL accounts.
        $fields .= $this->elIf('GLGROUP', $data['gl_group'] ?? null);
        // Item tax group (nested related-object form — the flat/key forms don't
        // take). Lets the item resolve a purchase tax schedule.
        if (! empty($data['tax_group'])) {
            $fields .= '<TAXGROUP>' . $this->el('NAME', $data['tax_group']) . '</TAXGROUP>';
        }

        return '<ITEM>' . $fields . '</ITEM>';
    }

    /** Build an EMPLOYEE. Needs an existing Contact + LOCATIONID + DEPARTMENTID. */
    protected function buildEmployee(array $data, bool $isCreate): string
    {
        $fields = '';
        if (! empty($data['id'])) {
            $fields .= $this->el('EMPLOYEEID', $data['id']);
        }
        if ($isCreate || isset($data['contactname'])) {
            $fields .= '<PERSONALINFO>' . $this->el('CONTACTNAME', $data['contactname'] ?? '') . '</PERSONALINFO>';
        }
        $fields .= $this->elIf('DEPARTMENTID', $data['departmentid'] ?? null);
        $fields .= $this->elIf('LOCATIONID', $data['locationid'] ?? null);
        $fields .= $this->elIf('STATUS', $data['status'] ?? null);

        return '<EMPLOYEE>' . $fields . '</EMPLOYEE>';
    }

    /**
     * Build a create_potransaction (Purchase Requisition). The Sage schema is a
     * strict sequence: transactiontype, datecreated, vendorid, referenceno,
     * datedue, returnto, payto, currency, potransitems. Line sequence:
     * itemid, itemdesc, quantity, unit, price, locationid, departmentid,
     * projectid, employeeid, classid.
     */
    protected function buildRequisition(array $h, array $lines): string
    {
        $items = '';
        foreach ($lines as $l) {
            $line  = $this->el('itemid', $l['itemid']);
            $line .= $this->elIf('itemdesc', $l['itemdesc'] ?? null);
            $line .= $this->el('quantity', $l['quantity'] ?? 1);
            $line .= $this->elIf('unit', $l['unit'] ?? null);
            $line .= $this->elIf('price', $l['price'] ?? null);
            // Receipt lines convert a PO line: reference it by its record key.
            $line .= $this->elIf('sourcelinekey', $l['sourcelinekey'] ?? null);
            $line .= $this->elIf('locationid', $l['locationid'] ?? null);
            $line .= $this->elIf('departmentid', $l['departmentid'] ?? null);
            $line .= $this->elIf('projectid', $l['projectid'] ?? null);
            $line .= $this->elIf('employeeid', $l['employeeid'] ?? null);
            $line .= $this->elIf('classid', $l['classid'] ?? null);
            $items .= '<potransitem>' . $line . '</potransitem>';
        }

        $hdr  = $this->el('transactiontype', $h['transactiontype']);
        $hdr .= $this->poDate('datecreated', $h['datecreated'] ?? null);
        $hdr .= $this->el('vendorid', $h['vendorid']);
        $hdr .= $this->elIf('referenceno', $h['referenceno'] ?? null);
        $hdr .= $this->poDate('datedue', $h['datedue'] ?? null);
        // returnto + payto are REQUIRED (a valid contact name).
        $hdr .= '<returnto>' . $this->el('contactname', $h['contactname'] ?? '') . '</returnto>';
        $hdr .= '<payto>' . $this->el('contactname', $h['contactname'] ?? '') . '</payto>';
        $hdr .= $this->elIf('currency', $h['currency'] ?? null);
        // When a transaction currency is set AND the session is scoped to an
        // entity, Sage requires an exchange rate. Sending an exchange-rate TYPE
        // lets Sage look the rate up (rate = 1 when it equals the base currency),
        // so both base and foreign currency trips post without a manual rate.
        if (! empty($h['currency'])) {
            $hdr .= $this->elIf('exchratetype', $h['exchratetype'] ?? null);
        }

        // Header custom fields (e.g. Dispatch Sheet's required REG + Driver
        // picklists). name => value; empty values are still sent (the field may
        // be required). Placed after the header, before the line items.
        if (! empty($h['customfields']) && is_array($h['customfields'])) {
            $cf = '';
            foreach ($h['customfields'] as $name => $value) {
                $cf .= '<customfield>' . $this->el('customfieldname', $name) . $this->el('customfieldvalue', $value) . '</customfield>';
            }
            $hdr .= '<customfields>' . $cf . '</customfields>';
        }

        return '<create_potransaction>' . $hdr . '<potransitems>' . $items . '</potransitems></create_potransaction>';
    }

    /**
     * Build a create_sotransaction (sales/Order-Entry, e.g. a Job Card). Schema
     * sequence: transactiontype, datecreated, customerid, referenceno, datedue
     * (ship date), currency, exchratetype, customfields, sotransitems. Line
     * sequence: itemid, quantity, unit, price, locationid, departmentid, memo.
     */
    protected function buildSalesTransaction(array $h, array $lines): string
    {
        $items = '';
        foreach ($lines as $l) {
            $line  = $this->el('itemid', $l['itemid']);
            $line .= $this->el('quantity', $l['quantity'] ?? 1);
            $line .= $this->elIf('unit', $l['unit'] ?? null);
            $line .= $this->elIf('price', $l['price'] ?? null);
            // Close-off / reversal convert a job-card line: reference it by key.
            $line .= $this->elIf('sourcelinekey', $l['sourcelinekey'] ?? null);
            $line .= $this->elIf('locationid', $l['locationid'] ?? null);
            $line .= $this->elIf('departmentid', $l['departmentid'] ?? null);
            $line .= $this->elIf('memo', $l['memo'] ?? null);
            $items .= '<sotransitem>' . $line . '</sotransitem>';
        }

        $hdr  = $this->el('transactiontype', $h['transactiontype']);
        $hdr .= $this->poDate('datecreated', $h['datecreated'] ?? null);
        $hdr .= $this->el('customerid', $h['customerid']);
        $hdr .= $this->elIf('referenceno', $h['referenceno'] ?? null);
        // datedue doubles as the required Ship Date on these definitions.
        $hdr .= $this->poDate('datedue', $h['datedue'] ?? null);
        $hdr .= $this->elIf('currency', $h['currency'] ?? null);
        if (! empty($h['currency'])) {
            $hdr .= $this->elIf('exchratetype', $h['exchratetype'] ?? null);
        }
        if (! empty($h['customfields']) && is_array($h['customfields'])) {
            $cf = '';
            foreach ($h['customfields'] as $name => $value) {
                $cf .= '<customfield>' . $this->el('customfieldname', $name) . $this->el('customfieldvalue', $value) . '</customfield>';
            }
            $hdr .= '<customfields>' . $cf . '</customfields>';
        }

        return '<create_sotransaction>' . $hdr . '<sotransitems>' . $items . '</sotransitems></create_sotransaction>';
    }

    /** Emit a Sage <tag><year/><month/><day/></tag> date, or '' if unparseable. */
    protected function poDate(string $tag, $value): string
    {
        if (empty($value)) {
            return '';
        }
        try {
            $d = \Carbon\Carbon::parse($value);
        } catch (Throwable $e) {
            return '';
        }
        return "<{$tag}><year>" . $d->format('Y') . '</year><month>' . $d->format('n') . '</month><day>' . $d->format('j') . "</day></{$tag}>";
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
        // Contact tax group uses the FLAT form (<TAXGROUP>value</TAXGROUP>) —
        // the nested <TAXGROUP><NAME> form 500s for contacts. Lets vendor
        // requisitions resolve a purchase tax schedule.
        $contact .= $this->elIf('TAXGROUP', $data['taxgroup'] ?? null);
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
    protected function transport(string $functionBody, ?string $locationId = null): array
    {
        $controlId = (config('sageintacct.xml.control_id', 'gonyeti')) . '-' . uniqid();
        $xml       = $this->envelope($functionBody, $controlId, $locationId);

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
    protected function send(string $functionBody, string $action, ?string $object = null, ?string $locationId = null): array
    {
        $t = $this->transport($functionBody, $locationId);

        if (! $t['ok']) {
            return $this->fail(null, $t['error']) + ['request' => $functionBody, 'response' => null];
        }

        $result             = $this->parse($t['body'], $t['status'], $action, $object);
        $result['request']  = $functionBody;
        $result['response'] = $t['body'];

        return $result;
    }

    protected function envelope(string $functionBody, string $controlId, ?string $locationId = null): string
    {
        $senderId   = $this->credentials['sender_id'] ?? '';
        $senderPass = $this->credentials['sender_password'] ?? '';
        $userId     = $this->credentials['user_id'] ?? '';
        $companyId  = $this->credentials['company_id'] ?? '';
        $userPass   = $this->credentials['user_password'] ?? '';
        $dtd        = config('sageintacct.xml.dtd_version', '3.0');

        // In a multi-entity company, <locationid> in the login scopes the whole
        // request to that operating entity, so records it creates are owned by the
        // entity (not the shared top level). Only sent when a caller needs it
        // (e.g. purchase requisitions) — shared objects omit it and stay top-level.
        $loginExtra = ($locationId !== null && $locationId !== '')
            ? $this->el('locationid', $locationId)
            : '';

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
            .       $loginExtra
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
        $resultId = null;
        $remaining = 0;
        if (isset($result->data)) {
            $attrs     = $result->data->attributes();
            $resultId  = isset($attrs['resultId']) ? (string) $attrs['resultId'] : null;
            $remaining = isset($attrs['numremaining']) ? (int) $attrs['numremaining'] : 0;
            foreach ($result->data->children() as $record) {
                $row = [];
                foreach ($record->children() as $field) {
                    $row[$field->getName()] = (string) $field;
                }
                $records[] = $row;
            }
        }

        return [
            'success'   => true,
            'status'    => $httpStatus,
            'data'      => $records,
            'resultId'  => $resultId,      // pass to readMore() for the next page
            'remaining' => $remaining,     // records still to fetch
            'error'     => null,
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
