<?php

namespace Modules\TitanChatbot\Automation\Handlers;

use Illuminate\Support\Facades\Log;

class SendSuggestedPromptsHandler
{
    public function handle(array $context): void
    {
        $chatbot = $context['chatbot'] ?? null;
        $conversationId = $context['conversation_id'] ?? null;

        $prompts = [];

        if ($chatbot !== null && isset($chatbot->config['suggested_prompts'])) {
            $prompts = (array) $chatbot->config['suggested_prompts'];
        }

        if (empty($prompts)) {
            return;
        }

        Log::channel('titan_chatbot')->info('SendSuggestedPromptsHandler: dispatching prompts', [
            'conversation_id' => $conversationId,
            'prompts' => $prompts,
        ]);

        event('titan_chatbot.suggested_prompts', [
            'conversation_id' => $conversationId,
            'prompts' => $prompts,
            'chatbot' => $chatbot,
        ]);
    }
}
