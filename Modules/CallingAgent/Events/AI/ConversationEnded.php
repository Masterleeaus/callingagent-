<?php

declare(strict_types=1);

namespace Modules\CallingAgent\Events\AI;

use Modules\CallingAgent\AI\Context\SessionIdentity;
use Modules\CallingAgent\AI\Usage\UsageRecord;

class ConversationEnded
{
    public function __construct(
        public SessionIdentity $session,
        public int $turnCount = 0,
        public ?UsageRecord $totalUsage = null,
    ) {}
}
