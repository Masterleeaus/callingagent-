<?php
namespace Modules\CallingAgent\Billing\Meters;

final class MinuteRoundingPolicy
{
    public function billableSeconds(int $actualSeconds, int $increment = 60): int
    {
        return (int) (ceil(max(1, $actualSeconds) / $increment) * $increment);
    }
}
