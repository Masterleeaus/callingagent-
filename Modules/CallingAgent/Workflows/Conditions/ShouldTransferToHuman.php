<?php
namespace Modules\CallingAgent\Workflows\Conditions;

final class ShouldTransferToHuman
{
    public function passes(array $context): bool
    {
        return ($context['intent']['intent'] ?? null) === 'handoff' || ($context['confidence'] ?? 1) < 0.35 || ! empty($context['requires_staff']);
    }
}
