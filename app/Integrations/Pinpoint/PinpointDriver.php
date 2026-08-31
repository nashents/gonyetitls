<?php

namespace App\Integrations\Pinpoint;

use App\Integrations\Contracts\PinpointDriver as PinpointDriverContract;
use App\Models\CompanyIntegration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pinpoint (trackingpinpoint.com) driver — a white-labelled GPS-tracking
 * cloud platform (docs: http://www.trackingpinpoint.com/docs/Developers/API).
 *
 * Auth is a single static API token, generated per-account under
 * Settings -> API Tokens on the tenant's own portal, sent as a `token` HTTP
 * header on every call — unlike FanTracker there is no session/hash to
 * obtain or refresh. base_url is the tenant's own portal domain (e.g.
 * https://track.trackingpinpoint.com), not a shared public API root.
 *
 * Every response is HTTP 200 with a JSON envelope {error, msg, data}; error
 * is 0 on success and non-zero on failure regardless of HTTP status, so
 * failure detection reads the body, not just the status code (mirrors
 * FanTracker's "Wrong hash" handling). Secrets are NEVER logged: only status
 * codes and response-derived messages are.
 */
class PinpointDriver implements PinpointDriverContract
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
        return $this->listTrackers();
    }

    public function listTrackers(array $filters = []): array
    {
        return $this->request('/api2/trackers', $filters);
    }

    public function getFleetLastPositions(): array
    {
        return $this->request('/api2/last', ['uin' => '__all_sys_']);
    }

    protected function request(string $path, array $query = []): array
    {
        if ($this->baseUrl === '' || empty($this->credentials['token'])) {
            return $this->fail(null, 'Pinpoint credentials are not fully configured.');
        }

        try {
            $response = Http::withHeaders([
                    'token'  => $this->credentials['token'],
                    'Accept' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->get($this->baseUrl . $path, $query);
        } catch (Throwable $e) {
            Log::error('Pinpoint transport error: ' . $e->getMessage());
            return $this->fail(null, 'Could not reach Pinpoint: ' . $e->getMessage());
        }

        return $this->parse($response);
    }

    protected function parse(Response $response): array
    {
        if ($response->failed()) {
            return $this->fail($response->status(), $this->genericError($response));
        }

        $body = $response->json();

        if ((int) ($body['error'] ?? 0) !== 0) {
            return $this->fail($response->status(), $body['msg'] ?? $this->genericError($response));
        }

        return [
            'success' => true,
            'status'  => $response->status(),
            'data'    => $body['data'] ?? null,
            'error'   => null,
        ];
    }

    protected function genericError(Response $response): string
    {
        return match ($response->status()) {
            401, 403 => 'Pinpoint rejected the token.',
            404      => 'Pinpoint endpoint not found.',
            default  => 'Pinpoint request failed (HTTP ' . $response->status() . ').',
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
