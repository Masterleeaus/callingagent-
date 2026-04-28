<?php

namespace Modules\TitanChatbot\Automation\Handlers;

use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Models\Conversation;

class EscalateToAgentHandler
{
    public function handle(array $context): void
    {
        $conversationId = $context['conversation_id'] ?? null;

        if ($conversationId === null) {
            Log::channel('titan_chatbot')->warning('EscalateToAgentHandler: no conversation_id in context');
            return;
        }

        $conversation = Conversation::find($conversationId);

        if ($conversation === null) {
            Log::channel('titan_chatbot')->warning('EscalateToAgentHandler: conversation not found', [
                'conversation_id' => $conversationId,
            ]);
            return;
        }

        $conversation->connect_agent_at = now();
        $conversation->save();

        Log::channel('titan_chatbot')->info('EscalateToAgentHandler: escalated to agent', [
            'conversation_id' => $conversationId,
            'connect_agent_at' => $conversation->connect_agent_at,
        ]);

        event('titan_chatbot.agent_escalation', [
            'conversation_id' => $conversationId,
            'conversation' => $conversation,
        ]);
    }
}
