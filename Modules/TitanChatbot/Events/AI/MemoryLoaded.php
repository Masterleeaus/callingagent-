<?php

namespace Modules\TitanChatbot\Events\AI;

class MemoryLoaded
{
    public function __construct(
        public readonly string $sessionId,
        public readonly int $messageCount,
    ) {}
}
