<?php

namespace System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use System\Models\ExtVoiceChatbot;

class TitanGoController extends Controller
{
    public function input(string $uuid, Request $request)
    {
        $payload = $request->validate([
            'session_id'  => ['required','string'],
            'source'      => ['required','string'],
            'mode'        => ['required','string'],
            'input_type'  => ['required','string'],
            'text'        => ['required','string'],
            'context'     => ['nullable','string'],
            'button_id'   => ['nullable','string'],
            'chatbot_uuid'=> ['nullable','string'],
        ]);

        $chatbot = ExtVoiceChatbot::whereUuid($uuid)->first();
        if ($chatbot) {
            DB::table('ext_voicechabot_conversations')->updateOrInsert(
                [
                    'ext_voice_chatbot_id' => $chatbot->id,
                    'session_id' => $payload['session_id'],
                ],
                [
                    'mode' => $payload['mode'],
                    'context' => $payload['context'] ?? null,
                    'last_user_speech_at' => now(),
                    'ended_at' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $event = [
            'conversation_id' => $payload['session_id'],
            'channel' => 'titan-go',
            'source' => $payload['source'] ?? 'titan-go',
            'tenant_id' => null,
            'user_id' => $request->user()?->id,
            'phone' => null,
            'role' => $request->user()?->role ?? null,
            'input_type' => $payload['input_type'],
            'text' => $payload['text'],
            'meta' => [
                'mode' => $payload['mode'],
                'context' => $payload['context'] ?? null,
                'button_id' => $payload['button_id'] ?? null,
                'chatbot_uuid' => $uuid,
            ],
        ];

        $internal = Request::create('/api/v1/titan-core/event', 'POST', $event);
        $internal->headers->set('Accept', 'application/json');
        $internal->headers->set('Content-Type', 'application/json');
        $internal->setUserResolver(fn () => $request->user());

        /** @var SymfonyResponse $resp */
        $resp = app()->handle($internal);

        return response($resp->getContent(), $resp->getStatusCode(), [
            'Content-Type' => $resp->headers->get('Content-Type', 'application/json'),
        ]);
    }

    public function end(string $uuid, Request $request)
    {
        $payload = $request->validate([
            'session_id' => ['required','string'],
        ]);

        $chatbot = ExtVoiceChatbot::whereUuid($uuid)->first();
        if ($chatbot) {
            DB::table('ext_voicechabot_conversations')
                ->where('ext_voice_chatbot_id', $chatbot->id)
                ->where('session_id', $payload['session_id'])
                ->update([
                    'ended_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['status' => 'ended']);
    }

    public function inputWithoutUuid(Request $request)
    {
        $uuid = (string) $request->input('chatbot_uuid', '');
        if ($uuid === '') {
            return response()->json(['error' => 'missing_chatbot_uuid'], 422);
        }
        return $this->input($uuid, $request);
    }

    public function endWithoutUuid(Request $request)
    {
        $uuid = (string) $request->input('chatbot_uuid', '');
        if ($uuid === '') {
            return response()->json(['error' => 'missing_chatbot_uuid'], 422);
        }
        return $this->end($uuid, $request);
    }
}
