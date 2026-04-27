<?php
namespace Modules\CallingAgent\Services\Analytics;

final class TalkRatioScorer
{
    public function score(array $turns): array
    {
        $caller = count(array_filter($turns, fn($t) => ($t['speaker'] ?? null) === 'caller'));
        $agent = count(array_filter($turns, fn($t) => ($t['speaker'] ?? null) === 'agent'));
        $total = max(1, $caller + $agent);
        return ['caller_ratio' => round($caller / $total, 2), 'agent_ratio' => round($agent / $total, 2)];
    }
}
