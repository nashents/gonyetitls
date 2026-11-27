<?php

namespace App\Integrations\GonyetiLink;

use App\Models\CompanyIntegration;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Talks to the client's OTHER Gonyeti instance (Gonyeti Instance Link).
 *
 * Every message is a JSON POST to {base_url}/api/gonyeti-link, signed with
 * the shared secret both instances hold:
 *   X-Gonyeti-Timestamp: unix time
 *   X-Gonyeti-Signature: hex HMAC-SHA256 of "{timestamp}.{raw body}"
 * The receiver rejects stale (> 5 min) or badly signed messages.
 */
class GonyetiLinkDriver
{
    const MAX_CLOCK_SKEW = 300;

    public function __construct(protected CompanyIntegration $integration)
    {
    }

    public static function sign(string $secret, string $timestamp, string $body): string
    {
        return hash_hmac('sha256', $timestamp . '.' . $body, $secret);
    }

    public function endpoint(): string
    {
        $base = rtrim((string) ($this->integration->credentials['base_url'] ?? ''), '/');
        // Accept "https://host", "https://host/" or "https://host/api".
        $base = preg_replace('#/api$#', '', $base);

        return $base . '/api/gonyeti-link';
    }

    /**
     * @return array{success: bool, status: ?int, error: ?string, data: ?array, permanent: bool}
     */
    public function post(string $event, ?string $ref, array $data): array
    {
        $secret = (string) ($this->integration->credentials['shared_secret'] ?? '');
        if ($secret === '' || blank($this->integration->credentials['base_url'] ?? null)) {
            return ['success' => false, 'status' => null, 'error' => 'Base URL and shared secret must both be set.', 'data' => null, 'permanent' => false];
        }

        $body = json_encode([
            'event' => $event,
            'ref' => $ref,
            'data' => $data,
            'sent_at' => now()->toIso8601String(),
            'source' => [
                'url' => config('app.url'),
                'company' => optional($this->integration->company)->name,
            ],
        ]);
        $timestamp = (string) time();

        try {
            $response = Http::timeout((int) ($this->integration->config['timeout'] ?? 15))
                ->acceptJson()
                ->withHeaders([
                    'X-Gonyeti-Timestamp' => $timestamp,
                    'X-Gonyeti-Signature' => self::sign($secret, $timestamp, $body),
                ])
                ->withBody($body, 'application/json')
                ->post($this->endpoint());
        } catch (Throwable $e) {
            return ['success' => false, 'status' => null, 'error' => 'Could not reach the other instance: ' . $e->getMessage(), 'data' => null, 'permanent' => false];
        }

        if ($response->successful()) {
            return ['success' => true, 'status' => $response->status(), 'error' => null, 'data' => $response->json(), 'permanent' => false];
        }

        $status = $response->status();
        $message = $response->json('message') ?: ('HTTP ' . $status);

        // 4xx other than auth/timeouts/throttling won't succeed on a resend.
        $permanent = $status >= 400 && $status < 500 && !in_array($status, [401, 403, 404, 408, 429], true);

        return ['success' => false, 'status' => $status, 'error' => $message, 'data' => $response->json(), 'permanent' => $permanent];
    }

    /** Used by Business Settings > Integrations "Test connection". */
    public function testConnection(): array
    {
        $result = $this->post('ping', null, ['role' => $this->integration->config['role'] ?? null]);

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error']];
        }

        $theirRole = $result['data']['role'] ?? null;
        $ourRole = $this->integration->config['role'] ?? null;
        if ($theirRole && $ourRole && $theirRole === $ourRole) {
            return ['success' => false, 'error' => "Both instances are set to role \"{$ourRole}\". Set one to freight and the other to transport."];
        }

        return ['success' => true, 'data' => ['summary' => 'Linked to ' . ($result['data']['company'] ?? 'the other instance') . ($theirRole ? " ({$theirRole})" : '') . '.']];
    }
}
