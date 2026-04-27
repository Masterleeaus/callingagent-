<?php

namespace Modules\TitanChatbot\AI\Pipelines;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\AI\Agents\VoiceAgent;
use Modules\TitanChatbot\Events\VoiceSessionDurationRecorded;

class VoiceConversationPipeline
{
    private string $sessionId = 'voice_default';
    private float $startTime;
    private int $messageCount = 0;

    public function __construct(private VoiceAgent $agent)
    {
        $this->startTime = microtime(true);
    }

    public function setSessionId(string $id): self
    {
        $this->sessionId = $id;

        return $this;
    }

    public function process(string $utterance, array $context = []): string
    {
        $context['session_id'] = $this->sessionId;

        $reply = $this->agent->respond($utterance, $context);

        $this->messageCount++;

        $duration = microtime(true) - $this->startTime;

        Log::info('VoiceConversationPipeline: message processed.', [
            'session_id'    => $this->sessionId,
            'duration_s'    => round($duration, 3),
            'message_count' => $this->messageCount,
        ]);

        Event::dispatch(new VoiceSessionDurationRecorded(
            sessionId:      $this->sessionId,
            durationSeconds: round($duration, 3),
            messageCount:   $this->messageCount,
        ));

        return $reply;
    }
}
