<?php

namespace Modules\TitanChatbot\Signals;

use Illuminate\Support\Facades\Log;

class ChatbotAnalyticsSignal
{
    private const SUPPORTED_EVENTS = [
        'conversation_started',
        'conversation_ended',
        'message_generated',
        'voice_session',
        'knowledge_hit',
        'agent_takeover',
    ];

    public function emit(string $event, array $data = []): void
    {
        if (!in_array($event, self::SUPPORTED_EVENTS, true)) {
            Log::channel('titan_chatbot_analytics')->warning('ChatbotAnalyticsSignal: unsupported event', [
                'event' => $event,
            ]);
            return;
        }

        Log::channel('titan_chatbot_analytics')->info($event, array_merge($data, [
            'emitted_at' => now()->toIso8601String(),
        ]));
    }
}
