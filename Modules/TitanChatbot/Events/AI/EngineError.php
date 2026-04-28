<?php

namespace Modules\TitanChatbot\Events\AI;

use Throwable;

class EngineError
{
    public function __construct(
        public readonly Throwable $exception,
        public readonly string $agentClass,
        public readonly string $sessionId,
    ) {}
}
