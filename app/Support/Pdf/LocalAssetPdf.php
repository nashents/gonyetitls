<?php

namespace App\Support\Pdf;

use Barryvdh\DomPDF\PDF;

/**
 * dompdf wrapper that serves this app's own images from disk.
 *
 * PDF templates reference logos with asset('images/...'), which dompdf then
 * fetches over HTTP from the server itself. When that request fails (APP_URL
 * unreachable from the server, SSL, firewall) dompdf draws an "Image not found"
 * placeholder that corrupts the embedded font, so every glyph in the document
 * renders as garbage. Rewriting those URLs to local paths avoids the round trip.
 */
class LocalAssetPdf extends PDF
{
    public function loadHTML(string $string, ?string $encoding = null): self
    {
        return parent::loadHTML($this->localiseAssets($string), $encoding);
    }

    protected function localiseAssets(string $html): string
    {
        $base = rtrim(asset('/'), '/');
        $publicPath = public_path();

        return preg_replace_callback('/(<img\b[^>]*\bsrc=)(["\'])(.*?)\2/i', function ($m) use ($base, $publicPath) {
            $src = $m[3];
            $path = parse_url($src, PHP_URL_PATH);

            // Only rewrite our own URLs (absolute to APP_URL, or root-relative).
            if (! $path || ! (str_starts_with($src, $base . '/') || str_starts_with($src, '/'))) {
                return $m[0];
            }

            $file = $publicPath . str_replace('/', DIRECTORY_SEPARATOR, rawurldecode($path));

            if (! is_file($file)) {
                // Drop the image rather than let dompdf's placeholder break the fonts.
                return $m[1] . $m[2] . 'data:image/gif;base64,R0lGODlhAQABAAAAACw=' . $m[2];
            }

            return $m[1] . $m[2] . $file . $m[2];
        }, $html);
    }
}
