<?php

namespace App\Services\Fleet;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Turns a lat/lng into a human-readable address via Google's Geocoding API
 * (the same GOOGLE_MAPS_API_KEY used for the JS map embed — it must also
 * have the Geocoding API enabled in Google Cloud Console for this to work).
 *
 * Results are cached by coordinate rounded to 4 decimal places (~11m), since
 * a parked/idle truck repeatedly reports almost the same point and street
 * addresses don't change — this keeps repeat lookups free and avoids
 * hammering the API on every page load.
 */
class ReverseGeocoder
{
    public function resolve(float $lat, float $lng): ?string
    {
        $key = config('services.google.maps_key');
        if (! $key) {
            return null;
        }

        $cacheKey = 'geocode:' . round($lat, 4) . ',' . round($lng, 4);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($lat, $lng, $key) {
            try {
                $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => "{$lat},{$lng}",
                    'key'    => $key,
                ]);

                if (! $response->ok()) {
                    return null;
                }

                $data = $response->json();

                if (($data['status'] ?? null) !== 'OK') {
                    return null;
                }

                return $data['results'][0]['formatted_address'] ?? null;
            } catch (\Throwable $e) {
                Log::warning('ReverseGeocoder: lookup failed: ' . $e->getMessage());

                return null;
            }
        });
    }
}
