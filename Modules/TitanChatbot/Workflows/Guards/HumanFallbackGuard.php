<?php

namespace Modules\TitanChatbot\Workflows\Guards;

class HumanFallbackGuard
{
    /**
     * Returns false (blocks workflow) when a human agent is available and escalation is requested,
     * indicating that the workflow should hand off to a human rather than continue.
     */
    public function passes(array $context): bool
    {
        $agentAvailable = (bool) ($context['agent_available'] ?? false);
        $escalationRequested = (bool) ($context['escalation_requested'] ?? false);

        if ($agentAvailable && $escalationRequested) {
            return false;
        }

        return true;
    }
}
