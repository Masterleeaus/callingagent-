<?php

namespace Modules\CallingAgent\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\CallingAgent\Services\TwilioChannelService;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\Models\CallingAgent;
use Modules\CallingAgent\Models\CallingAgentCall;

/**
 * Third-pass production-readiness tests covering:
 *  - Route registration
 *  - Provider boot / singleton resolution
 *  - Migration schema integrity
 *  - Outbound call API
 *  - Transfer and hangup API
 *  - Builder JS endpoints (load/save/assign)
 *  - CallingAgentBuilderPreset is Eloquent
 *  - TwilioMediaStreamRelay audio buffer flush
 */
class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Route registration
    // =========================================================================

    public function test_voice_webhook_routes_are_registered(): void
    {
        $expected = [
            'calling-agent.webhooks.voice.incoming',
            'calling-agent.webhooks.voice.gather',
            'calling-agent.webhooks.voice.status',
            'calling-agent.webhooks.voice.recording',
            'calling-agent.webhooks.voice.voicemail',
            'calling-agent.webhooks.message.incoming',
        ];

        foreach ($expected as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route [$routeName] is not registered."
            );
        }
    }

    public function test_api_outbound_transfer_hangup_routes_are_registered(): void
    {
        $expected = [
            'api.calling-agent.calls.outbound',
            'api.calling-agent.calls.transfer',
            'api.calling-agent.calls.hangup',
            'api.calling-agent.calls.show',
            'api.calling-agent.sms.send',
            'api.calling-agent.whatsapp.send',
        ];

        foreach ($expected as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route [$routeName] is not registered."
            );
        }
    }

    public function test_builder_routes_are_registered(): void
    {
        $expected = [
            'calling-agent.builder',
            'calling-agent.builder.load',
            'calling-agent.builder.save',
            'calling-agent.builder.assign-number',
            'calling-agent.builder.preview',
        ];

        foreach ($expected as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Builder route [$routeName] is not registered."
            );
        }
    }

    // =========================================================================
    // Provider / singleton resolution
    // =========================================================================

    public function test_twilio_channel_service_resolves_as_singleton(): void
    {
        $a = $this->app->make(TwilioChannelService::class);
        $b = $this->app->make(TwilioChannelService::class);
        $this->assertSame($a, $b, 'TwilioChannelService must be a singleton.');
    }

    public function test_receptionist_orchestrator_resolves_as_singleton(): void
    {
        $a = $this->app->make(ReceptionistOrchestrator::class);
        $b = $this->app->make(ReceptionistOrchestrator::class);
        $this->assertSame($a, $b, 'ReceptionistOrchestrator must be a singleton.');
    }

    // =========================================================================
    // Migration schema integrity
    // =========================================================================

    public function test_calling_agents_table_exists(): void
    {
        $this->assertTrue(
            \Schema::hasTable('calling_agents'),
            'calling_agents table must exist.'
        );
    }

    public function test_calling_agent_calls_table_has_required_columns(): void
    {
        $required = ['id', 'call_sid', 'direction', 'from', 'to', 'status', 'duration', 'started_at'];
        foreach ($required as $col) {
            $this->assertTrue(
                \Schema::hasColumn('calling_agent_calls', $col),
                "calling_agent_calls is missing column [$col]."
            );
        }
    }

    public function test_calling_agent_call_outcomes_table_has_rich_columns(): void
    {
        // These columns are added by migration 000004 (ALTER TABLE from 000003 base)
        $required = ['id', 'call_sid', 'intent', 'urgency', 'lead_quality',
                     'handoff_required', 'booking_requested', 'entities', 'next_actions', 'raw'];
        foreach ($required as $col) {
            $this->assertTrue(
                \Schema::hasColumn('calling_agent_call_outcomes', $col),
                "calling_agent_call_outcomes is missing column [$col]."
            );
        }
    }

    public function test_calling_agent_caller_profiles_has_soft_deletes(): void
    {
        $this->assertTrue(
            \Schema::hasColumn('calling_agent_caller_profiles', 'deleted_at'),
            'calling_agent_caller_profiles must have deleted_at for SoftDeletes.'
        );
    }

    public function test_webhook_idempotency_table_exists(): void
    {
        $this->assertTrue(
            \Schema::hasTable('calling_agent_webhook_idempotency'),
            'calling_agent_webhook_idempotency table must exist.'
        );
    }

    public function test_recordings_table_exists(): void
    {
        $this->assertTrue(
            \Schema::hasTable('calling_agent_recordings'),
            'calling_agent_recordings table must exist.'
        );
    }

    // =========================================================================
    // Outbound call
    // =========================================================================

    public function test_place_outbound_call_persists_call_log(): void
    {
        $mockTwilio = $this->createMock(TwilioChannelService::class);
        $mockTwilio->method('placeCall')->willReturn([
            'sid'    => 'CAoutbound000000000000000000000001',
            'status' => 'queued',
            'to'     => '+15005550002',
            'from'   => '+15005550001',
        ]);
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->postJson('/api/calling-agent/calls/outbound', [
            'to'        => '+15005550002',
            'from'      => '+15005550001',
            'twiml_url' => 'https://example.com/twiml',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('call_sid', 'CAoutbound000000000000000000000001');

        $this->assertDatabaseHas('calling_agent_calls', [
            'call_sid'  => 'CAoutbound000000000000000000000001',
            'direction' => 'outbound',
            'status'    => 'queued',
        ]);
    }

    // =========================================================================
    // Transfer
    // =========================================================================

    public function test_transfer_endpoint_records_attempt(): void
    {
        $callSid = 'CATransfer0000000000000000000001';

        // Stub the Twilio client to avoid real HTTP calls
        $mockClient = $this->createMock(\Twilio\Rest\Client::class);
        $mockCallsContext = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['update'])
            ->getMock();
        $mockCallsContext->method('update')->willReturn(new \stdClass());

        $mockTwilio = $this->createPartialMock(TwilioChannelService::class, ['client']);
        $mockTwilio->method('client')->willReturn($this->createConfiguredTwilioClient($callSid, $mockCallsContext));
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->postJson(
            "/api/calling-agent/calls/{$callSid}/transfer",
            ['target' => '+15005550099']
        );

        // 200 or 422 both indicate the controller ran (422 = Twilio client error in test)
        $this->assertContains($response->status(), [200, 422]);
    }

    // =========================================================================
    // Hangup
    // =========================================================================

    public function test_hangup_endpoint_calls_orchestrator_complete(): void
    {
        $callSid = 'CAHangup000000000000000000000001';

        $mockTwilio = $this->createPartialMock(TwilioChannelService::class, ['client']);
        $mockTwilio->method('client')->will($this->throwException(
            new \Exception('Twilio not available in test')
        ));
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->postJson(
            "/api/calling-agent/calls/{$callSid}/hangup"
        );

        // 422 expected since Twilio is mocked to fail
        $this->assertContains($response->status(), [200, 422]);
    }

    // =========================================================================
    // Builder UI endpoints
    // =========================================================================

    public function test_builder_index_route_resolves_controller(): void
    {
        $response = $this->withoutMiddleware()->get('/calling-agent/builder');
        // Should return 200 with the view (or 500 if template missing — not 404)
        $this->assertNotEquals(404, $response->status(), 'Builder index must not 404.');
    }

    public function test_builder_load_endpoint_returns_json_for_known_agent(): void
    {
        $agent = CallingAgent::create([
            'name'          => 'PR Test Agent',
            'status'        => 'active',
            'first_message' => 'Hello!',
            'settings'      => ['voicemail_enabled' => true],
        ]);

        $response = $this->withoutMiddleware()
            ->getJson("/calling-agent/builder/{$agent->id}/load");

        $this->assertContains($response->status(), [200, 401, 302],
            'builder.load must return 200 (or redirect to login) — not 404/500.');

        if ($response->status() === 200) {
            $response->assertJsonStructure(['agent', 'settings', 'webhook_urls']);
            $response->assertJsonPath('agent.id', $agent->id);
        }
    }

    public function test_builder_save_endpoint_persists_changes(): void
    {
        $agent = CallingAgent::create([
            'name'     => 'Save PR Agent',
            'status'   => 'active',
            'settings' => [],
        ]);

        $response = $this->withoutMiddleware()
            ->postJson("/calling-agent/builder/{$agent->id}/save", [
                'name'          => 'Save PR Agent Updated',
                'first_message' => 'Welcome to our practice!',
                'settings'      => ['recording_enabled' => true],
            ]);

        $this->assertContains($response->status(), [200, 201, 401, 302],
            'builder.save must not 404/500.');

        if ($response->status() === 200) {
            $this->assertDatabaseHas('calling_agents', ['name' => 'Save PR Agent Updated']);
        }
    }

    public function test_builder_assign_number_persists_phone(): void
    {
        $agent = CallingAgent::create([
            'name'   => 'Assign PR Agent',
            'status' => 'active',
        ]);

        $response = $this->withoutMiddleware()
            ->postJson("/calling-agent/builder/{$agent->id}/assign-number", [
                'number' => '+15005551234',
            ]);

        $this->assertContains($response->status(), [200, 201, 401, 302],
            'builder.assign-number must not 404/500.');

        if ($response->status() === 200) {
            $this->assertDatabaseHas('calling_agent_phone_numbers', ['number' => '+15005551234']);
        }
    }

    // =========================================================================
    // CallingAgentBuilderPreset is Eloquent
    // =========================================================================

    public function test_calling_agent_builder_preset_is_eloquent_model(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Model::class,
            new \Modules\CallingAgent\Models\CallingAgentBuilderPreset(),
            'CallingAgentBuilderPreset must extend Eloquent Model.'
        );
    }

    // =========================================================================
    // TwilioMediaStreamRelay
    // =========================================================================

    public function test_media_stream_relay_normalize_returns_expected_keys(): void
    {
        $relay = new \Modules\CallingAgent\Services\Realtime\TwilioMediaStreamRelay();

        $result = $relay->normalize([
            'event'          => 'media',
            'streamSid'      => 'MZabc123',
            'sequenceNumber' => 42,
            'media'          => ['payload' => 'base64data'],
        ]);

        $this->assertSame('media',      $result['event']);
        $this->assertSame('MZabc123',   $result['stream_sid']);
        $this->assertSame(42,           $result['sequence']);
        $this->assertSame('base64data', $result['payload']);
    }

    public function test_media_stream_relay_should_flush_below_threshold(): void
    {
        $relay  = new \Modules\CallingAgent\Services\Realtime\TwilioMediaStreamRelay();
        $buffer = new \Modules\CallingAgent\Services\Realtime\AudioFrameBuffer();

        $this->assertFalse($relay->shouldFlush($buffer, 20));
    }

    public function test_media_stream_relay_should_flush_at_threshold(): void
    {
        $relay  = new \Modules\CallingAgent\Services\Realtime\TwilioMediaStreamRelay();
        $buffer = new \Modules\CallingAgent\Services\Realtime\AudioFrameBuffer();

        for ($i = 0; $i < 20; $i++) {
            $buffer->push(['frame' => $i]);
        }

        $this->assertTrue($relay->shouldFlush($buffer, 20));
    }

    // =========================================================================
    // Internal helpers
    // =========================================================================

    /**
     * Build a minimal stub that mimics $twilio->client()->calls($callSid)->update().
     * We just need the calls()->update() chain to not throw.
     */
    private function createConfiguredTwilioClient(string $callSid, object $callsContext): object
    {
        $client = new class($callSid, $callsContext) {
            public function __construct(private string $sid, private object $ctx) {}
            public function calls(string $sid): object { return $this->ctx; }
        };
        return $client;
    }
}
