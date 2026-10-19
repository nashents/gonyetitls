<?php

namespace App\Services\Fiscal;

use App\Models\Currency;
use App\Models\Tax;
use App\Services\FiscalHarmonyService;
use Carbon\Carbon;

/**
 * Readiness check for one Fiscal Harmony account: credentials, signing,
 * device, subscription, tax mappings (our tax abbreviation → ZIMRA tax ID),
 * HS codes and currencies. Returns a plain-array report rendered by both
 * `php artisan fiscalharmony:check` and the Integrations "Readiness Check" modal.
 *
 * Status levels used throughout: ok | warn | fail.
 */
class FiscalHarmonyReadinessService
{
    /**
     * @return array{
     *   url: string, ready: bool, problems: int, fatal: ?string,
     *   checks: array<int, array{label: string, status: string, detail: string}>,
     *   device_taxes: array<int, array{id: mixed, name: string, percent: string}>,
     *   taxes: array<int, array{id: int, code: string, rate: mixed, hs_code: ?string, mapping: string, status: string}>,
     *   currencies: array<int, array{local: string, sent: string, status: string}>,
     *   supported_currencies: array<int, string>,
     *   unmapped: int, webhook_url: string
     * }
     */
    public function run(FiscalHarmonyService $api, bool $mapTaxes = false): array
    {
        $report = [
            'url'                  => $api->apiUrl(),
            'ready'                => false,
            'problems'             => 0,
            'fatal'                => null,
            'checks'               => [],
            'device_taxes'         => [],
            'taxes'                => [],
            'currencies'           => [],
            'supported_currencies' => [],
            'unmapped'             => 0,
            'webhook_url'          => route('webhooks.fiscal-harmony'),
        ];

        // 1. Credentials — nothing else is worth running if these fail.
        $test = $api->test();
        if (! $test['success']) {
            $report['fatal'] = 'Credentials failed: ' . $test['error'];
            $this->check($report, 'Credentials', 'fail', $test['error']);
            return $report;
        }
        $this->check($report, 'Credentials', 'ok', 'Account "' . ($test['data']['FullName'] ?? '?') . '"');

        // /test doesn't enforce signatures, so prove signing on /taxmapping.
        $probe = $api->testSignatureEnforcement();
        if ($probe['verified'] === true) {
            $this->check($report, 'Signing', 'ok', 'Verified — a wrong signature was rejected (401); ours passed');
        } elseif ($probe['verified'] === false) {
            $this->check($report, 'Signing', 'fail', 'Our signature is rejected (HTTP 401). Check the API secret; submissions will fail.');
        } else {
            $this->check($report, 'Signing', 'warn', "Inconclusive — wrong signature got HTTP {$probe['wrong']}, ours got HTTP {$probe['ours']} (expected 401 / 400)");
        }

        // 2. Device + subscription
        $device = $api->getFiscalDevice();
        if ($device['success']) {
            $d = $device['data'];
            $this->check($report, 'Fiscal device', 'ok',
                "{$d['TaxpayerName']} | TIN {$d['TIN']} | VAT {$d['VATNumber']} | device {$d['DeviceId']} ({$d['SerialNo']})");
        } else {
            $this->check($report, 'Fiscal device', 'fail', $device['error']);
        }

        $sub = $api->getSubscription();
        if ($sub['success']) {
            $live = strtolower((string) ($sub['data']['Status'] ?? '')) === 'live';
            $this->check($report, 'Subscription', $live ? 'ok' : 'warn',
                ($sub['data']['Status'] ?? '?') . ' until ' . substr((string) ($sub['data']['ExpiresOn'] ?? ''), 0, 10));
        }

        // 3. Tax mappings
        $deviceTaxes = collect($api->getApplicableTaxes())
            ->filter(fn ($t) => empty($t['taxValidTill']) || Carbon::parse($t['taxValidTill'])->isFuture())
            ->values();

        $report['device_taxes'] = $deviceTaxes->map(fn ($t) => [
            'id'      => $t['taxID'],
            'name'    => $t['taxName'],
            'percent' => $t['taxPercent'] === null ? 'exempt' : $t['taxPercent'] . '%',
        ])->all();

        $mappingsResult = $api->getTaxMappings();
        $mappings = collect($mappingsResult['success'] ? FiscalHarmonyService::camelKeys($mappingsResult['data']) : [])
            ->keyBy(fn ($m) => (string) $m['taxCode']);

        foreach (Tax::orderBy('id')->get() as $tax) {
            $code   = (string) $tax->abbreviation;
            $mapped = $mappings->get($code);
            $text   = $mapped ? "Mapped → {$mapped['destinationTaxId']}" : 'Not mapped';
            $status = $mapped ? 'ok' : 'fail';

            if (! $mapped && $mapTaxes) {
                $target = $this->matchDeviceTax($tax, $deviceTaxes);
                if ($target) {
                    $created = $api->createTaxMapping($code, (int) $target['taxID'], (string) $tax->name);
                    $text    = $created['success']
                        ? "Created → {$target['taxID']} ({$target['taxName']})"
                        : 'Create failed: ' . $created['error'];
                    $status  = $created['success'] ? 'ok' : 'fail';
                } else {
                    $text = 'Not mapped — no device tax with this rate';
                }
            }

            if ($status !== 'ok') {
                $report['problems']++;
                $report['unmapped']++;
            }
            if (! $tax->hs_code) {
                $report['problems']++;
                $status = 'fail';
            }

            $report['taxes'][] = [
                'id'      => $tax->id,
                'code'    => $code,
                'rate'    => $tax->rate,
                'hs_code' => $tax->hs_code,
                'mapping' => $text,
                'status'  => $status,
            ];
        }

        // 4. Currencies
        $supportedResult = $api->getSupportedCurrencies();
        $supported = $supportedResult['success'] ? array_values((array) $supportedResult['data']) : [];
        $aliases   = config('fiscal.currency_aliases', []);
        $report['supported_currencies'] = $supported;

        foreach (Currency::orderBy('id')->get() as $currency) {
            $code = strtoupper(trim((string) $currency->name));
            $sent = $aliases[$code] ?? $code;
            $ok   = in_array($sent, $supported, true);
            if (! $ok) {
                $report['problems']++;
            }
            $report['currencies'][] = ['local' => $currency->name, 'sent' => $sent, 'status' => $ok ? 'ok' : 'fail'];
        }

        $report['ready'] = $report['problems'] === 0;

        return $report;
    }

    protected function check(array &$report, string $label, string $status, string $detail): void
    {
        $report['checks'][] = compact('label', 'status', 'detail');
        if ($status === 'fail') {
            $report['problems']++;
        }
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
