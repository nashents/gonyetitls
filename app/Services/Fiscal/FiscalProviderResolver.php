<?php

namespace App\Services\Fiscal;

use App\Integrations\Contracts\FiscalDriver;
use App\Integrations\FiscalHarmony\FiscalHarmonyDriver;
use App\Models\CompanyIntegration;
use App\Models\FiscalDocument;
use App\Models\IntegrationProvider;
use App\Services\Integrations\IntegrationGate;
use RuntimeException;

/**
 * Which fiscal provider handles a company's documents.
 *
 * A company fiscalises through its ONE active company integration of type
 * "fiscal" (Fiscal Harmony, Revmax, ...), configured on the Integrations
 * screen. Installs that predate this still have Fiscal Harmony in .env
 * (FISCAL_HARMONY_*); with no fiscal integration configured we fall back to
 * that, so they keep working until someone adds the integration.
 *
 * Status checks / PDFs for an existing fiscal document go back to the
 * integration that fiscalised it, even if the company has since switched.
 */
class FiscalProviderResolver
{
    public const TYPE = 'fiscal';

    /** The active fiscal integration for a company (or the .env Fiscal Harmony fallback). */
    public function integrationForCompany(?int $companyId): CompanyIntegration
    {
        $companyId ??= IntegrationGate::companyIdForUser();

        $active = $companyId
            ? CompanyIntegration::with('integration_provider')
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->whereHas('integration_provider', fn ($q) => $q->where('type', self::TYPE))
                ->get()
            : collect();

        if ($active->count() > 1) {
            throw new RuntimeException(
                'More than one fiscal provider is active (' . $active->pluck('integration_provider.name')->implode(', ')
                . '). Deactivate all but one under Integrations.'
            );
        }

        if ($active->count() === 1) {
            return $active->first();
        }

        if ($legacy = $this->legacyFiscalHarmony()) {
            return $legacy;
        }

        throw new RuntimeException('No fiscal provider is configured. Add and activate one (e.g. Fiscal Harmony or Revmax) under Integrations.');
    }

    /** The integration a fiscal document was submitted through. */
    public function integrationForDocument(FiscalDocument $doc, ?int $companyId = null): CompanyIntegration
    {
        if ($doc->company_integration_id) {
            $integration = CompanyIntegration::withTrashed()->with('integration_provider')->find($doc->company_integration_id);
            if ($integration) {
                return $integration;
            }
        }

        // Documents from before provider tracking were all Fiscal Harmony.
        if (($doc->provider ?? 'fiscal_harmony') === 'fiscal_harmony') {
            $current = rescue(fn () => $this->integrationForCompany($companyId), null, false);
            if ($current && $current->integration_provider?->key === 'fiscal_harmony') {
                return $current;
            }
            if ($legacy = $this->legacyFiscalHarmony()) {
                return $legacy;
            }
        }

        return $this->integrationForCompany($companyId);
    }

    public function driver(CompanyIntegration $integration): FiscalDriver
    {
        $class = $integration->integration_provider->driver ?? null;

        if (! $class || ! class_exists($class) || ! is_subclass_of($class, FiscalDriver::class)) {
            throw new RuntimeException(($integration->integration_provider->name ?? 'This provider') . ' has no fiscal driver.');
        }

        return new $class($integration);
    }

    public function providerKey(CompanyIntegration $integration): string
    {
        return $integration->integration_provider->key ?? 'fiscal_harmony';
    }

    /**
     * Unsaved Fiscal Harmony integration built from config/fiscalharmony.php,
     * for installs that still configure FH through .env. Null when .env has no key.
     */
    public function legacyFiscalHarmony(): ?CompanyIntegration
    {
        if (! config('fiscalharmony.api_key') || ! config('fiscalharmony.api_secret')) {
            return null;
        }

        $provider = IntegrationProvider::where('key', 'fiscal_harmony')->first()
            ?? new IntegrationProvider(['key' => 'fiscal_harmony', 'name' => 'Fiscal Harmony', 'type' => self::TYPE]);
        $provider->driver = FiscalHarmonyDriver::class;

        $integration = new CompanyIntegration([
            'status'      => 'active',
            'credentials' => [],   // empty → FiscalHarmonyService reads .env
            'config'      => ['payment_method' => config('fiscalharmony.payment_method', 'Credit')],
        ]);
        $integration->setRelation('integration_provider', $provider);

        return $integration;
    }
}
