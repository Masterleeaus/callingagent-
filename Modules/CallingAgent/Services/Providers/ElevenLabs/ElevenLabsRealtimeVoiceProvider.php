<?php
namespace Modules\CallingAgent\Services\Providers\ElevenLabs;

use Modules\CallingAgent\Contracts\RealtimeVoiceProvider;
use Modules\CallingAgent\Contracts\TTSProvider;

final class ElevenLabsRealtimeVoiceProvider implements RealtimeVoiceProvider, TTSProvider
{
    public function openSession(array $context): array
    {
        return ['provider' => 'elevenlabs', 'session_id' => $context['session_id'] ?? uniqid('el_', true), 'context' => $context];
    }

    public function receiveMediaFrame(array $frame): array
    {
        return ['provider' => 'elevenlabs', 'event' => 'media_frame_received', 'sequence' => $frame['sequence'] ?? null];
    }

    public function closeSession(string $sessionId): array
    {
        return ['provider' => 'elevenlabs', 'session_id' => $sessionId, 'closed' => true];
    }

    public function synthesize(string $text, array $voice = [], array $options = []): array
    {
        return ['provider' => 'elevenlabs', 'text' => $text, 'voice' => $voice, 'options' => $options];
    }

    public function streamUrl(string $text, array $voice = [], array $options = []): ?string
    {
        return $options['stream_url'] ?? null;
    }
}
