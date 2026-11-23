<?php

namespace App\Services\Maps;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Driving distance between two saved points (Destination, LoadingPoint,
 * OffloadingPoint ...) via the Google Distance Matrix API.
 *
 * Reads the key through config() — env() returns null once config is cached.
 */
class RouteDistanceService
{
    /** Why the last call returned null, for showing to the user. */
    public static ?string $lastError = null;

    public static function hasCoordinates($point): bool
    {
        return $point
            && is_numeric($point->lat) && is_numeric($point->long)
            && !((float) $point->lat == 0.0 && (float) $point->long == 0.0);
    }

    /**
     * @return array{km: float, duration: string}|null
     */
    public static function between($from, $to): ?array
    {
        self::$lastError = null;

        if (!self::hasCoordinates($from) || !self::hasCoordinates($to)) {
            $missing = collect([$from, $to])
                ->reject(fn ($p) => self::hasCoordinates($p))
                ->map(fn ($p) => $p->name ?? $p->city ?? 'a point')
                ->implode(', ');
            self::$lastError = "No coordinates saved for {$missing}. Add its Google Maps link or lat/long to calculate distance.";

            return null;
        }

        $key = config('services.google.maps_key');
        if (!$key) {
            self::$lastError = 'Google Maps API key is not configured.';

            return null;
        }

        $origin = "{$from->lat},{$from->long}";
        $destination = "{$to->lat},{$to->long}";

        $cached = Cache::get($cacheKey = 'route-distance:' . md5($origin . '|' . $destination));
        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'units' => 'metric',
                'origins' => $origin,
                'destinations' => $destination,
                'key' => $key,
            ]);
        } catch (\Throwable $e) {
            self::$lastError = 'Could not reach Google Maps to calculate distance.';
            Log::warning('Distance Matrix request failed', ['error' => $e->getMessage()]);

            return null;
        }

        $data = $response->json() ?? [];
        $element = $data['rows'][0]['elements'][0] ?? null;
        $meters = $element['distance']['value'] ?? null;

        if (($data['status'] ?? null) !== 'OK' || ($element['status'] ?? null) !== 'OK' || !is_numeric($meters)) {
            self::$lastError = 'Google could not find a driving route between these points'
                . ' (' . ($data['error_message'] ?? $element['status'] ?? $data['status'] ?? 'unknown error') . ').';
            Log::warning('Distance Matrix returned no route', ['origin' => $origin, 'destination' => $destination, 'response' => $data]);

            return null;
        }

        $result = [
            'km' => round($meters / 1000, 2),
            'duration' => $element['duration']['text'] ?? '',
        ];

        Cache::put($cacheKey, $result, now()->addDays(30));

        return $result;
    }
}
