<?php

namespace Modules\TitanChatbot\Workflows\Guards;

class ConfidenceThresholdGuard
{
    private float $threshold;

    public function __construct(float $threshold = 0.6)
    {
        $this->threshold = $threshold;
    }

    public function passes(array $context): bool
    {
        $confidence = (float) ($context['confidence'] ?? 1.0);

        return $confidence >= $this->threshold;
    }
}
