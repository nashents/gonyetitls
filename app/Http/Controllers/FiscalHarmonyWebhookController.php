<?php

namespace App\Http\Controllers;

use App\Models\FiscalDocument;
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
 */
class FiscalHarmonyWebhookController extends Controller
{
    public function store(Request $request, FiscalHarmonyService $api, FiscalisationService $fiscalisation)
    {
        $raw = $request->getContent();

        if (! $api->verifySignature($raw, $request->header('X-Api-Signature'))) {
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
                $fiscalisation->applyStatus($doc, $item);
            } catch (\Throwable $e) {
                Log::error("FiscalHarmony webhook apply failed for request {$item['requestId']}: {$e->getMessage()}");
            }
        }

        return response()->json(['received' => true]);
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
