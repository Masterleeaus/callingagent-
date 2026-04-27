<?php
namespace Modules\CallingAgent\Services\Realtime;

use Modules\CallingAgent\Enums\RealtimeTurnState;

final class TurnTakingStateMachine
{
    public function onSpeechStart(RealtimeConversationSession $session): RealtimeConversationSession
    {
        if ($session->state === RealtimeTurnState::SPEAKING) {
            $session->transition(RealtimeTurnState::INTERRUPTED, ['reason' => 'barge_in']);
        }
        $session->transition(RealtimeTurnState::LISTENING);
        return $session;
    }

    public function onUtteranceFinal(RealtimeConversationSession $session, string $text): RealtimeConversationSession
    {
        $session->turns[] = ['speaker' => 'caller', 'text' => $text, 'final' => true];
        $session->transition(RealtimeTurnState::THINKING);
        return $session;
    }

    public function onResponseReady(RealtimeConversationSession $session, string $text): RealtimeConversationSession
    {
        $session->turns[] = ['speaker' => 'agent', 'text' => $text, 'final' => true];
        $session->transition(RealtimeTurnState::SPEAKING);
        return $session;
    }
}
