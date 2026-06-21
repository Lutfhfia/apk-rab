<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message using Fonnte API.
     *
     * @param string $target The phone number(s), comma separated
     * @param string $message The message content
     * @return bool
     */
    public static function send(string $target, string $message): bool
    {
        $token = env('FONNTE_TOKEN');

        if (!$token) {
            Log::warning('Fonnte token is not configured. WA message not sent.', [
                'target' => $target,
                'message' => $message,
            ]);
            return false;
        }

        // Clean target number (remove 0 at the front and replace with 62)
        $target = preg_replace('/^0/', '62', $target);

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully via Fonnte.', [
                    'target' => $target,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('Fonnte API returned an error.', [
                'target' => $target,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message via Fonnte.', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
