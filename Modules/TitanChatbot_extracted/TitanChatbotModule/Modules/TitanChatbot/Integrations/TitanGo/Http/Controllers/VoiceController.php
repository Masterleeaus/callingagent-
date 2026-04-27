<?php

namespace Modules\TitanGo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\TitanGo\Services\VoiceActionMapper;
use Modules\TitanGo\Services\TitanZeroActionClient;

class VoiceController extends Controller
{
    public function index()
    {
        return view('titango::voice');
    }

    public function execute(Request $request, VoiceActionMapper $mapper, TitanZeroActionClient $client)
    {
        $previewOnly = (bool) $request->input('preview_only', false);
        $confirm = (bool) $request->input('confirm', false);

        if ($previewOnly) {
            $request->validate([
                'transcript' => 'required|string|max:5000',
                'record_type' => 'nullable|string|max:50',
                'record_id' => 'nullable|integer',
            ]);

            $transcript = $request->input('transcript');
            $mapping = $mapper->map($transcript);

            if (!$mapping) {
                $this->logVoiceEvent('denied', null, $request, $transcript, null, 'No matching action');
                return response()->json(['message' => 'No matching Titan action found.'], 422);
            }

            $payload = [
                'action' => $mapping['action'],
                'record_type' => $request->input('record_type'),
                'record_id' => $request->input('record_id'),
                'input' => [
                    'transcript' => $transcript,
                    'args' => $mapping['args'] ?? [],
                ],
                'source' => 'titango_voice',
            ];

            $this->logVoiceEvent('preview', $mapping['action'], $request, $transcript, $payload);

            return response()->json(['ok' => true, 'payload' => $payload]);
        }

        if ($confirm) {
            $request->validate([
                'action' => 'required|string|max:100',
                'record_type' => 'nullable|string|max:50',
                'record_id' => 'nullable|integer',
                'input' => 'nullable|array',
                'source' => 'nullable|string|max:50',
            ]);

            $payload = $request->only(['action','record_type','record_id','input','source']);
            $transcript = $payload['input']['transcript'] ?? null;

            $this->logVoiceEvent('confirm', $payload['action'], $request, $transcript, $payload);

            $result = $client->dispatch($payload);

            return response()->json([
                'ok' => true,
                'action' => $payload['action'],
                'result' => $result,
            ]);
        }

        return response()->json(['message' => 'Invalid request. Use preview_only or confirm.'], 422);
    }

    protected function logVoiceEvent(string $type, ?string $action, Request $request, ?string $transcript, ?array $payload, ?string $error = null): void
    {
        try {
            $hash = $transcript ? hash('sha256', $transcript) : null;

            \DB::table('titango_voice_events')->insert([
                'user_id' => auth()->id(),
                'company_id' => (function_exists('company') && company()) ? company()->id : null,
                'event_type' => $type,
                'action' => $action,
                'record_type' => $payload['record_type'] ?? $request->input('record_type'),
                'record_id' => $payload['record_id'] ?? $request->input('record_id'),
                'transcript' => $transcript,
                'transcript_hash' => $hash,
                'payload' => $payload ? json_encode($payload) : null,
                'status' => $error ? 'error' : 'ok',
                'error' => $error,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // never block user on logging failure
        }
    }
}
