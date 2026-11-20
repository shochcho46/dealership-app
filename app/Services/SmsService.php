<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends a single SMS through the same bdbulksms.net gateway the vendor collection SMS uses
 * (see DsrCollectionController). Nothing needs to be set in .env.
 */
class SmsService
{
    const GATEWAY_URL   = 'https://api.bdbulksms.net/api.php?json';
    const GATEWAY_TOKEN = 'c3253885b10f98c971b719b5372a4b34';

    /**
     * @return array{ok: bool, response: string}
     */
    public function send(string $mobile, string $message): array
    {
        $to = self::normalizeMobile($mobile);
        if (!$to) {
            return ['ok' => false, 'response' => 'Invalid mobile number'];
        }

        try {
            $res = Http::asForm()->timeout(15)->post(self::GATEWAY_URL, [
                'to'      => $to,
                'message' => $message,
                'token'   => self::GATEWAY_TOKEN,
            ]);

            $body = trim($res->body());
            $ok   = $res->successful() && !preg_match('/error|invalid|fail|insufficient/i', $body);

            if (!$ok) {
                Log::warning('SMS failed', ['to' => $to, 'status' => $res->status(), 'body' => $body]);
            }

            return ['ok' => $ok, 'response' => mb_substr($body ?: ('HTTP ' . $res->status()), 0, 250)];
        } catch (\Throwable $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());

            return ['ok' => false, 'response' => mb_substr($e->getMessage(), 0, 250)];
        }
    }

    /**
     * Bangladesh mobile → 01XXXXXXXXX. Returns null when it doesn't look like a BD mobile.
     */
    public static function normalizeMobile(string $mobile): ?string
    {
        $digits = preg_replace('/\D/', '', $mobile);
        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) ? $digits : null;
    }
}
