<?php

namespace App\Http\Controllers;

use App\Integrations\FiscalHarmony\FiscalHarmonyDriver;
use App\Models\CompanyIntegration;
use App\Models\FiscalDocument;
use App\Services\Fiscal\FiscalProviderResolver;
use App\Services\FiscalHarmonyService;
use App\Services\FiscalisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Fiscal Harmony pushes invoice/credit note processing results here
 * (configured on the API key in the FH portal: "Save & Test Webhook URL").
 *
 * Rules from the docs: HTTPS on 443, reply 2xx within 5 seconds, no cookies,
 * 2xx for a correctly signed payload and 401 for a badly signed one. The
 * payload schema isn't published, so we accept a StatusRequest-shaped object,
 * a list of them, or either wrapped in "Data", and log it for reference.
 *
 * The body is signed with the sending account's API secret. Each company
 * configures its own FH integration, so the signature is checked against
 * every active FH integration (plus the legacy .env credentials).
 */
class FiscalHarmonyWebhookController extends Controller
{
    public function store(Request $request, FiscalProviderResolver $providers, FiscalisationService $fiscalisation)
    {
        $raw    = $request->getContent();
        $driver = $this->signingDriver($providers, $raw, $request->header('X-Api-Signature'));

        if (! $driver) {
            Log::warning('FiscalHarmony webhook rejected: bad signature', ['ip' => $request->ip()]);
            return response('Unauthorized', 401);
        }

        $payload = FiscalHarmonyService::camelKeys(json_decode($raw, true) ?: []);
        Log::info('FiscalHarmony webhook received', ['payload' => $payload]);

        foreach ($this->statusItems($payload) as $item) {
            $doc = FiscalDocument::where('request_id', $item['requestId'])->first();

            if (! $doc) {
                // FH's "Test Webhook" event and documents from other systems land here.
                continue;
            }

            if ($doc->isApproved()) {
                continue;
            }

            try {
                $fiscalisation->applyOutcome($doc, $driver->fromStatusItem($item)['data'], $driver->providerName());
            } catch (\Throwable $e) {
                Log::error("FiscalHarmony webhook apply failed for request {$item['requestId']}: {$e->getMessage()}");
            }
        }

        return response()->json(['received' => true]);
    }

    /** The FH driver whose API secret signed this body, or null. */
    protected function signingDriver(FiscalProviderResolver $providers, string $raw, ?string $signature): ?FiscalHarmonyDriver
    {
        $integrations = CompanyIntegration::with('integration_provider')
            ->where('status', 'active')
            ->whereHas('integration_provider', fn ($q) => $q->where('key', 'fiscal_harmony'))
            ->get();

        if ($legacy = $providers->legacyFiscalHarmony()) {
            $integrations->push($legacy);
        }

        foreach ($integrations as $integration) {
            $driver = new FiscalHarmonyDriver($integration);
            if ($driver->api()->verifySignature($raw, $signature)) {
                return $driver;
            }
        }

        return null;
    }

    /** @return array<int, array> StatusRequest items that carry a requestId */
    protected function statusItems(array $payload): array
    {
        $payload = $payload['data'] ?? $payload;
        $items   = array_is_list($payload) ? $payload : [$payload];

        return array_values(array_filter(
            $items,
            fn ($item) => is_array($item) && ! empty($item['requestId'])
        ));
    }
}
