<?php

namespace Modules\TitanChatbot\AI\Memory;

use Illuminate\Support\Facades\Cache;

class ConversationMemoryStore
{
    private const PREFIX = 'titan_chatbot_memory:';
    private const TTL    = 3600; // 1 hour

    public function remember(string $sessionId, string $role, string $content): void
    {
        $key      = self::PREFIX . $sessionId;
        $messages = Cache::get($key, []);

        $messages[] = ['role' => $role, 'content' => $content];

        Cache::put($key, $messages, self::TTL);
    }

    public function recall(string $sessionId, int $limit = 10): array
    {
        $messages = Cache::get(self::PREFIX . $sessionId, []);

        if (count($messages) > $limit) {
            $messages = array_slice($messages, -$limit);
        }

        return $messages;
    }

    public function forget(string $sessionId): void
    {
        Cache::forget(self::PREFIX . $sessionId);
    }
}
