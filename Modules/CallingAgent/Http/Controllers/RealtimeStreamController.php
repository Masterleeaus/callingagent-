<?php

namespace Modules\CallingAgent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CallingAgent\Services\Realtime\TwilioMediaStreamRelay;
use Modules\CallingAgent\Services\Realtime\RealtimeSessionTokenService;

/**
 * Handles Twilio Media Stream HTTP lifecycle events (start / media / stop)
 * and exposes a session-token endpoint for WebSocket authentication.
 *
 * Lifecycle flow:
 *   1. Twilio sends POST "start"  → create/update realtime session record
 *   2. Twilio sends POST "media"  → normalize, count frames (no DB per-frame)
 *   3. Twilio sends POST "stop"   → finalize session, emit transcripts hook
 *
 * When CALLING_AGENT_REALTIME_ENABLED=false or the realtime feature flag is
 * off, the endpoint returns a Gather/Say TwiML fallback instead.
 */
final class RealtimeStreamController
{
    public function __construct(
        private RealtimeSessionTokenService $tokenService,
    ) {}

    // ── Main webhook dispatcher ───────────────────────────────────────────────

    public function twilio(Request $request): mixed
    {
        // Fallback: realtime disabled via feature flag or env
        if (!$this->isRealtimeEnabled()) {
            return $this->gatherSayFallback();
        }

        $event     = $request->input('event');
        $streamSid = $request->input('streamSid') ?? $request->input('stream_sid');
        $callSid   = $request->input('start.callSid')
            ?? $request->input('callSid')
            ?? $request->input('call_sid');

        return match ($event) {
            'start'      => $this->handleStart($request, $streamSid, $callSid),
            'media'      => $this->handleMedia($request, $streamSid),
            'stop'       => $this->handleStop($request, $streamSid),
            'connected'  => response()->json(['ack' => 'connected']),
            default      => response()->json(
                (new TwilioMediaStreamRelay())->normalize($request->all())
            ),
        };
    }

    // ── Token endpoint ────────────────────────────────────────────────────────

    /**
     * POST calling-agent/realtime/token
     * Issues a short-lived HMAC session token for WebSocket authentication.
     */
    public function token(Request $request): \Illuminate\Http\JsonResponse
    {
        $callSid   = $request->input('call_sid');
        $sessionId = $callSid ? 'ca_' . $callSid : 'ca_' . uniqid('', true);
        $token     = $this->tokenService->generate($sessionId);

        return response()->json([
            'session_id' => $sessionId,
            'token'      => $token,
            'expires_at' => now()->addMinutes(60)->toIso8601String(),
        ]);
    }

    /**
     * POST calling-agent/realtime/validate-token
     * Validates a previously issued session token.
     */
    public function validateToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $sessionId = $request->input('session_id');
        $token     = $request->input('token');

        if (!$sessionId || !$token) {
            return response()->json(['valid' => false, 'error' => 'Missing session_id or token'], 400);
        }

        $valid = $this->tokenService->validate($sessionId, $token);
        return response()->json(['valid' => $valid]);
    }

    // ── Stream lifecycle handlers ─────────────────────────────────────────────

    private function handleStart(Request $request, ?string $streamSid, ?string $callSid): \Illuminate\Http\JsonResponse
    {
        $sessionId = $streamSid ?? ('ca_' . uniqid('', true));
        $token     = $this->tokenService->generate($sessionId);

        try {
            \DB::table('calling_agent_realtime_sessions')->updateOrInsert(
                ['session_id' => $sessionId],
                [
                    'provider'   => 'twilio',
                    'call_sid'   => $callSid,
                    'state'      => 'listening',
                    'context'    => json_encode([
                        'start_payload'  => $request->all(),
                        'stream_token'   => $token,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'ack'        => 'start',
            'session_id' => $sessionId,
            'token'      => $token,
        ]);
    }

    private function handleMedia(Request $request, ?string $streamSid): \Illuminate\Http\JsonResponse
    {
        // We don't persist each individual audio frame — just normalize and return
        $normalized = (new TwilioMediaStreamRelay())->normalize($request->all());

        // Optionally: buffer to queue for STT here
        return response()->json(['ack' => 'media', 'sequence' => $normalized['sequence']]);
    }

    private function handleStop(Request $request, ?string $streamSid): \Illuminate\Http\JsonResponse
    {
        if ($streamSid) {
            try {
                \DB::table('calling_agent_realtime_sessions')
                    ->where('session_id', $streamSid)
                    ->update([
                        'state'      => 'completed',
                        'updated_at' => now(),
                    ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['ack' => 'stop', 'session_id' => $streamSid]);
    }

    // ── Fallback TwiML ────────────────────────────────────────────────────────

    /**
     * When realtime is disabled, return a Gather/Say TwiML so the call
     * still gets a voice response rather than silence.
     */
    private function gatherSayFallback(): Response
    {
        $r = new \Twilio\TwiML\VoiceResponse();
        $g = $r->gather([
            'input'        => 'speech dtmf',
            'action'       => url('/calling-agent/webhooks/twilio/voice/gather'),
            'method'       => 'POST',
            'speechTimeout'=> 'auto',
            'timeout'      => 5,
        ]);
        $g->say(
            'Hello, you have reached our AI receptionist. How can I help you today?',
            ['voice' => 'Polly.Amy', 'language' => 'en-US']
        );
        $r->say('I did not hear anything. Please call again or leave a message.');

        return response($r->asXML(), 200)->header('Content-Type', 'text/xml');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function isRealtimeEnabled(): bool
    {
        if (!config('calling-agent.features.realtime_streaming', true)) {
            return false;
        }
        if (env('CALLING_AGENT_REALTIME_ENABLED', null) === 'false') {
            return false;
        }
        return true;
    }
}
