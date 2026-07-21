<?php

namespace App\Services\Sage;

use App\Services\Integrations\IntegrationGate;

/**
 * Sage-specific convenience wrapper over the generic IntegrationGate.
 * Kept so Sage callers read cleanly; all logic lives in IntegrationGate.
 */
class SageIntegration
{
    public const PROVIDER_KEY = 'sage_intacct';

    public static function companyIdForUser(): ?int
    {
        return IntegrationGate::companyIdForUser();
    }

    public static function activeForCompany(?int $companyId): bool
    {
        return IntegrationGate::activeForCompany(self::PROVIDER_KEY, $companyId);
    }

    public static function enabledForUser(): bool
    {
        return IntegrationGate::enabledForUser(self::PROVIDER_KEY);
    }
}
