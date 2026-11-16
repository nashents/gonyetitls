<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal WhatsApp Cloud API (Meta) client: uploads a PDF and sends it either
 * inside an approved template or as a plain document message. See
 * config/services.php 'whatsapp' for the template contract.
 */
class WhatsAppService
{
    public function isConfigured(): bool
    {
        return (bool) config('services.whatsapp.token') && (bool) config('services.whatsapp.phone_number_id');
    }

    /** Digits only, in international form; null when the number is unusable. */
    public function normalizeNumber(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = config('services.whatsapp.default_country_code', '263') . substr($digits, 1);
        }

        return strlen($digits) >= 9 ? $digits : null;
    }

    /**
     * Send a PDF. With $template the document rides in the template header and
     * $bodyParams fill {{1}}..{{n}}; without it $caption is the message text.
     *
     * @throws RuntimeException on any API failure
     */
    public function sendDocument(string $to, string $pdf, string $filename, string $caption, ?string $template = null, array $bodyParams = []): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('WhatsApp is not configured.');
        }

        $number = $this->normalizeNumber($to);
        if (! $number) {
            throw new RuntimeException("Invalid WhatsApp number: {$to}");
        }

        $mediaId = $this->uploadPdf($pdf, $filename);
        $document = ['id' => $mediaId, 'filename' => $filename];

        if ($template) {
            $payload = [
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => config('services.whatsapp.template_language', 'en')],
                    'components' => [
                        ['type' => 'header', 'parameters' => [['type' => 'document', 'document' => $document]]],
                        ['type' => 'body', 'parameters' => array_map(
                            fn ($value) => ['type' => 'text', 'text' => (string) $value],
                            array_values($bodyParams)
                        )],
                    ],
                ],
            ];
        } else {
            $payload = ['type' => 'document', 'document' => $document + ['caption' => $caption]];
        }

        $response = Http::withToken(config('services.whatsapp.token'))
            ->timeout(30)
            ->post($this->url('messages'), ['messaging_product' => 'whatsapp', 'to' => $number] + $payload);

        if (! $response->successful()) {
            throw new RuntimeException('WhatsApp send failed: ' . $this->error($response));
        }
    }

    private function uploadPdf(string $pdf, string $filename): string
    {
        $response = Http::withToken(config('services.whatsapp.token'))
            ->timeout(60)
            ->attach('file', $pdf, $filename, ['Content-Type' => 'application/pdf'])
            ->post($this->url('media'), ['messaging_product' => 'whatsapp', 'type' => 'application/pdf']);

        if (! $response->successful() || ! $response->json('id')) {
            throw new RuntimeException('WhatsApp media upload failed: ' . $this->error($response));
        }

        return $response->json('id');
    }

    private function url(string $endpoint): string
    {
        return 'https://graph.facebook.com/' . config('services.whatsapp.api_version', 'v21.0')
            . '/' . config('services.whatsapp.phone_number_id') . '/' . $endpoint;
    }

    private function error($response): string
    {
        return $response->json('error.message') ?: ('HTTP ' . $response->status());
    }
}
