<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppOtpSender
{
    public function send(string $recipient, string $code): void
    {
        $token = config('services.wasender.api_token');
        if (! $token) {
            throw new WhatsAppOtpDeliveryException(
                'WhatsApp OTP delivery is not configured. Please configure the WASender API token.',
            );
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->post('https://wasenderapi.com/api/send-message', [
                    'to' => $recipient,
                    'text' => "Your AURA verification code is {$code}. It expires in 5 minutes. Do not share this code.",
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('WASender WhatsApp OTP delivery could not connect.', [
                'recipient_suffix' => substr($recipient, -4),
            ]);

            throw new WhatsAppOtpDeliveryException(
                'We could not send a verification code right now. Please try again.',
                previous: $exception,
            );
        }

        if (! $response->successful()) {
            Log::warning('WASender WhatsApp OTP delivery was rejected.', [
                'recipient_suffix' => substr($recipient, -4),
                'status' => $response->status(),
            ]);

            throw new WhatsAppOtpDeliveryException(
                'We could not send a verification code to that WhatsApp number. Please check the number and try again.',
            );
        }
    }
}
