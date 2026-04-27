<?php
namespace Modules\CallingAgent\Services\Analytics;

final class SentimentScoringService
{
    public function score(string $text): array
    {
        $t = strtolower($text); $score = 0;
        foreach (['great','thanks','good','helpful'] as $w) if (str_contains($t, $w)) $score++;
        foreach (['angry','bad','cancel','complaint'] as $w) if (str_contains($t, $w)) $score--;
        return ['score' => $score, 'label' => $score > 0 ? 'positive' : ($score < 0 ? 'negative' : 'neutral')];
    }
}
