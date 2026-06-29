<?php

namespace App\Integrations\Contracts;

use App\Models\CompanyIntegration;

/**
 * Base contract every integration driver implements.
 *
 * Drivers are constructed from a CompanyIntegration (which holds the encrypted
 * credentials and config for one company + provider). The generic Integrations
 * settings screen resolves `integration_provider->driver` to one of these and
 * calls testConnection().
 *
 * All methods return a normalised result array:
 *   [
 *     'success' => bool,
 *     'status'  => int|string|null,   // HTTP / provider status code
 *     'data'    => mixed,             // parsed payload on success
 *     'error'   => string|null,      // readable, secret-free message on failure
 *   ]
 */
interface IntegrationDriver
{
    public function __construct(CompanyIntegration $integration);

    /**
     * Verify the credentials / reachability. Used by the "Test Connection"
     * button on the Integrations screen.
     */
    public function testConnection(): array;
}
