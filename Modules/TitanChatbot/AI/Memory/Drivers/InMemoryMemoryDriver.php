<?php
namespace Modules\TitanChatbot\AI\Memory\Drivers;

use Modules\TitanChatbot\AI\Memory\MemoryDriverInterface;

class InMemoryMemoryDriver implements MemoryDriverInterface
{
    private static array $store = [];

    public function get(string $sessionId): array
    {
        return static::$store[$sessionId] ?? [];
    }

    public function push(string $sessionId, string $role, string $content): void
    {
        static::$store[$sessionId][] = ['role' => $role, 'content' => $content];
    }

    public function forget(string $sessionId): void
    {
        unset(static::$store[$sessionId]);
    }

    public static function flush(): void
    {
        static::$store = [];
    }
}
