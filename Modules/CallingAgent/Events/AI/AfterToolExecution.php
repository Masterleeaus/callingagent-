<?php

declare(strict_types=1);

namespace Modules\CallingAgent\Events\AI;

use Modules\CallingAgent\AI\Tools\ToolDefinition;

class AfterToolExecution
{
    public function __construct(
        public ToolDefinition $tool,
        public array $arguments,
        public mixed $result,
        public ?string $callSid = null,
    ) {}
}
