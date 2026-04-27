<?php
namespace Modules\CallingAgent\Services\Providers\OpenAI;

use Modules\CallingAgent\Contracts\RealtimeVoiceProvider;
use Modules\CallingAgent\Contracts\STTProvider;

final class OpenAIRealtimeProvider implements RealtimeVoiceProvider, STTProvider
{
    public function openSession(array $context): array { return ['provider' => 'openai', 'session_id' => $context['session_id'] ?? uniqid('oa_', true)]; }
    public function receiveMediaFrame(array $frame): array { return ['provider' => 'openai', 'event' => 'frame', 'sequence' => $frame['sequence'] ?? null]; }
    public function closeSession(string $sessionId): array { return ['provider' => 'openai', 'session_id' => $sessionId, 'closed' => true]; }
    public function transcribe(string $audioUrl, array $options = []): array { return ['provider' => 'openai', 'audio_url' => $audioUrl, 'text' => null, 'options' => $options]; }
    public function supportsStreaming(): bool { return true; }
}
