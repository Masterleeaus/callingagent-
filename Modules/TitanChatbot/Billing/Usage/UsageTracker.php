<?php

namespace Modules\TitanChatbot\Billing\Usage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UsageTracker
{
    private const PREFIX = 'titan_chatbot_usage:';
    private const TTL    = 86400 * 31; // 31 days

    public function record(UsageRecord $usage): void
    {
        $record   = $usage->normalize();
        $date     = substr($record->recordedAt, 0, 10) ?: date('Y-m-d');
        $tenantId = $record->tenantId;

        $this->increment("tokens:{$tenantId}:{$date}", $record->totalTokens);
        $this->increment("prompt_tokens:{$tenantId}:{$date}", $record->promptTokens);
        $this->increment("completion_tokens:{$tenantId}:{$date}", $record->completionTokens);

        if ($record->chatbotId) {
            $this->increment("chatbot_tokens:{$record->chatbotId}:{$date}", $record->totalTokens);
        }

        Log::debug('UsageTracker: recorded', $record->toArray());
    }

    public function getTotalTokens(int $tenantId, ?string $date = null): int
    {
        $date = $date ?? date('Y-m-d');
        return (int) Cache::get(self::PREFIX . "tokens:{$tenantId}:{$date}", 0);
    }

    public function getChatbotTokens(int $chatbotId, ?string $date = null): int
    {
        $date = $date ?? date('Y-m-d');
        return (int) Cache::get(self::PREFIX . "chatbot_tokens:{$chatbotId}:{$date}", 0);
    }

    private function increment(string $key, int $by): void
    {
        if ($by <= 0) {
            return;
        }
        Cache::increment(self::PREFIX . $key, $by);
    }
}
