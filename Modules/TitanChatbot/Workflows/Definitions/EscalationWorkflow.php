<?php

namespace Modules\TitanChatbot\Workflows\Definitions;

use Modules\TitanChatbot\Workflows\Guards\ConfidenceThresholdGuard;
use Modules\TitanChatbot\Workflows\Guards\HumanFallbackGuard;

class EscalationWorkflow
{
    public function name(): string
    {
        return 'escalation';
    }

    public function steps(): array
    {
        return [
            'DetectEscalationTrigger',
            'NotifyAgent',
            'TransferConversation',
        ];
    }

    public function guards(): array
    {
        return [
            new ConfidenceThresholdGuard(),
            new HumanFallbackGuard(),
        ];
    }
}
