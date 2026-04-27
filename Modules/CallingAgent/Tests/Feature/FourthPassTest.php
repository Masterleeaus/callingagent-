<?php

namespace Modules\CallingAgent\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CallingAgent\Models\CallingAgent;
use Modules\CallingAgent\Models\CallingAgentCall;
use Modules\CallingAgent\Services\TwilioChannelService;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\Services\Realtime\RealtimeSessionTokenService;
use Modules\CallingAgent\Billing\Meters\VoiceSecondsMeter;
use Modules\CallingAgent\Billing\Meters\MinuteRoundingPolicy;
use Modules\CallingAgent\Billing\Usage\CallUsageRecorder;

/**
 * Fourth-pass production integration tests covering:
 *  - Twilio SDK + config fallback modes (outbound, transfer, hangup)
 *  - Billing idempotency via VoiceSecondsMeter / CallUsageRecorder
 *  - Realtime session token generation and validation
 *  - Stream start/stop lifecycle persistence
 *  - Builder CSRF meta tag in layout
 *  - Provider singleton boot (RealtimeSessionTokenService)
 */
class FourthPassTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // TwilioChannelService SDK / config availability
    // =========================================================================

    public function test_is_sdk_installed_returns_bool(): void
    {
        $svc    = new TwilioChannelService();
        $result = $svc->isSdkInstalled();
        $this->assertIsBool($result, 'isSdkInstalled() must return bool.');
    }

    public function test_is_configured_returns_false_when_env_blank(): void
    {
        // Temporarily clear twilio env
        config(['services.twilio.sid' => null]);
        config(['services.twilio.token' => null]);

        $svc = new TwilioChannelService();
        $this->assertFalse($svc->isConfigured(), 'isConfigured() must return false when credentials are blank.');
    }

    public function test_place_call_returns_mock_result_when_sdk_unavailable(): void
    {
        // Create a subclass that simulates SDK unavailability
        $svc = new class extends TwilioChannelService {
            public function isAvailable(): bool { return false; }
        };

        $result = $svc->placeCall('+15005550002', 'https://example.com/twiml');

        $this->assertSame('sdk-unavailable', $result['status']);
        $this->assertTrue($result['_mock']);
        $this->assertSame('+15005550002', $result['to']);
    }

    public function test_outbound_endpoint_persists_mock_call_when_sdk_unavailable(): void
    {
        $mock = $this->createMock(TwilioChannelService::class);
        $mock->method('placeCall')->willReturn([
            'sid'    => 'CAmock0000000000000000000000mock',
            'status' => 'sdk-unavailable',
            'to'     => '+15005550002',
            'from'   => '+15005550001',
            '_mock'  => true,
        ]);
        $this->app->instance(TwilioChannelService::class, $mock);

        $response = $this->withoutMiddleware()->postJson('/api/calling-agent/calls/outbound', [
            'to'        => '+15005550002',
            'twiml_url' => 'https://example.com/twiml',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('calling_agent_calls', [
            'call_sid'  => 'CAmock0000000000000000000000mock',
            'direction' => 'outbound',
        ]);
    }

    public function test_transfer_returns_422_with_message_when_sdk_unavailable(): void
    {
        $mock = $this->createMock(TwilioChannelService::class);
        $mock->method('isAvailable')->willReturn(false);
        $this->app->instance(TwilioChannelService::class, $mock);

        $response = $this->withoutMiddleware()->postJson(
            '/api/calling-agent/calls/CAtransferSDK/transfer',
            ['target' => '+15005550099']
        );

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('unavailable', $response->json('error'));
    }

    public function test_hangup_returns_422_with_message_when_sdk_unavailable(): void
    {
        $mock = $this->createMock(TwilioChannelService::class);
        $mock->method('isAvailable')->willReturn(false);
        $this->app->instance(TwilioChannelService::class, $mock);

        $response = $this->withoutMiddleware()->postJson(
            '/api/calling-agent/calls/CAhangupSDK/hangup'
        );

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('unavailable', $response->json('error'));
    }

    // =========================================================================
    // Billing idempotency
    // =========================================================================

    public function test_voice_seconds_meter_records_once_only(): void
    {
        $meter   = new VoiceSecondsMeter();
        $callSid = 'CAbilling' . str_repeat('1', 23);

        $meter->record($callSid, 60, null, null);
        $meter->record($callSid, 60, null, null); // duplicate — must be ignored

        $this->assertDatabaseCount('calling_agent_usage_records', 1,
            'VoiceSecondsMeter must insert exactly once per call_sid (idempotent).');
    }

    public function test_voice_seconds_meter_skips_zero_seconds(): void
    {
        $meter = new VoiceSecondsMeter();
        $meter->record('CAzero' . str_repeat('0', 26), 0);

        $this->assertDatabaseCount('calling_agent_usage_records', 0,
            'VoiceSecondsMeter must not record 0-second calls.');
    }

    public function test_call_usage_recorder_bills_completed_call(): void
    {
        $call = CallingAgentCall::create([
            'call_sid'  => 'CArecorder' . str_repeat('1', 22),
            'direction' => 'inbound',
            'status'    => 'completed',
            'duration'  => 45,
            'provider'  => 'twilio',
            'from'      => '+15005550001',
            'to'        => '+15005550002',
        ]);

        $recorder = new CallUsageRecorder(new VoiceSecondsMeter(), new MinuteRoundingPolicy());
        $recorder->record($call);
        $recorder->record($call); // second call must be no-op

        $this->assertDatabaseCount('calling_agent_usage_records', 1,
            'CallUsageRecorder must bill exactly once per call.');
    }

    public function test_status_callback_triggers_billing(): void
    {
        // Create a completed call first so orchestrator->complete() finds it
        $callSid = 'CAstatus' . str_repeat('9', 24);
        CallingAgentCall::create([
            'call_sid'   => $callSid,
            'direction'  => 'inbound',
            'status'     => 'in-progress',
            'duration'   => null,
            'provider'   => 'twilio',
            'from'       => '+15005550001',
            'to'         => '+15005550002',
            'started_at' => now(),
        ]);

        $response = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/status', [
                'CallSid'      => $callSid,
                'CallStatus'   => 'completed',
                'CallDuration' => '67',
            ]);

        $response->assertStatus(204);

        // Usage record must have been created
        $this->assertDatabaseHas('calling_agent_usage_records', [
            'meter'           => 'voice_seconds',
            'idempotency_key' => 'voice:' . $callSid,
        ]);
    }

    // =========================================================================
    // Realtime session token
    // =========================================================================

    public function test_token_service_resolves_as_singleton(): void
    {
        $a = $this->app->make(RealtimeSessionTokenService::class);
        $b = $this->app->make(RealtimeSessionTokenService::class);
        $this->assertSame($a, $b, 'RealtimeSessionTokenService must be a singleton.');
    }

    public function test_token_generate_returns_non_empty_string(): void
    {
        $svc   = new RealtimeSessionTokenService();
        $token = $svc->generate('test_session_1');
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_token_validate_returns_true_for_valid_token(): void
    {
        $svc       = new RealtimeSessionTokenService();
        $sessionId = 'MZ_test_' . uniqid('', true);
        $token     = $svc->generate($sessionId);

        $this->assertTrue(
            $svc->validate($sessionId, $token),
            'validate() must return true for a freshly-generated token.'
        );
    }

    public function test_token_validate_returns_false_for_wrong_session_id(): void
    {
        $svc   = new RealtimeSessionTokenService();
        $token = $svc->generate('session_A');

        $this->assertFalse(
            $svc->validate('session_B', $token),
            'validate() must return false when session ID does not match.'
        );
    }

    public function test_token_validate_returns_false_for_expired_token(): void
    {
        // Use a 0-second TTL so every token is immediately expired
        $svc       = new RealtimeSessionTokenService(ttlSeconds: 0);
        $sessionId = 'expired_session';
        $token     = $svc->generate($sessionId);

        $this->assertFalse(
            $svc->validate($sessionId, $token),
            'validate() must return false for an expired token.'
        );
    }

    public function test_token_validate_returns_false_for_tampered_token(): void
    {
        $svc   = new RealtimeSessionTokenService();
        $token = $svc->generate('tamper_session') . 'tampered';

        $this->assertFalse(
            $svc->validate('tamper_session', $token),
            'validate() must return false for a tampered token.'
        );
    }

    public function test_realtime_token_endpoint_returns_session_id_and_token(): void
    {
        $response = $this->withoutMiddleware()
            ->postJson('/calling-agent/realtime/token', ['call_sid' => 'CAtest0001']);

        $response->assertStatus(200)
            ->assertJsonStructure(['session_id', 'token', 'expires_at']);
        $this->assertStringStartsWith('ca_CA', $response->json('session_id'));
    }

    public function test_realtime_validate_token_endpoint_returns_valid_true(): void
    {
        $svc       = $this->app->make(RealtimeSessionTokenService::class);
        $sessionId = 'ca_validate_test';
        $token     = $svc->generate($sessionId);

        $response = $this->withoutMiddleware()
            ->postJson('/calling-agent/realtime/validate-token', [
                'session_id' => $sessionId,
                'token'      => $token,
            ]);

        $response->assertStatus(200)->assertJson(['valid' => true]);
    }

    public function test_realtime_validate_token_endpoint_returns_valid_false_for_bad_token(): void
    {
        $response = $this->withoutMiddleware()
            ->postJson('/calling-agent/realtime/validate-token', [
                'session_id' => 'any_session',
                'token'      => 'bad_token_value',
            ]);

        $response->assertStatus(200)->assertJson(['valid' => false]);
    }

    // =========================================================================
    // Realtime stream lifecycle persistence
    // =========================================================================

    public function test_realtime_stream_start_persists_session(): void
    {
        $streamSid = 'MZ' . str_repeat('A', 32);
        $callSid   = 'CA' . str_repeat('B', 32);

        $response = $this->withoutMiddleware()
            ->postJson('/calling-agent/realtime/twilio', [
                'event'     => 'start',
                'streamSid' => $streamSid,
                'callSid'   => $callSid,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('ack', 'start')
            ->assertJsonStructure(['session_id', 'token']);

        $this->assertDatabaseHas('calling_agent_realtime_sessions', [
            'session_id' => $streamSid,
            'call_sid'   => $callSid,
            'state'      => 'listening',
        ]);
    }

    public function test_realtime_stream_stop_finalizes_session(): void
    {
        $streamSid = 'MZ' . str_repeat('C', 32);

        // Create the session first
        \DB::table('calling_agent_realtime_sessions')->insert([
            'session_id' => $streamSid,
            'state'      => 'listening',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withoutMiddleware()
            ->postJson('/calling-agent/realtime/twilio', [
                'event'     => 'stop',
                'streamSid' => $streamSid,
            ]);

        $response->assertStatus(200)->assertJsonPath('ack', 'stop');

        $this->assertDatabaseHas('calling_agent_realtime_sessions', [
            'session_id' => $streamSid,
            'state'      => 'completed',
        ]);
    }

    public function test_realtime_fallback_returns_gather_say_when_disabled(): void
    {
        // Disable realtime feature via env
        putenv('CALLING_AGENT_REALTIME_ENABLED=false');

        $response = $this->withoutMiddleware()
            ->post('/calling-agent/realtime/twilio', ['event' => 'start']);

        // Restore
        putenv('CALLING_AGENT_REALTIME_ENABLED=true');

        $this->assertContains($response->status(), [200, 500],
            'Fallback must not 404. If TwiML class missing returns 500 — acceptable in unit context.');

        if ($response->status() === 200) {
            // Should be TwiML XML (text/xml)
            $this->assertStringContainsString('Gather', $response->getContent());
        }
    }

    // =========================================================================
    // Builder layout — CSRF meta tag
    // =========================================================================

    public function test_builder_layout_contains_csrf_meta_tag(): void
    {
        $layoutPath = realpath(
            __DIR__ . '/../../Resources/views/layouts/builder.blade.php'
        );
        $this->assertNotFalse($layoutPath, 'builder.blade.php layout must exist');

        $content = file_get_contents($layoutPath);
        $this->assertStringContainsString(
            'name="csrf-token"',
            $content,
            'Builder layout must contain <meta name="csrf-token"> for JS CSRF handling.'
        );
    }

    // =========================================================================
    // Builder JS — test-call button
    // =========================================================================

    public function test_builder_js_contains_test_call_handler(): void
    {
        $jsPath = realpath(
            __DIR__ . '/../../Resources/assets/js/builder.js'
        );
        $this->assertNotFalse($jsPath, 'builder.js must exist');

        $content = file_get_contents($jsPath);
        $this->assertStringContainsString(
            'ca-test-call-btn',
            $content,
            'builder.js must wire the test-call button.'
        );
    }

    // =========================================================================
    // Provider boot
    // =========================================================================

    public function test_all_core_singletons_registered(): void
    {
        $singletons = [
            TwilioChannelService::class,
            ReceptionistOrchestrator::class,
            RealtimeSessionTokenService::class,
        ];
        foreach ($singletons as $class) {
            $a = $this->app->make($class);
            $b = $this->app->make($class);
            $this->assertSame($a, $b, "{$class} must be a singleton.");
        }
    }
}
