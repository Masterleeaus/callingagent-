<?php
namespace Modules\CallingAgent\Services;

final class CallRetryPolicy
{
    public function shouldRetry(array $call, int $attempt): bool
    {
        return $attempt < ($call['max_attempts'] ?? 3) && in_array($call['status'] ?? null, ['busy','no-answer','failed','missed'], true);
    }
    public function nextDelaySeconds(int $attempt): int { return min(3600, 60 * max(1, $attempt) ** 2); }
}
