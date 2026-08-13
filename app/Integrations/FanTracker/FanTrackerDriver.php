<?php

namespace App\Integrations\FanTracker;

use App\Integrations\Contracts\FanTrackerDriver as FanTrackerDriverContract;
use App\Models\CompanyIntegration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * FanTracker (white-labelled Navixy) driver.
 *
 * Unlike Cartrack (HTTP Basic on every call), this platform uses a session
 * hash: POST {base_url}/user/auth {login,password} -> {hash}, then every
 * other call sends {"hash": "..."} in its JSON body. Navixy's own docs say
 * hashes are valid ~24h; live-tested that a wrong/expired hash comes back as
 * HTTP 400 with body {"success":false,"status":{"code":3,"description":"Wrong hash"}}
 * (NOT an HTTP-layer 401) — so failure detection must read the JSON body, not
 * just the status code. The hash is cached per company-integration and
 * transparently re-obtained on first use / after that specific error.
 *
 * Secrets (login/password/hash) are NEVER logged: only status codes and
 * response-derived messages are.
 */
class FanTrackerDriver implements FanTrackerDriverContract
{
    protected array $credentials;
    protected array $config;
    protected string $baseUrl;
    protected int $timeout;
    protected int $integrationId;
    protected int $sessionTtlHours;

    public function __construct(CompanyIntegration $integration)
    {
        // credentials is an encrypted:array cast on the model.
        $this->credentials = $integration->credentials ?? [];
        $this->config      = $integration->config ?? [];

        $this->baseUrl         = rtrim($this->credentials['base_url'] ?? '', '/');
        $this->timeout          = (int) ($this->config['timeout'] ?? 30);
        $this->integrationId    = $integration->id;
        // Kept comfortably under Navixy's documented ~24h hash lifetime.
        $this->sessionTtlHours = (int) ($this->config['session_ttl_hours'] ?? 12);
    }

    public function testConnection(): array
    {
        return $this->listTrackers();
    }

    public function listTrackers(): array
    {
        return $this->request('tracker/list');
    }

    public function getStates(array $trackerIds): array
    {
        return $this->request('tracker/get_states', [
            'trackers'        => array_values($trackerIds),
            'allow_not_exist' => true,
            'list_blocked'    => true,
        ]);
    }

    public function getCounters(int $trackerId): array
    {
        return $this->request('tracker/get_counters', ['tracker_id' => $trackerId]);
    }

    protected function request(string $path, array $params = [], bool $isRetry = false): array
    {
        if ($this->baseUrl === '' || empty($this->credentials['login']) || empty($this->credentials['password'])) {
            return $this->fail(null, 'FanTracker credentials are not fully configured.');
        }

        $hash = $this->ensureHash();

        if (! $hash) {
            return $this->fail(null, 'FanTracker authentication failed — check the login/password/base_url.');
        }

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->timeout($this->timeout)
                ->post($this->baseUrl . '/' . ltrim($path, '/'), array_merge(['hash' => $hash], $params));
        } catch (Throwable $e) {
            Log::error('FanTracker transport error: ' . $e->getMessage());
            return $this->fail(null, 'Could not reach FanTracker: ' . $e->getMessage());
        }

        $body = $response->json();
        $success = (bool) data_get($body, 'success', false);

        if (! $success) {
            // code 3 / "Wrong hash" is Navixy's session-expired signal — refresh once and retry.
            $code = data_get($body, 'status.code');
            $description = (string) data_get($body, 'status.description', '');

            if (! $isRetry && ($code === 3 || stripos($description, 'hash') !== false)) {
                Cache::forget($this->hashCacheKey());
                return $this->request($path, $params, true);
            }

            return $this->fail($response->status(), $description !== '' ? $description : $this->genericError($response));
        }

        return [
            'success' => true,
            'status'  => $response->status(),
            'data'    => $body,
            'error'   => null,
        ];
    }

    /** Cached session hash, obtained via POST /user/auth on first use / after an invalid-hash response. */
    protected function ensureHash(): ?string
    {
        $cached = Cache::get($this->hashCacheKey());

        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->timeout($this->timeout)
                ->post($this->baseUrl . '/user/auth', [
                    'login'    => $this->credentials['login'],
                    'password' => $this->credentials['password'],
                ]);
        } catch (Throwable $e) {
            Log::error('FanTracker auth transport error: ' . $e->getMessage());
            return null;
        }

        $body = $response->json();

        if (! data_get($body, 'success')) {
            Log::error('FanTracker auth failed: ' . data_get($body, 'status.description', 'unknown error'));
            return null;
        }

        $hash = data_get($body, 'hash');

        if (! $hash) {
            return null;
        }

        Cache::put($this->hashCacheKey(), $hash, now()->addHours($this->sessionTtlHours));

        return $hash;
    }

    protected function hashCacheKey(): string
    {
        return "fantracker:hash:{$this->integrationId}";
    }

    protected function genericError(Response $response): string
    {
        return match ($response->status()) {
            400     => 'FanTracker rejected the request.',
            401, 403 => 'FanTracker rejected the credentials.',
            404      => 'FanTracker endpoint not found.',
            default  => 'FanTracker request failed (HTTP ' . $response->status() . ').',
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
