<?php
namespace Modules\CallingAgent\AI\Tools;

final class IntentClassifierTool
{
    public function classify(string $text): array
    {
        $t = strtolower($text);
        $intent = match (true) {
            str_contains($t, 'book') || str_contains($t, 'appointment') => 'booking',
            str_contains($t, 'price') || str_contains($t, 'cost') => 'pricing',
            str_contains($t, 'complaint') || str_contains($t, 'cancel') => 'support',
            str_contains($t, 'human') || str_contains($t, 'agent') => 'handoff',
            default => 'general',
        };
        return ['intent' => $intent, 'confidence' => $intent === 'general' ? 0.45 : 0.8];
    }
}
