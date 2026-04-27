<?php
namespace Modules\CallingAgent\AI\Pipelines;

use Modules\CallingAgent\Services\Realtime\RealtimeConversationSession;
use Modules\CallingAgent\Services\Realtime\TurnTakingStateMachine;

final class ReceptionistRealtimePipeline
{
    public function __construct(private TurnTakingStateMachine $turns = new TurnTakingStateMachine()) {}

    public function handleFinalUtterance(RealtimeConversationSession $session, string $utterance, array $context = []): array
    {
        $this->turns->onUtteranceFinal($session, $utterance);
        $reply = $this->composeReceptionReply($utterance, $context);
        $this->turns->onResponseReady($session, $reply);
        return ['session' => $session, 'reply' => $reply];
    }

    private function composeReceptionReply(string $utterance, array $context): string
    {
        $text = strtolower($utterance);
        if (str_contains($text, 'book') || str_contains($text, 'appointment')) {
            return 'I can help book that. What day and time works best for you?';
        }
        if (str_contains($text, 'human') || str_contains($text, 'staff')) {
            return 'I can connect you with the front desk. Please hold while I transfer you.';
        }
        return $context['fallback_reply'] ?? 'Thanks for calling. How can I help you today?';
    }
}
