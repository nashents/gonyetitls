<?php

namespace App\Services\Maps;

use Illuminate\Support\Facades\Http;

/**
 * Pulls a lat/lng pair out of whatever a user pastes into a "Location" box:
 * Google Maps short links (maps.app.goo.gl / goo.gl/maps), full place/search
 * URLs, ?q= / ?query= / ?ll= links, or plain "lat, lng" text.
 */
class MapLocationParser
{
    /**
     * @return array{0: float, 1: float}|null [lat, lng]
     */
    public static function coordinates(?string $input): ?array
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        // Plain "lat, lng" (or "lat lng") typed/pasted directly.
        if ($pair = self::pair('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*[,\s]\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', $input)) {
            return $pair;
        }

        $url = self::isShortLink($input) ? (self::expand($input) ?? $input) : $input;
        $url = urldecode($url);

        $patterns = [
            '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',                  // pin position in /place/ data
            '/\/search\/(-?\d+(?:\.\d+)?),\s*\+?(-?\d+(?:\.\d+)?)/',       // /search/lat,lng
            '/[?&](?:q|query|ll|destination|daddr|center)=(-?\d+(?:\.\d+)?),\s*\+?(-?\d+(?:\.\d+)?)/',
            '/\/place\/(-?\d+(?:\.\d+)?),\s*\+?(-?\d+(?:\.\d+)?)/',        // /place/lat,lng
            '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)(?:,|$)/',                // map viewport centre (last resort)
        ];

        foreach ($patterns as $pattern) {
            if ($pair = self::pair($pattern, $url)) {
                return $pair;
            }
        }

        return null;
    }

    private static function pair(string $pattern, string $subject): ?array
    {
        if (!preg_match($pattern, $subject, $m)) {
            return null;
        }

        $lat = (float) $m[1];
        $lng = (float) $m[2];

        if (abs($lat) > 90 || abs($lng) > 180) {
            return null;
        }

        return [$lat, $lng];
    }

    private static function isShortLink(string $input): bool
    {
        return (bool) preg_match('#^https?://(maps\.app\.goo\.gl|goo\.gl/maps)/#i', $input);
    }

    private static function expand(string $url): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            return (string) $response->effectiveUri();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
