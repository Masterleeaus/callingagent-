<?php

namespace Modules\TitanChatbot\Events\AI;

class BeforeToolExecution
{
    public function __construct(
        public readonly string $toolName,
        public readonly array $arguments,
        public readonly string $sessionId,
    ) {}
}
