<?php

namespace Modules\TitanGo\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoiceTtsService
{
    public function synthesizeToUrl(string $text): ?string
    {
        // Optional server-side TTS (e.g., VoiceRSS). Disabled unless TITANGO_TTS_PROVIDER is set.
        $provider = env('TITANGO_TTS_PROVIDER');
        if (!$provider) {
            return null;
        }

        $hash = hash('sha256', $text);
        $path = "titango/tts/{$hash}.txt";

        // MVP: store text file as placeholder (replace with real mp3 generation in Pass 4)
        if (!Storage::disk($this->disk())->exists($path)) {
            Storage::disk($this->disk())->put($path, $text);
        }

        return Storage::disk($this->disk())->url($path);
    }

    protected function disk(): string
    {
        return env('TITANGO_TTS_DISK', 'public');
    }
}
