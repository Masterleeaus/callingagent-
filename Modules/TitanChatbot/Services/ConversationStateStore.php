<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Cache;

class ConversationStateStore
{
    private const PREFIX = 'titan_chatbot_state:';

    public function get(string $sessionId, string $key): mixed
    {
        return data_get($this->all($sessionId), $key);
    }

    public function set(string $sessionId, string $key, mixed $value, int $ttl = 3600): void
    {
        $state = $this->all($sessionId);
        data_set($state, $key, $value);
        Cache::put($this->cacheKey($sessionId), $state, $ttl);
    }

    public function clear(string $sessionId): void
    {
        Cache::forget($this->cacheKey($sessionId));
    }

    public function all(string $sessionId): array
    {
        return Cache::get($this->cacheKey($sessionId), []);
    }

    private function cacheKey(string $sessionId): string
    {
        return self::PREFIX . $sessionId;
    }
}
