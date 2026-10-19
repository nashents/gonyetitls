<?php

namespace App\Console\Commands;

use App\Integrations\FiscalHarmony\FiscalHarmonyDriver;
use App\Services\Fiscal\FiscalHarmonyReadinessService;
use App\Services\Fiscal\FiscalProviderResolver;
use App\Services\FiscalHarmonyService;
use Illuminate\Console\Command;

/**
 * Readiness check for Fiscal Harmony (logic in FiscalHarmonyReadinessService,
 * shared with the Integrations "Readiness Check" modal).
 * Run after changing the Fiscal Harmony integration (--company=ID) or the
 * legacy FISCAL_HARMONY_* .env credentials, before the first submit.
 */
class FiscalHarmonyCheck extends Command
{
    protected $signature = 'fiscalharmony:check
                            {--company= : Company id whose Fiscal Harmony integration to check (default: the .env FISCAL_HARMONY_* credentials)}
                            {--map-taxes : Create missing tax mappings by matching each local tax rate to the device\'s applicable taxes}';

    protected $description = 'Check Fiscal Harmony credentials, device, tax mappings, HS codes and currencies';

    public function handle(FiscalProviderResolver $providers, FiscalHarmonyReadinessService $readiness): int
    {
        if ($companyId = $this->option('company')) {
            try {
                $integration = $providers->integrationForCompany((int) $companyId);
            } catch (\RuntimeException $e) {
                $this->error($e->getMessage());
                return self::FAILURE;
            }
            if ($providers->providerKey($integration) !== 'fiscal_harmony') {
                $this->error("Company {$companyId}'s active fiscal provider is " . ($integration->integration_provider->name ?? '?') . ', not Fiscal Harmony.');
                return self::FAILURE;
            }
            $api    = (new FiscalHarmonyDriver($integration))->api();
            $source = "company {$companyId} integration";
        } else {
            $api    = new FiscalHarmonyService();
            $source = '.env';
        }

        $report = $readiness->run($api, (bool) $this->option('map-taxes'));

        $this->info("Fiscal Harmony — {$report['url']} ({$source})");

        $styles = ['ok' => 'line', 'warn' => 'warn', 'fail' => 'error'];
        foreach ($report['checks'] as $check) {
            $this->{$styles[$check['status']]}("  {$check['label']}: " . strtoupper($check['status']) . " — {$check['detail']}");
        }

        if ($report['fatal']) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Device taxes (ZIMRA)');
        $this->table(['taxID', 'Name', '%'], array_map(fn ($t) => [$t['id'], $t['name'], $t['percent']], $report['device_taxes']));

        $this->newLine();
        $this->info('Local taxes (TaxCode = abbreviation; every invoice line needs one + an HS code)');
        $this->table(['id', 'TaxCode', 'Rate', 'HS code', 'FH mapping'], array_map(
            fn ($t) => [$t['id'], $t['code'], $t['rate'], $t['hs_code'] ?: 'MISSING', $t['mapping']],
            $report['taxes']
        ));

        $this->newLine();
        $this->info('Currencies (supported: ' . implode(', ', $report['supported_currencies']) . ')');
        $this->table(['Local', 'Sent as', 'Status'], array_map(
            fn ($c) => [$c['local'], $c['sent'], $c['status'] === 'ok' ? 'supported' : 'NOT SUPPORTED'],
            $report['currencies']
        ));

        $this->newLine();
        $this->line("Webhook URL to set on the API key: {$report['webhook_url']}");

        if (! $report['ready']) {
            $this->warn("{$report['problems']} issue(s) to resolve before fiscalising."
                . ($report['unmapped'] && ! $this->option('map-taxes') ? ' Try --map-taxes for unmapped taxes.' : ''));
            return self::FAILURE;
        }

        $this->info('Ready to fiscalise.');
        return self::SUCCESS;
    }
}
