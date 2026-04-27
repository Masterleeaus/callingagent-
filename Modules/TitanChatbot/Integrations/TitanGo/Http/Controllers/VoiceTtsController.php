<?php

namespace Modules\TitanGo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\TitanGo\Services\VoiceTtsService;

class VoiceTtsController extends Controller
{
    public function speak(Request $request, VoiceTtsService $tts)
    {
        $request->validate([
            'text' => 'required|string|max:500',
        ]);

        // If no server TTS configured, return a clear error (client can fall back to browser TTS).
        $url = $tts->synthesizeToUrl($request->input('text'));

        if (!$url) {
            return response()->json([
                'ok' => false,
                'message' => 'Server TTS not configured. Use browser TTS.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'url' => $url,
        ]);
    }
}
