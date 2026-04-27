<?php
namespace Modules\CallingAgent\Services\Analytics;

final class CallMetricsAggregator
{
    public function aggregate(array $calls): array
    {
        $count = count($calls);
        $duration = array_sum(array_map(fn($c) => (int)($c['duration'] ?? 0), $calls));
        return ['calls' => $count, 'seconds' => $duration, 'avg_seconds' => $count ? round($duration / $count, 2) : 0];
    }
}
