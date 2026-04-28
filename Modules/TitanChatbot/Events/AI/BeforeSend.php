<?php

namespace Modules\TitanChatbot\Events\AI;

class BeforeSend
{
    public function __construct(
        public readonly string $message,
        public readonly string $sessionId,
        public readonly string $agentClass,
    ) {}
}
