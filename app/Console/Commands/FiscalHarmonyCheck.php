<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\Tax;
use App\Services\FiscalHarmonyService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Readiness check for Fiscal Harmony: credentials, device, subscription,
 * tax mappings (our tax abbreviation → ZIMRA tax ID), HS codes and currencies.
 * Run after changing FISCAL_HARMONY_* credentials, before the first submit.
 */
class FiscalHarmonyCheck extends Command
{
    protected $signature = 'fiscalharmony:check
                            {--map-taxes : Create missing tax mappings by matching each local tax rate to the device\'s applicable taxes}';

    protected $description = 'Check Fiscal Harmony credentials, device, tax mappings, HS codes and currencies';

    public function handle(FiscalHarmonyService $api): int
    {
        $problems = 0;

        $this->info('Fiscal Harmony — ' . config('fiscalharmony.api_url'));
        $this->line('  X-Application: ' . config('fiscalharmony.app_name')
            . ' | X-App-Station: ' . config('fiscalharmony.app_station')
            . ' | X-App-Version: ' . config('fiscalharmony.app_version'));

        // 1. Credentials
        $test = $api->test();
        if (! $test['success']) {
            $this->error('  Credentials: FAILED — ' . $test['error']);
            return self::FAILURE;
        }
        $this->line('  Credentials: OK (account "' . ($test['data']['FullName'] ?? '?') . '")');

        // 2. Device + subscription
        $device = $api->getFiscalDevice();
        if ($device['success']) {
            $d = $device['data'];
            $this->line("  Device: {$d['TaxpayerName']} | TIN {$d['TIN']} | VAT {$d['VATNumber']} | device {$d['DeviceId']} ({$d['SerialNo']})");
        } else {
            $this->error('  Device: ' . $device['error']);
            $problems++;
        }

        $sub = $api->getSubscription();
        if ($sub['success']) {
            $this->line("  Subscription: {$sub['data']['Status']} until " . substr((string) $sub['data']['ExpiresOn'], 0, 10));
        }

        // 3. Tax mappings
        $deviceTaxes = collect($api->getApplicableTaxes())
            ->filter(fn ($t) => empty($t['taxValidTill']) || Carbon::parse($t['taxValidTill'])->isFuture())
            ->values();

        $this->newLine();
        $this->info('Device taxes (ZIMRA)');
        $this->table(['taxID', 'Name', '%'], $deviceTaxes->map(fn ($t) => [$t['taxID'], $t['taxName'], $t['taxPercent'] ?? 'exempt'])->all());

        $mappingsResult = $api->getTaxMappings();
        $mappings = collect($mappingsResult['success'] ? FiscalHarmonyService::camelKeys($mappingsResult['data']) : [])
            ->keyBy(fn ($m) => (string) $m['taxCode']);

        $rows = [];
        foreach (Tax::orderBy('id')->get() as $tax) {
            $code   = (string) $tax->abbreviation;
            $mapped = $mappings->get($code);
            $status = $mapped ? "mapped → {$mapped['destinationTaxId']}" : 'NOT MAPPED';

            if (! $mapped && $this->option('map-taxes')) {
                $target = $this->matchDeviceTax($tax, $deviceTaxes);
                if ($target) {
                    $created = $api->createTaxMapping($code, (int) $target['taxID'], (string) $tax->name);
                    $status  = $created['success']
                        ? "CREATED → {$target['taxID']} ({$target['taxName']})"
                        : 'create failed: ' . $created['error'];
                    $mapped  = $created['success'];
                } else {
                    $status = 'NOT MAPPED — no device tax with this rate';
                }
            }

            if (! $mapped) {
                $problems++;
            }
            if (! $tax->hs_code) {
                $problems++;
            }

            $rows[] = [$tax->id, $code, $tax->rate, $tax->hs_code ?: 'MISSING', $status];
        }

        $this->newLine();
        $this->info('Local taxes (TaxCode = abbreviation; every invoice line needs one + an HS code)');
        $this->table(['id', 'TaxCode', 'Rate', 'HS code', 'FH mapping'], $rows);

        // 4. Currencies
        $supportedResult = $api->getSupportedCurrencies();
        $supported = $supportedResult['success'] ? (array) $supportedResult['data'] : [];
        $aliases   = config('fiscalharmony.currency_aliases', []);

        $currencyRows = [];
        foreach (Currency::orderBy('id')->get() as $currency) {
            $code = strtoupper(trim((string) $currency->name));
            $sent = $aliases[$code] ?? $code;
            $ok   = in_array($sent, $supported, true);
            if (! $ok) {
                $problems++;
            }
            $currencyRows[] = [$currency->name, $sent, $ok ? 'supported' : 'NOT SUPPORTED'];
        }

        $this->newLine();
        $this->info('Currencies (supported: ' . implode(', ', $supported) . ')');
        $this->table(['Local', 'Sent as', 'Status'], $currencyRows);

        $this->newLine();
        $this->line('Webhook URL to set on the API key: ' . route('webhooks.fiscal-harmony'));

        if ($problems) {
            $this->warn("{$problems} issue(s) to resolve before fiscalising." . ($this->option('map-taxes') ? '' : ' Try --map-taxes for unmapped taxes.'));
            return self::FAILURE;
        }

        $this->info('Ready to fiscalise.');
        return self::SUCCESS;
    }

    /** Exempt ↔ device tax with no percent; otherwise match on the rate. */
    protected function matchDeviceTax(Tax $tax, $deviceTaxes): ?array
    {
        $isExempt = stripos($tax->name . ' ' . $tax->abbreviation, 'exempt') !== false;

        return $deviceTaxes->first(function ($t) use ($tax, $isExempt) {
            if ($isExempt) {
                return $t['taxPercent'] === null;
            }

            return $t['taxPercent'] !== null
                && abs((float) $t['taxPercent'] - (float) $tax->rate) < 0.001
                // 5% withholding is not a VAT rate.
                && stripos($t['taxName'], 'withholding') === false;
        });
    }
}
