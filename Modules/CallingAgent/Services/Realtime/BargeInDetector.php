<?php
namespace Modules\CallingAgent\Services\Realtime;

final class BargeInDetector
{
    public function detect(array $frame, array $assistantState = []): bool
    {
        $isAssistantSpeaking = (bool)($assistantState['speaking'] ?? false);
        $energy = (float)($frame['energy'] ?? 0);
        $speechProbability = (float)($frame['speech_probability'] ?? 0);
        return $isAssistantSpeaking && $energy > 0.62 && $speechProbability > 0.70;
    }
}
