<?php

namespace Modules\TitanChatbot\Events\AI;

class AfterToolExecution
{
    public function __construct(
        public readonly string $toolName,
        public readonly array $arguments,
        public readonly mixed $result,
        public readonly string $sessionId,
    ) {}
}
