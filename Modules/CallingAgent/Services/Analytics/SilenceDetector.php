<?php
namespace Modules\CallingAgent\Services\Analytics;

final class SilenceDetector
{
    public function detect(array $frames, int $silenceThreshold = 5): array
    {
        $silent = array_filter($frames, fn($f) => empty($f['payload']) || ($f['energy'] ?? 1) <= 0.01);
        return ['silent_frames' => count($silent), 'is_excessive' => count($silent) >= $silenceThreshold];
    }
}
