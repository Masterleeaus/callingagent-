<?php
namespace Modules\CallingAgent\AI\Tools;

final class LeadScoringTool
{
    public function score(array $signals): int
    {
        $score = 20;
        foreach (['phone','email','booking_intent','budget','urgency'] as $key) {
            if (! empty($signals[$key])) { $score += 16; }
        }
        return min(100, $score);
    }
}
