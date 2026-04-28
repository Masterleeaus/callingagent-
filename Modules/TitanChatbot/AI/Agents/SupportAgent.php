<?php

namespace Modules\TitanChatbot\AI\Agents;

/**
 * Support-escalation agent — extends TitanAgent via ConversationAgent.
 * Adds intent-based escalation detection on top of the base conversation flow.
 */
class SupportAgent extends ConversationAgent
{
    public string $system_prompt = 'You are a customer support specialist. Diagnose issues systematically, provide clear troubleshooting steps, and escalate complex or unresolved problems to a human agent. Be empathetic and solution-focused.';

    public float $escalationThreshold = 0.7;

    /** @var string[] */
    private array $escalationTriggers = [
        'speak to a human',
        'talk to someone',
        'real person',
        'manager',
        'supervisor',
        'escalate',
        'not working',
        'still broken',
        'frustrated',
        'angry',
        'complaint',
        'refund',
        'cancel',
    ];

    public function shouldEscalate(string $message, array $context = []): bool
    {
        $lower = strtolower($message);

        foreach ($this->escalationTriggers as $trigger) {
            if (str_contains($lower, $trigger)) {
                return true;
            }
        }

        $confidence = $context['confidence'] ?? 1.0;

        return (float) $confidence < $this->escalationThreshold;
    }
}
