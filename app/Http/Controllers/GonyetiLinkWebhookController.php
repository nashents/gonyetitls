<?php

namespace App\Http\Controllers;

use App\Services\GonyetiLink\GonyetiLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Receives messages from the client's other Gonyeti instance (see GonyetiLinkService). */
class GonyetiLinkWebhookController extends Controller
{
    public function store(Request $request, GonyetiLinkService $service)
    {
        $integration = $request->attributes->get('gonyeti_link_integration');

        $validated = $request->validate([
            'event' => 'required|string|max:50',
            'ref' => 'nullable|string|max:64',
            'data' => 'nullable|array',
            'source' => 'nullable|array',
        ]);

        $result = DB::transaction(fn () => $service->handleInbound(
            $integration,
            $validated['event'],
            $validated['ref'] ?? null,
            $validated['data'] ?? [],
            $validated['source'] ?? []
        ));

        return response()->json($result);
    }
}
