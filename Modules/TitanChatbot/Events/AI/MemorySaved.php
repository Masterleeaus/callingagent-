<?php

namespace Modules\TitanChatbot\Events\AI;

class MemorySaved
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $role,
        public readonly string $content,
    ) {}
}
