<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Signals\ChatbotAnalyticsSignal;

class ChatbotAnalyticsService
{
    private const CACHE_TTL_SECONDS = 86400; // 24 hours

    public function __construct(private readonly ChatbotAnalyticsSignal $signal) {}

    /**
     * Record an analytics event for a chatbot.
     * Increments a daily counter stored under:
     *   titan_chatbot_analytics:{chatbotId}:{event}:{date}
     */
    public function record(string $event, array $data = []): void
    {
        $chatbotId = $data['chatbot_id'] ?? 0;
        $date      = now()->toDateString();
        $key       = "titan_chatbot_analytics:{$chatbotId}:{$event}:{$date}";

        Cache::increment($key);
        Cache::put($key . ':last', array_merge($data, ['recorded_at' => now()->toIso8601String()]), self::CACHE_TTL_SECONDS);

        $this->signal->emit($event, $data);

        Log::channel('titan_chatbot')->debug('ChatbotAnalyticsService: event recorded', [
            'event'      => $event,
            'chatbot_id' => $chatbotId,
            'date'       => $date,
        ]);
    }

    /**
     * Get an analytics summary for a chatbot covering the last 30 days.
     *
     * @return array<string, array<string, int>>
     */
    public function getSummary(int $chatbotId): array
    {
        $summary = [];
        $events  = [
            'conversation_started',
            'conversation_ended',
            'message_generated',
            'voice_session',
            'knowledge_hit',
            'agent_takeover',
        ];

        foreach ($events as $event) {
            $summary[$event] = [];
            for ($day = 0; $day < 30; $day++) {
                $date  = now()->subDays($day)->toDateString();
                $key   = "titan_chatbot_analytics:{$chatbotId}:{$event}:{$date}";
                $count = (int) Cache::get($key, 0);
                if ($count > 0) {
                    $summary[$event][$date] = $count;
                }
            }
        }

        return $summary;
    }
}
