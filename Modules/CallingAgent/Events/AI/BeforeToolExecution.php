<?php

declare(strict_types=1);

namespace Modules\CallingAgent\Events\AI;

use Modules\CallingAgent\AI\Tools\ToolDefinition;

class BeforeToolExecution
{
    public function __construct(
        public ToolDefinition $tool,
        public array $arguments,
        public ?string $callSid = null,
    ) {}
}
