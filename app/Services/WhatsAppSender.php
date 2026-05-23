<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppSender
{
    /**
     * Send a WhatsApp message via WAAPI (octopusteam.net).
     *
     * Returns true on success. Failures are logged + returned as false so the
     * caller can decide whether to fall back (e.g. show the OTP on screen in dev).
     */
    public function send(string $phone, string $message): bool
    {
        if (! config('services.waapi.enabled')) {
            Log::info('[WAAPI disabled] would send', ['to' => $phone, 'msg' => $message]);
            return false;
        }

        $phone = $this->normalisePhone($phone);
        if (! $phone) {
            Log::warning('[WAAPI] invalid phone', ['phone' => $phone]);
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->asJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->post(config('services.waapi.url'), [
                    'appkey'      => config('services.waapi.app_key'),
                    'authkey'     => config('services.waapi.auth_key'),
                    'device_uuid' => config('services.waapi.device_uuid'),
                    'to'          => $phone,
                    'message'     => $message,
                ]);

            $ok = $response->successful();
            $body = $response->json();

            // Some providers return 200 with an error inside the body
            if ($ok && is_array($body) && isset($body['message_status'])) {
                $ok = strtolower((string) $body['message_status']) === 'success';
            }

            if (! $ok) {
                Log::warning('[WAAPI] send failed', [
                    'status' => $response->status(),
                    'body'   => $body ?: $response->body(),
                    'to'     => $phone,
                ]);
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::warning('[WAAPI] exception', ['err' => $e->getMessage(), 'to' => $phone]);
            return false;
        }
    }

    /**
     * Normalise an Egyptian phone to the international form WAAPI expects.
     * Accepts 01xxxxxxxxx, +201xxxxxxxxx, 201xxxxxxxxx, or any digit string.
     */
    public function normalisePhone(string $phone): ?string
    {
        $digits = preg_replace('/[^\d]/', '', $phone);
        if (! $digits) return null;

        // Strip leading 00 (international dialling)
        if (str_starts_with($digits, '00')) $digits = substr($digits, 2);

        // Egyptian local (01xxxxxxxxx → 201xxxxxxxxx)
        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            return '20' . substr($digits, 1);
        }

        // Already international
        if (strlen($digits) >= 11 && strlen($digits) <= 15) {
            return $digits;
        }

        return null;
    }

    public function generateOtp(int $length = 5): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }
}
