<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * QR image for a fiscal verification URL, as a data URI so printed views and
 * dompdf PDFs embed it instead of fetching a remote image at render time.
 * Rendered once by the same QR service the on-screen templates use, then
 * cached forever (the URL of a fiscal receipt never changes). Null when it
 * cannot be fetched — callers still print the verification code and URL.
 */
class FiscalQr
{
    public static function dataUri(?string $url, int $size = 120): ?string
    {
        if (! $url) {
            return null;
        }

        $key = 'fiscal-qr:' . $size . ':' . md5($url);

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        try {
            $response = Http::timeout(5)->get('https://api.qrserver.com/v1/create-qr-code/', [
                'size'   => "{$size}x{$size}",
                'format' => 'png',
                'data'   => $url,
            ]);
        } catch (Throwable $e) {
            return null;
        }

        if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
            return null;
        }

        $uri = 'data:image/png;base64,' . base64_encode($response->body());
        Cache::forever($key, $uri);

        return $uri;
    }
}
