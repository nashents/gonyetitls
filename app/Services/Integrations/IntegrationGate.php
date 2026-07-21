<?php

namespace App\Services\Integrations;

use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;

/**
 * Provider-agnostic gate: "does the acting user's company have an ACTIVE
 * integration for provider <key>?"
 *
 * This is the reusable primitive every integration UI should use to hide its
 * controls, skip its eager-loads, and no-op its actions for companies that
 * haven't set the integration up. Results are memoised per request so guarding
 * a whole page of rows costs at most one lookup per provider.
 *
 * Usage in a Livewire component:
 *   public function getSageEnabledProperty() {
 *       return IntegrationGate::enabledForUser('sage_intacct');
 *   }
 * then wrap every provider control in `@if($this->sageEnabled)` and early-return
 * from provider actions when it is false.
 */
class IntegrationGate
{
    /** @var array<string,bool> keyed by "providerKey:companyId" */
    protected static array $memo = [];

    /** @var array<string,int|null> resolved provider ids keyed by provider key */
    protected static array $providerIds = [];

    /** The acting user's company id (many records have no company_id column). */
    public static function companyIdForUser(): ?int
    {
        $user = auth()->user();

        return optional(optional($user)->employee)->company_id
            ?? optional($user)->company_id
            ?? optional(optional($user)->company)->id;
    }

    /** True if the company has an active integration for the given provider. */
    public static function activeForCompany(string $providerKey, ?int $companyId): bool
    {
        if (! $companyId) {
            return false;
        }

        $memoKey = $providerKey . ':' . $companyId;
        if (array_key_exists($memoKey, static::$memo)) {
            return static::$memo[$memoKey];
        }

        if (! array_key_exists($providerKey, static::$providerIds)) {
            static::$providerIds[$providerKey] = optional(
                IntegrationProvider::where('key', $providerKey)->first()
            )->id;
        }

        $providerId = static::$providerIds[$providerKey];
        if (! $providerId) {
            return static::$memo[$memoKey] = false;
        }

        return static::$memo[$memoKey] = CompanyIntegration::where('company_id', $companyId)
            ->where('integration_provider_id', $providerId)
            ->where('status', 'active')
            ->exists();
    }

    /** True if the acting user's company has an active integration for provider. */
    public static function enabledForUser(string $providerKey): bool
    {
        return static::activeForCompany($providerKey, static::companyIdForUser());
    }
}
