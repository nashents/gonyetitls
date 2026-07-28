<?php

namespace App\Integrations\EzyTrack;

use App\Integrations\Contracts\IntegrationDriver;
use App\Models\CompanyIntegration;

/**
 * EzyTrack is push-based: their Device Manager POSTs Digital Matter JSON
 * position updates to us at /api/webhooks/ezytrack (see
 * EzyTrackWebhookController + VerifyEzyTrackToken), authenticated with a
 * single shared bearer token — there is no outbound API call to make.
 *
 * "Test Connection" on the Integrations screen therefore just confirms our
 * side is ready to receive, not that EzyTrack is reachable.
 */
class EzyTrackDriver implements IntegrationDriver
{
    protected $integration;

    public function __construct(CompanyIntegration $integration)
    {
        $this->integration = $integration;
    }

    public function testConnection(): array
    {
        $token = config('services.ezytrack.token');

        if (empty($token)) {
            return [
                'success' => false,
                'status'  => null,
                'data'    => null,
                'error'   => 'EZYTRACK_WEBHOOK_TOKEN is not configured on this server yet.',
            ];
        }

        return [
            'success' => true,
            'status'  => null,
            'data'    => ['webhook_url' => route('webhooks.ezytrack')],
            'error'   => null,
        ];
    }
}
