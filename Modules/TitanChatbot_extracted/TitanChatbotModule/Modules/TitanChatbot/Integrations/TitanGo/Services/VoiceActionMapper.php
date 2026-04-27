<?php

namespace Modules\TitanGo\Services;

class VoiceActionMapper
{
    public function map(string $transcript): ?array
    {
        $t = mb_strtolower(trim($transcript));
        $map = config('titango.voice_actions', []);

        foreach ($map as $needle => $action) {
            if ($needle !== '' && str_contains($t, mb_strtolower($needle))) {
                return [
                    'action' => $action,
                    'args' => [],
                ];
            }
        }

        return null;
    }
}
