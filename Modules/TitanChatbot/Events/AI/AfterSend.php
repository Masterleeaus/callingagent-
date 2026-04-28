<?php

namespace Modules\TitanChatbot\Events\AI;

class AfterSend
{
    public function __construct(
        public readonly string $message,
        public readonly string $reply,
        public readonly string $sessionId,
        public readonly string $agentClass,
    ) {}
}
