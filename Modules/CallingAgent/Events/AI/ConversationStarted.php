<?php

declare(strict_types=1);

namespace Modules\CallingAgent\Events\AI;

use Modules\CallingAgent\AI\Context\SessionIdentity;

class ConversationStarted
{
    public function __construct(
        public SessionIdentity $session,
        public ?int $agentId = null,
    ) {}
}
