<?php
namespace Modules\CallingAgent\Services\Realtime;

final class SilenceTimeoutHeuristics
{
    public function shouldPrompt(array $session, int $nowMs): bool
    {
        $lastSpeechMs = (int)($session['last_speech_ms'] ?? $nowMs);
        $threshold = (int)($session['silence_prompt_ms'] ?? 4500);
        return ($nowMs - $lastSpeechMs) >= $threshold;
    }

    public function shouldEnd(array $session, int $nowMs): bool
    {
        $lastSpeechMs = (int)($session['last_speech_ms'] ?? $nowMs);
        $threshold = (int)($session['silence_end_ms'] ?? 18000);
        return ($nowMs - $lastSpeechMs) >= $threshold;
    }
}
