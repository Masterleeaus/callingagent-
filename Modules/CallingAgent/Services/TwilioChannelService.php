<?php

namespace Modules\CallingAgent\Services;

use Twilio\TwiML\VoiceResponse;

/**
 * Wraps the Twilio REST SDK with graceful fallbacks when the SDK is not
 * installed or the credentials are missing / unconfigured.
 */
class TwilioChannelService
{
    // ── SDK / config availability ─────────────────────────────────────────────

    /** True when the twilio/sdk Composer package is installed. */
    public function isSdkInstalled(): bool
    {
        return class_exists(\Twilio\Rest\Client::class);
    }

    /** True when both TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN are present. */
    public function isConfigured(): bool
    {
        return !empty(config('services.twilio.sid', env('TWILIO_ACCOUNT_SID')))
            && !empty(config('services.twilio.token', env('TWILIO_AUTH_TOKEN')));
    }

    /** True when the SDK is installed AND credentials are present. */
    public function isAvailable(): bool
    {
        return $this->isSdkInstalled() && $this->isConfigured();
    }

    // ── Client factory ────────────────────────────────────────────────────────

    /**
     * Return a configured Twilio REST client.
     *
     * @throws \RuntimeException when SDK is missing or credentials are absent.
     */
    public function client(): \Twilio\Rest\Client
    {
        if (!$this->isSdkInstalled()) {
            throw new \RuntimeException(
                'Twilio SDK not installed. Run: composer require twilio/sdk'
            );
        }
        if (!$this->isConfigured()) {
            throw new \RuntimeException(
                'Twilio credentials not configured. Set TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN.'
            );
        }
        return new \Twilio\Rest\Client(
            config('services.twilio.sid', env('TWILIO_ACCOUNT_SID')),
            config('services.twilio.token', env('TWILIO_AUTH_TOKEN'))
        );
    }

    // ── Messaging ─────────────────────────────────────────────────────────────

    public function sendSms(string $to, string $body, ?string $from = null): array
    {
        if (!$this->isAvailable()) {
            return $this->sdkUnavailableResponse(['to' => $to, 'body' => $body]);
        }
        $m = $this->client()->messages->create($to, [
            'from' => $from ?: env('TWILIO_FROM_NUMBER'),
            'body' => $body,
        ]);
        return $this->messageProperties($m);
    }

    public function sendWhatsapp(string $to, string $body, ?string $from = null): array
    {
        if (!$this->isAvailable()) {
            return $this->sdkUnavailableResponse(['to' => $to, 'body' => $body]);
        }
        $to   = str_starts_with($to, 'whatsapp:') ? $to : 'whatsapp:' . $to;
        $from = $from ?: env('TWILIO_WHATSAPP_FROM', env('TWILIO_FROM_NUMBER'));
        $from = str_starts_with($from, 'whatsapp:') ? $from : 'whatsapp:' . $from;
        $m    = $this->client()->messages->create($to, ['from' => $from, 'body' => $body]);
        return $this->messageProperties($m);
    }

    // ── Voice ─────────────────────────────────────────────────────────────────

    /**
     * Place an outbound call.
     * Returns a mock result when the SDK or credentials are unavailable so
     * callers can still persist a call log record in a test / seed environment.
     */
    public function placeCall(
        string $to,
        string $url,
        ?string $from = null,
        ?string $statusCallback = null
    ): array {
        if (!$this->isAvailable()) {
            return [
                'sid'    => 'CA' . str_pad('0', 32, '0'),
                'status' => 'sdk-unavailable',
                'to'     => $to,
                'from'   => $from ?: env('TWILIO_FROM_NUMBER', 'unknown'),
                '_mock'  => true,
            ];
        }
        $payload = ['from' => $from ?: env('TWILIO_FROM_NUMBER'), 'url' => $url];
        if ($statusCallback) {
            $payload['statusCallback']      = $statusCallback;
            $payload['statusCallbackEvent'] = ['initiated', 'ringing', 'answered', 'completed'];
        }
        $c = $this->client()->calls->create($to, $payload);
        return ['sid' => $c->sid, 'status' => $c->status, 'to' => $c->to, 'from' => $c->from];
    }

    // ── TwiML helpers ─────────────────────────────────────────────────────────

    public function receptionistTwiML(string $say, string $gatherUrl, array $opts = []): string
    {
        $r = new VoiceResponse();
        $g = $r->gather([
            'input'        => 'speech dtmf',
            'action'       => $gatherUrl,
            'method'       => 'POST',
            'speechTimeout'=> 'auto',
            'timeout'      => $opts['timeout'] ?? 5,
            'numDigits'    => $opts['numDigits'] ?? 1,
        ]);
        $g->say($say, [
            'voice'    => $opts['voice'] ?? 'Polly.Amy',
            'language' => $opts['language'] ?? 'en-US',
        ]);
        $r->say($opts['fallback'] ?? 'I did not hear anything. Please call again or leave a message after the tone.');
        if (!empty($opts['record_url'])) {
            $r->record(['action' => $opts['record_url'], 'maxLength' => 120]);
        }
        return $r->asXML();
    }

    public function sayAndHangup(string $message): string
    {
        $r = new VoiceResponse();
        $r->say($message);
        $r->hangup();
        return $r->asXML();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function messageProperties(object $m): array
    {
        return [
            'sid'       => $m->sid,
            'status'    => $m->status,
            'to'        => $m->to,
            'from'      => $m->from,
            'body'      => $m->body,
            'direction' => $m->direction,
            'price'     => $m->price ?? null,
        ];
    }

    private function sdkUnavailableResponse(array $extra = []): array
    {
        return array_merge([
            'sid'    => null,
            'status' => 'sdk-unavailable',
            '_mock'  => true,
        ], $extra);
    }
}
