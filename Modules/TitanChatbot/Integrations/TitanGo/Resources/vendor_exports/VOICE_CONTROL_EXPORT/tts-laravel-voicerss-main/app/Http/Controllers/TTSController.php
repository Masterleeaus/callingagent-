<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TTSController extends Controller
{
    public function speak(Request $request)
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'min:2', 'max:1000'],
            'hl'   => ['nullable', 'string'],
        ]);

        $apiKey = env('VOICERSS_KEY');
        if (!$apiKey) {
            return back()->withErrors(['text' => 'Configure VOICERSS_KEY no .env'])->withInput();
        }

        // Parâmetros padrão + idioma opcional
        $params = [
            'key' => $apiKey,
            'hl'  => $data['hl'] ?? env('VOICERSS_LANG', 'pt-br'),
            'src' => $data['text'],
            'c'   => env('VOICERSS_CODEC', 'MP3'),
            'f'   => env('VOICERSS_FORMAT', '44khz_16bit_stereo'),
        ];

        // Chamada (Voice RSS aceita GET com query string)
        $response = Http::timeout(20)->get('https://api.voicerss.org/', $params);

        if (!$response->ok()) {
            return back()->withErrors(['text' => 'Falha na requisição TTS (HTTP '.$response->status().').'])->withInput();
        }

        $body = $response->body();

        // A API retorna string começando com "ERROR" em caso de erro
        if (str_starts_with($body, 'ERROR')) {
            return back()->withErrors(['text' => "Voice RSS: $body"])->withInput();
        }

        // Salvar o MP3 em storage/app/public/tts/...
        $dir = 'tts';
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        // Cache simples por hash (mesmo texto+idioma => mesmo arquivo)
        $hash = substr(hash('sha256', ($params['hl'].'|'.$data['text'])), 0, 16);
        $filename = "{$dir}/tts_{$hash}.mp3";

        if (!Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->put($filename, $body);
        }

        return view('welcome', [
            'audioUrl' => Storage::url($filename),
            'text'     => $data['text'],
            'hl'       => $params['hl'],
            'download' => url(Storage::url($filename)),
        ]);
    }
}
