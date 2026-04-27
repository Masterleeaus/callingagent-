<?php

namespace Modules\TitanChatbot\Automation\Handlers;

use Illuminate\Support\Facades\Log;

class SendWelcomeBannerHandler
{
    public function handle(array $context): void
    {
        $chatbot = $context['chatbot'] ?? null;
        $conversationId = $context['conversation_id'] ?? null;

        $message = 'Welcome! How can I help you today?';

        if ($chatbot !== null && isset($chatbot->config['welcome_banner'])) {
            $message = (string) $chatbot->config['welcome_banner'];
        }

        Log::channel('titan_chatbot')->info('SendWelcomeBannerHandler: sending welcome banner', [
            'conversation_id' => $conversationId,
            'message' => $message,
        ]);

        event('titan_chatbot.welcome_banner', [
            'conversation_id' => $conversationId,
            'message' => $message,
            'chatbot' => $chatbot,
        ]);
    }
}
