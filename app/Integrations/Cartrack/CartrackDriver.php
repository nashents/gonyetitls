<?php

namespace App\Integrations\Cartrack;

use App\Integrations\Contracts\CartrackDriver as CartrackDriverContract;
use App\Models\CompanyIntegration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cartrack Fleet API driver (https://fleetapi-{region}.cartrack.com/rest).
 *
 * Auth is HTTP Basic on every request — no session/token to obtain or refresh.
 * Secrets are NEVER logged: only status codes and response-derived messages are.
 */
class CartrackDriver implements CartrackDriverContract
{
    protected array $credentials;
    protected array $config;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(CompanyIntegration $integration)
    {
        // credentials is an encrypted:array cast on the model.
        $this->credentials = $integration->credentials ?? [];
        $this->config      = $integration->config ?? [];

        $this->baseUrl = rtrim($this->credentials['base_url'] ?? '', '/');
        $this->timeout = (int) ($this->config['timeout'] ?? 30);
    }

    public function testConnection(): array
    {
        // /vehicles is the endpoint Cartrack's own auth guide uses as the
        // canonical example (developer.cartrack.com/docs/fleet-api-general/authentication).
        // /manufacturer/customers lives under their separate "Car Manufacturers"
        // (OEM partner) product and returned a "must be a Cartrack Subscriber"
        // 401 for a normal Fleet API account — this endpoint is the safer default.
        return $this->request('GET', '/vehicles');
    }

    public function listVehicles(array $filters = []): array
    {
        return $this->request('GET', '/vehicles', $filters);
    }

    public function getFleetSnapshot(int $page = 1, int $limit = 100): array
    {
        return $this->request('GET', '/aemp/iso15143-3/beta/fleet', [
            'page'  => $page,
            'limit' => min($limit, 100),
        ], $this->aempHeaders());
    }

    public function getVehicleSnapshot(string $registrationOrId): array
    {
        return $this->request(
            'GET',
            '/aemp/iso15143-3/beta/equipment/' . rawurlencode($registrationOrId),
            [],
            $this->aempHeaders()
        );
    }

    /** ISO15143-3 endpoints require this Accept header instead of application/json. */
    protected function aempHeaders(): array
    {
        return ['Accept' => 'application/iso15143-snapshot+json'];
    }

    protected function request(string $method, string $path, array $query = [], array $headers = []): array
    {
        if ($this->baseUrl === '' || empty($this->credentials['username']) || empty($this->credentials['password'])) {
            return $this->fail(null, 'Cartrack credentials are not fully configured.');
        }

        try {
            $response = Http::withBasicAuth($this->credentials['username'], $this->credentials['password'])
                ->withHeaders($headers ?: ['Accept' => 'application/json'])
                ->timeout($this->timeout)
                ->{strtolower($method)}($this->baseUrl . $path, $query);
        } catch (Throwable $e) {
            Log::error('Cartrack transport error: ' . $e->getMessage());
            return $this->fail(null, 'Could not reach Cartrack: ' . $e->getMessage());
        }

        return $this->parse($response);
    }

    protected function parse(Response $response): array
    {
        if ($response->failed()) {
            return $this->fail($response->status(), $this->extractError($response));
        }

        return [
            'success' => true,
            'status'  => $response->status(),
            'data'    => $response->json(),
            'error'   => null,
        ];
    }

    protected function extractError(Response $response): string
    {
        $body = $response->json();

        $message = data_get($body, 'message') ?? data_get($body, 'error') ?? data_get($body, 'error_description') ?? null;

        if (is_array($message)) {
            $message = collect($message)->flatten()->implode('; ');
        }

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return match ($response->status()) {
            401, 403 => 'Cartrack rejected the credentials.',
            404      => 'Cartrack vehicle or endpoint not found.',
            default  => 'Cartrack request failed (HTTP ' . $response->status() . ').',
        };
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
}
