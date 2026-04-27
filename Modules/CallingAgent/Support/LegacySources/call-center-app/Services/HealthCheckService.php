<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client as TwilioClient;

class HealthCheckService
{
    public function checkTwilio(): array
    {
        try {
            $client = new TwilioClient(
                config('services.twilio.account_sid'),
                config('services.twilio.auth_token')
            );
            $account = $client->api->v2010->accounts(config('services.twilio.account_sid'))->fetch();
            return [
                'status' => $account->status === 'active' ? 'healthy' : 'degraded',
                'message' => 'Twilio Account is ' . $account->status,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Twilio Error: ' . $e->getMessage(),
            ];
        }
    }

    public function checkOpenAI(): array
    {
        try {
            $response = Http::withToken(config('services.openai.key'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [['role' => 'user', 'content' => 'ping']],
                    'max_tokens' => 5
                ]);

            if ($response->successful()) {
                return ['status' => 'healthy', 'message' => 'OpenAI API responsive'];
            }
            return ['status' => 'unhealthy', 'message' => 'OpenAI Error: ' . $response->body()];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'OpenAI Exception: ' . $e->getMessage()];
        }
    }

    public function checkElevenLabs(): array
    {
        try {
            $response = Http::withHeaders(['xi-api-key' => config('services.elevenlabs.key')])
                ->get('https://api.elevenlabs.io/v1/user/subscription');

            if ($response->successful()) {
                $data = $response->json();
                $remaining = ($data['character_limit'] ?? 0) - ($data['character_count'] ?? 0);
                return [
                    'status' => $remaining > 1000 ? 'healthy' : 'degraded',
                    'message' => "ElevenLabs: {$remaining} characters remaining.",
                    'remaining' => $remaining
                ];
            }
            return ['status' => 'unhealthy', 'message' => 'ElevenLabs Error: ' . $response->body()];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'ElevenLabs Exception: ' . $e->getMessage()];
        }
    }
}
