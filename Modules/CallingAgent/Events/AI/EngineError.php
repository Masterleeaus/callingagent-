<?php

declare(strict_types=1);

namespace Modules\CallingAgent\Events\AI;

use Modules\CallingAgent\AI\Core\AgentConfig;

class EngineError
{
    public function __construct(
        public \Throwable $exception,
        public AgentConfig $config,
        public ?string $callSid = null,
    ) {}
}
