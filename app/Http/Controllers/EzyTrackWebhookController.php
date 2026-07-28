<?php

namespace App\Http\Controllers;

use App\Services\EzyTrack\EzyTrackPositionIngestor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Receives EzyTrack's real-time position push (Digital Matter JSON format,
 * see the DMT JSON Device Integration spec). Auth is handled upstream by the
 * `ezytrack.token` middleware.
 *
 * Per the spec (6.1.3): a 200 response tells the device it can delete the
 * committed records from flash; anything else makes it re-POST. So we return
 * 200 for anything we can parse, even for a device we don't recognise yet —
 * only a structurally broken payload (missing SerNo, invalid JSON) gets a
 * non-200, since that genuinely needs fixing upstream before it can commit.
 */
class EzyTrackWebhookController extends Controller
{
    public function store(Request $request, EzyTrackPositionIngestor $ingestor)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            return response()->json(['message' => 'Empty or invalid JSON payload'], 400);
        }

        // The spec's own samples are a single base packet (one SerNo) per
        // POST; tolerate a top-level array too in case the connector ever
        // batches multiple devices in one request.
        $isList = array_keys($payload) === range(0, count($payload) - 1);
        $packets = $isList ? $payload : [$payload];

        foreach ($packets as $packet) {
            if (! is_array($packet) || empty($packet['SerNo'])) {
                return response()->json(['message' => 'Missing SerNo'], 400);
            }
        }

        try {
            foreach ($packets as $packet) {
                $ingestor->ingest($packet);
            }
        } catch (Throwable $e) {
            Log::error('EzyTrack webhook ingest failed: ' . $e->getMessage());

            return response()->json(['message' => 'Ingest failed'], 500);
        }

        return response()->json(['message' => 'OK']);
    }
}
