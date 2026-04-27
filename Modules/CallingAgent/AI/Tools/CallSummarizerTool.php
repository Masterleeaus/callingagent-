<?php
namespace Modules\CallingAgent\AI\Tools;

final class CallSummarizerTool
{
    public function summarize(array $turns): array
    {
        $lines = array_values(array_filter(array_map(fn($t) => $t['text'] ?? null, $turns)));
        $summary = implode(' ', array_slice($lines, -6));
        return ['summary' => mb_substr($summary, 0, 1000), 'turn_count' => count($turns)];
    }
}
