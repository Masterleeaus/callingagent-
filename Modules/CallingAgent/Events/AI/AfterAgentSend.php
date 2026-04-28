<?php

declare(strict_types=1);

namespace Modules\CallingAgent\Events\AI;

use Modules\CallingAgent\AI\Core\AgentConfig;
use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Usage\UsageRecord;

class AfterAgentSend
{
    public function __construct(
        public MessageCollection $messages,
        public AgentConfig $config,
        public AssistantMessage $response,
        public UsageRecord $usage,
        public ?string $callSid = null,
    ) {}
}
