<?php

namespace App\Integrations\SageIntacct;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use Illuminate\Support\Facades\Log;

/**
 * Sage Intacct REST API driver (OAuth 2.0) — SCAFFOLD.
 *
 * Sage's strategic API for new apps. Left as a working skeleton so the system
 * can switch to REST later by setting the company integration's config
 * `driver = rest` and supplying `client_id` / `client_secret` credentials.
 *
 * TODO: enable after completing Sage OAuth client-application onboarding.
 *   1. Confirm the token grant (client credentials vs. authorization code).
 *   2. Implement getAccessToken() with refresh + caching on the integration.
 *   3. Confirm the object endpoints below against the current REST reference.
 */
class SageRestDriver implements SageDriver
{
    protected array $credentials;
    protected array $config;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(CompanyIntegration $integration)
    {
        $this->credentials = $integration->credentials ?? [];
        $this->config      = $integration->config ?? [];
        $this->baseUrl     = rtrim($this->config['base_url'] ?? config('sageintacct.rest.base_url'), '/');
        $this->timeout     = (int) ($this->config['timeout'] ?? config('sageintacct.timeout', 30));
    }

    public function testConnection(): array
    {
        return $this->notImplemented();
    }

    public function createCustomer(array $customer): array
    {
        // return $this->post('/objects/accounts-receivable/customer', $this->toRest($customer));
        return $this->notImplemented();
    }

    public function updateCustomer(string $sageId, array $customer): array
    {
        return $this->notImplemented();
    }

    public function createVendor(array $vendor): array
    {
        // return $this->post('/objects/accounts-payable/vendor', $this->toRest($vendor));
        return $this->notImplemented();
    }

    public function updateVendor(string $sageId, array $vendor): array
    {
        return $this->notImplemented();
    }

    public function createClass(array $class): array
    {
        return $this->notImplemented();
    }

    public function updateClass(string $classId, array $class): array
    {
        return $this->notImplemented();
    }

    public function createProject(array $project): array
    {
        return $this->notImplemented();
    }

    public function updateProject(string $projectId, array $project): array
    {
        return $this->notImplemented();
    }

    public function createWarehouse(array $warehouse): array
    {
        return $this->notImplemented();
    }

    public function updateWarehouse(string $warehouseId, array $warehouse): array
    {
        return $this->notImplemented();
    }

    public function readByQuery(string $object, array $fields, string $query, int $pageSize = 200): array
    {
        return $this->notImplemented();
    }

    public function readMore(string $resultId): array
    {
        return $this->notImplemented();
    }

    public function createItem(array $item): array
    {
        return $this->notImplemented();
    }

    public function updateItem(string $itemId, array $item): array
    {
        return $this->notImplemented();
    }

    public function createContact(array $contact): array
    {
        return $this->notImplemented();
    }

    public function createEmployee(array $employee): array
    {
        return $this->notImplemented();
    }

    public function updateEmployee(string $employeeId, array $employee): array
    {
        return $this->notImplemented();
    }

    public function createRequisition(array $header, array $lines): array
    {
        return $this->notImplemented();
    }

    public function updateRequisition(string $key, array $lines): array
    {
        return $this->notImplemented();
    }

    public function createSalesTransaction(array $header, array $lines): array
    {
        return $this->notImplemented();
    }

    public function appendSalesTransactionLines(string $key, array $lines, ?string $entityId = null): array
    {
        return $this->notImplemented();
    }

    /**
     * Obtain (and cache/refresh) the OAuth bearer token.
     * TODO: implement against the confirmed grant type.
     */
    protected function getAccessToken(): ?string
    {
        // $response = Http::asForm()->post(config('sageintacct.rest.token_url'), [
        //     'grant_type'    => 'client_credentials',
        //     'client_id'     => $this->credentials['client_id'] ?? '',
        //     'client_secret' => $this->credentials['client_secret'] ?? '',
        // ]);
        return null;
    }

    protected function notImplemented(): array
    {
        Log::warning('SageIntacct REST driver invoked but not yet enabled; switch driver to "xml" or complete REST onboarding.');

        return [
            'success' => false,
            'status'  => null,
            'data'    => null,
            'error'   => 'Sage Intacct REST driver is not enabled yet. Use the XML driver or complete OAuth onboarding.',
        ];
    }
}
