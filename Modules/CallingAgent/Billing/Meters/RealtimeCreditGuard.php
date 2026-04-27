<?php
namespace Modules\CallingAgent\Billing\Meters;

final class RealtimeCreditGuard
{
    public function canContinue(int|float $remainingCredits, int $elapsedSeconds, float $ratePerSecond): bool
    {
        return $remainingCredits - ($elapsedSeconds * $ratePerSecond) > $ratePerSecond * 15;
    }
}
