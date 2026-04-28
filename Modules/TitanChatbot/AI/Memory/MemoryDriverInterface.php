<?php
namespace Modules\TitanChatbot\AI\Memory;

interface MemoryDriverInterface
{
    public function get(string $sessionId): array;
    public function push(string $sessionId, string $role, string $content): void;
    public function forget(string $sessionId): void;
}
