<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIVoiceAgentService
{
    protected $openaiApiKey;
    protected $elevenLabsApiKey;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.key');
        $this->elevenLabsApiKey = config('services.elevenlabs.key');
    }

    public function transcribe(string $audioUrl)
    {
        // OpenAI Whisper Logic
        $response = Http::withToken($this->openaiApiKey)
            ->attach('file', file_get_contents($audioUrl), 'recording.mp3')
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
            ]);

        return $response->json()['text'] ?? '';
    }

    public function generateResponse(string $text)
    {
        // GPT-4o Logic for decision making
        $response = Http::withToken($this->openaiApiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'أنت وكيل ذكاء اصطناعي محترف لمركز اتصال. تحدث باللغة العربية فقط.'],
                    ['role' => 'user', 'content' => $text],
                ],
            ]);

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }

    public function textToSpeech(string $text)
    {
        // ElevenLabs Logic
        $response = Http::withHeaders([
            'xi-api-key' => $this->elevenLabsApiKey,
        ])->post('https://api.elevenlabs.io/v1/text-to-speech/voice-id', [
            'text' => $text,
            'model_id' => 'eleven_turbo_v2_5',
        ]);

        return $response->body(); // Audio binary
    }
}
