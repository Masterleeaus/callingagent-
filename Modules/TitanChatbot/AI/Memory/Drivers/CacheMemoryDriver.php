<?php
namespace Modules\TitanChatbot\AI\Memory\Drivers;

use Modules\TitanChatbot\AI\Memory\MemoryDriverInterface;
use Illuminate\Support\Facades\Cache;

class CacheMemoryDriver implements MemoryDriverInterface
{
    private const PREFIX = 'titan_chatbot_memory:';
    private const TTL    = 3600;

    public function get(string $sessionId): array
    {
        return Cache::get(self::PREFIX . $sessionId, []);
    }

    public function push(string $sessionId, string $role, string $content): void
    {
        $key      = self::PREFIX . $sessionId;
        $messages = Cache::get($key, []);
        $messages[] = ['role' => $role, 'content' => $content];
        Cache::put($key, $messages, self::TTL);
    }

    public function forget(string $sessionId): void
    {
        Cache::forget(self::PREFIX . $sessionId);
    }
}
