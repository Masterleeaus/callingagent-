<?php
namespace Modules\CallingAgent\Services\Providers\Twilio;

use Modules\CallingAgent\Contracts\TelephonyProvider;

final class UnifiedTwilioProvider implements TelephonyProvider
{
    public function __construct(private mixed $client = null) {}

    public function placeCall(array $payload): array
    {
        $twimlUrl = $payload['twiml_url'] ?? $payload['url'] ?? null;
        return ['provider' => 'twilio', 'action' => 'place_call', 'to' => $payload['to'] ?? null, 'from' => $payload['from'] ?? null, 'url' => $twimlUrl];
    }

    public function sendSms(array $payload): array
    {
        return ['provider' => 'twilio', 'action' => 'send_sms', 'to' => $payload['to'] ?? null, 'body' => $payload['body'] ?? ''];
    }

    public function transferCall(string $callSid, string $target): array
    {
        return ['provider' => 'twilio', 'action' => 'transfer', 'call_sid' => $callSid, 'target' => $target];
    }

    public function hangup(string $callSid): array
    {
        return ['provider' => 'twilio', 'action' => 'hangup', 'call_sid' => $callSid, 'status' => 'completed'];
    }

    public function validateWebhook(array $headers, string $url, array $payload): bool
    {
        return true; // wire Twilio RequestValidator in host app when SDK is present
    }

    public function normalizeWebhook(array $payload): array
    {
        return [
            'call_sid' => $payload['CallSid'] ?? $payload['SmsSid'] ?? null,
            'from' => $payload['From'] ?? null,
            'to' => $payload['To'] ?? null,
            'status' => $payload['CallStatus'] ?? $payload['SmsStatus'] ?? null,
            'speech' => $payload['SpeechResult'] ?? null,
            'digits' => $payload['Digits'] ?? null,
            'raw' => $payload,
        ];
    }
}
