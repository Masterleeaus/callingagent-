<?php

declare(strict_types=1);

namespace Modules\CallingAgent\Events\AI;

use Modules\CallingAgent\AI\Core\AgentConfig;
use Modules\CallingAgent\AI\Messages\MessageCollection;

class BeforeAgentSend
{
    public function __construct(
        public MessageCollection $messages,
        public AgentConfig $config,
        public ?string $callSid = null,
    ) {}
}
