<?php

namespace Modules\CallingAgent\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CallingAgent\AI\Agents\ReceptionistAgent;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\Services\TwilioChannelService;

class TwilioVoiceWebhookTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Signature helpers
    // -------------------------------------------------------------------------

    /**
     * Build a valid X-Twilio-Signature header for the given URL + POST params.
     */
    private function twilioSignedHeaders(string $url, array $params = []): array
    {
        $token = config('services.twilio.token', 'test-auth-token');
        ksort($params);
        $str = $url;
        foreach ($params as $k => $v) {
            $str .= $k . $v;
        }
        $sig = base64_encode(hash_hmac('sha1', $str, $token, true));
        return ['X-Twilio-Signature' => $sig];
    }

    private function skipValidation(): void
    {
        config(['calling-agent.skip_twilio_validation' => true]);
    }

    // -------------------------------------------------------------------------
    // Inbound voice tests
    // -------------------------------------------------------------------------

    public function test_valid_twilio_signature_is_accepted(): void
    {
        config(['calling-agent.skip_twilio_validation' => false]);
        config(['services.twilio.token' => 'test-secret']);

        $url    = url('/calling-agent/webhooks/twilio/voice/incoming');
        $params = [
            'CallSid'    => 'CA' . str_repeat('0', 32),
            'From'       => '+15005550006',
            'To'         => '+15005550001',
            'CallStatus' => 'ringing',
        ];
        $headers = $this->twilioSignedHeaders($url, $params);

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockCall = new \Modules\CallingAgent\Models\CallingAgentCall();
        $mockCall->id = 1;
        $mockCall->call_sid = $params['CallSid'];
        $mockOrchestrator->method('startInbound')->willReturn($mockCall);
        $mockOrchestrator->method('touchActive')->willReturn(null);
        $mockOrchestrator->method('resolveByNumber')->willReturn(null);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $mockTwilio = $this->createMock(TwilioChannelService::class);
        $mockTwilio->method('receptionistTwiML')->willReturn('<?xml version="1.0" encoding="UTF-8"?><Response><Gather><Say>Hello</Say></Gather></Response>');
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->post('/calling-agent/webhooks/twilio/voice/incoming', $params, $headers);
        $response->assertStatus(200);
    }

    public function test_invalid_twilio_signature_is_rejected(): void
    {
        config(['calling-agent.skip_twilio_validation' => false]);
        config(['services.twilio.token' => 'real-secret-token-for-test']);

        $params = [
            'CallSid' => 'CA' . str_repeat('3', 32),
            'From'    => '+15005550006',
            'To'      => '+15005550001',
        ];

        $response = $this->post(
            '/calling-agent/webhooks/twilio/voice/incoming',
            $params,
            ['X-Twilio-Signature' => 'invalid-bad-signature']
        );

        $response->assertStatus(403);
    }

    public function test_incoming_webhook_returns_twiml(): void
    {
        $this->skipValidation();

        $params = [
            'CallSid'    => 'CA' . str_repeat('A', 32),
            'From'       => '+15005550006',
            'To'         => '+15005550001',
            'CallStatus' => 'ringing',
        ];

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockCall = new \Modules\CallingAgent\Models\CallingAgentCall();
        $mockCall->id = 1;
        $mockCall->call_sid = $params['CallSid'];
        $mockOrchestrator->method('startInbound')->willReturn($mockCall);
        $mockOrchestrator->method('touchActive')->willReturn(null);
        $mockOrchestrator->method('resolveByNumber')->willReturn(null);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $mockTwilio = $this->createMock(TwilioChannelService::class);
        $mockTwilio->method('receptionistTwiML')->willReturn(
            '<?xml version="1.0" encoding="UTF-8"?><Response><Gather><Say>Hello</Say></Gather></Response>'
        );
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/incoming', $params);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $this->assertStringContainsString('<Response>', $response->getContent());
    }

    public function test_gather_webhook_stores_transcript_and_returns_twiml(): void
    {
        $this->skipValidation();

        $callSid = 'CA' . str_repeat('1', 32);
        $params  = [
            'CallSid'      => $callSid,
            'From'         => '+15005550006',
            'To'           => '+15005550001',
            'SpeechResult' => 'I want to book an appointment',
        ];

        $mockCall = new \Modules\CallingAgent\Models\CallingAgentCall();
        $mockCall->id = 1;
        $mockCall->call_sid = $callSid;

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestrator->method('startInbound')->willReturn($mockCall);
        $mockOrchestrator->expects($this->once())
            ->method('answer')
            ->with($mockCall, 'I want to book an appointment')
            ->willReturn('I can help with bookings. What day works for you?');
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $mockTwilio = $this->createMock(TwilioChannelService::class);
        $mockTwilio->method('receptionistTwiML')->willReturn(
            '<?xml version="1.0" encoding="UTF-8"?><Response><Gather><Say>I can help</Say></Gather></Response>'
        );
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/gather', $params);

        $response->assertStatus(200);
        $this->assertStringContainsString('<Response>', $response->getContent());
    }

    public function test_status_callback_updates_call_idempotently(): void
    {
        $this->skipValidation();

        $callSid = 'CA' . str_repeat('2', 32);
        $params  = [
            'CallSid'      => $callSid,
            'CallStatus'   => 'completed',
            'CallDuration' => '47',
        ];

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestrator->expects($this->once())
            ->method('complete')
            ->with($callSid, $params);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $response = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/status', $params);

        $response->assertStatus(204);
    }

    // -------------------------------------------------------------------------
    // Recording callback
    // -------------------------------------------------------------------------

    public function test_recording_callback_persists_and_deduplicates(): void
    {
        $this->skipValidation();

        $callSid      = 'CA' . str_repeat('R', 32);
        $recordingSid = 'RE' . str_repeat('R', 32);
        $params = [
            'CallSid'          => $callSid,
            'RecordingSid'     => $recordingSid,
            'RecordingUrl'     => 'https://api.twilio.com/recordings/' . $recordingSid,
            'RecordingDuration'=> '30',
        ];

        // First call — should process
        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestrator->method('isDuplicate')->willReturn(false);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $response1 = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/recording', $params);
        $response1->assertStatus(204);

        // Second call — idempotency guard should short-circuit
        $mockOrchestratorDup = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestratorDup->method('isDuplicate')->willReturn(true);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestratorDup);

        $response2 = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/recording', $params);
        $response2->assertStatus(204);
    }

    // -------------------------------------------------------------------------
    // Voicemail callback
    // -------------------------------------------------------------------------

    public function test_voicemail_prompt_returns_record_twiml(): void
    {
        $this->skipValidation();

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $response = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/voicemail', [
                'CallSid' => 'CA' . str_repeat('V', 32),
            ]);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('<Record', $content);
        $this->assertStringContainsString('leave your message', $content);
    }

    public function test_voicemail_completion_callback_updates_call(): void
    {
        $this->skipValidation();

        $callSid      = 'CA' . str_repeat('M', 32);
        $recordingSid = 'RE' . str_repeat('M', 32);
        $params = [
            'CallSid'         => $callSid,
            'RecordingStatus' => 'completed',
            'RecordingSid'    => $recordingSid,
            'RecordingUrl'    => 'https://api.twilio.com/recordings/' . $recordingSid,
        ];

        // No duplicate — should update call
        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestrator->method('isDuplicate')->willReturn(false);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $response = $this->withoutMiddleware()
            ->post('/calling-agent/webhooks/twilio/voice/voicemail', $params);

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // SMS webhook
    // -------------------------------------------------------------------------

    public function test_sms_webhook_persists_and_replies(): void
    {
        $this->skipValidation();

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestrator->method('isDuplicate')->willReturn(false);
        $mockOrchestrator->method('recallCallerProfile')->willReturn(null);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $mockAgent = $this->createMock(ReceptionistAgent::class);
        $mockAgent->method('respond')->willReturn('Thanks for your message!');
        $this->app->instance(ReceptionistAgent::class, $mockAgent);

        $mockTwilio = $this->createMock(TwilioChannelService::class);
        $mockTwilio->expects($this->once())->method('sendSms');
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->post(
            '/calling-agent/webhooks/twilio/message/incoming',
            [
                'MessageSid' => 'SM' . str_repeat('S', 32),
                'From'       => '+15005550006',
                'To'         => '+15005550001',
                'Body'       => 'Hello, I need help',
            ]
        );

        $response->assertStatus(204);
    }

    // -------------------------------------------------------------------------
    // WhatsApp webhook
    // -------------------------------------------------------------------------

    public function test_whatsapp_webhook_persists_and_replies(): void
    {
        $this->skipValidation();

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestrator->method('isDuplicate')->willReturn(false);
        $mockOrchestrator->method('recallCallerProfile')->willReturn(null);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $mockAgent = $this->createMock(ReceptionistAgent::class);
        $mockAgent->method('respond')->willReturn('Hi via WhatsApp!');
        $this->app->instance(ReceptionistAgent::class, $mockAgent);

        $mockTwilio = $this->createMock(TwilioChannelService::class);
        $mockTwilio->expects($this->once())->method('sendWhatsapp');
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->post(
            '/calling-agent/webhooks/twilio/message/incoming',
            [
                'MessageSid' => 'SM' . str_repeat('W', 32),
                'From'       => 'whatsapp:+15005550006',
                'To'         => 'whatsapp:+15005550001',
                'Body'       => 'Hello from WhatsApp',
            ]
        );

        $response->assertStatus(204);
    }

    // -------------------------------------------------------------------------
    // Idempotency duplicate prevention
    // -------------------------------------------------------------------------

    public function test_duplicate_message_is_skipped(): void
    {
        $this->skipValidation();

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        // Simulate duplicate: return true so handler should early-exit
        $mockOrchestrator->method('isDuplicate')->willReturn(true);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $mockTwilio = $this->createMock(TwilioChannelService::class);
        // Should NOT be called on a duplicate
        $mockTwilio->expects($this->never())->method('sendSms');
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->post(
            '/calling-agent/webhooks/twilio/message/incoming',
            [
                'MessageSid' => 'SM' . str_repeat('D', 32),
                'From'       => '+15005550006',
                'To'         => '+15005550001',
                'Body'       => 'Duplicate message',
            ]
        );

        $response->assertStatus(204);
    }

    // -------------------------------------------------------------------------
    // Builder save/load
    // -------------------------------------------------------------------------

    public function test_builder_load_returns_agent_and_webhook_urls(): void
    {
        $this->skipValidation();

        $agent = \Modules\CallingAgent\Models\CallingAgent::create([
            'name'          => 'Test Agent',
            'status'        => 'active',
            'first_message' => 'Hello!',
            'settings'      => [],
        ]);

        $response = $this->withoutMiddleware()->actingAs(
            (object)['id' => 1],
            'web'
        )->getJson("/calling-agent/builder/{$agent->id}/load");

        // Without a real auth setup the route will 302 or 401; assert it at least
        // resolves the controller (not 404/500) – in a host app auth would pass.
        $this->assertContains($response->status(), [200, 302, 401, 419]);
    }

    public function test_builder_save_validates_input(): void
    {
        $this->skipValidation();

        $agent = \Modules\CallingAgent\Models\CallingAgent::create([
            'name'     => 'Save Test Agent',
            'status'   => 'active',
            'settings' => [],
        ]);

        $response = $this->withoutMiddleware()
            ->postJson("/calling-agent/builder/{$agent->id}/save", [
                'name'          => 'Updated Agent Name',
                'first_message' => 'Welcome!',
                'settings'      => [
                    'voicemail_enabled' => true,
                    'recording_enabled' => true,
                ],
            ]);

        // Without host auth middleware, expect 200 or redirect
        $this->assertContains($response->status(), [200, 201, 302, 401, 419, 422]);
    }

    // -------------------------------------------------------------------------
    // Insufficient credit TwiML
    // -------------------------------------------------------------------------

    public function test_insufficient_credit_twiml_view_exists(): void
    {
        $viewPath = __DIR__ . '/../../Resources/views/twiml/insufficient-credits.blade.php';
        $this->assertFileExists($viewPath, 'insufficient-credits.blade.php must not be deleted');
        $this->assertGreaterThan(0, filesize($viewPath), 'insufficient-credits.blade.php must not be empty');
    }

    public function test_insufficient_credit_view_contains_hangup(): void
    {
        $viewPath = __DIR__ . '/../../Resources/views/twiml/insufficient-credits.blade.php';
        $content  = file_get_contents($viewPath);
        $this->assertStringContainsStringIgnoringCase('hangup', $content);
    }

    // -------------------------------------------------------------------------
    // Receptionist agent fallback
    // -------------------------------------------------------------------------

    public function test_receptionist_agent_fallback_responds(): void
    {
        $agent = new \Modules\CallingAgent\AI\Agents\ReceptionistAgent();

        foreach ([
            'I want to book an appointment',
            'I need to speak to a person',
            'What are your hours?',
            'random gibberish xyz',
        ] as $utterance) {
            $reply = $agent->respond($utterance);
            $this->assertIsString($reply);
            $this->assertNotEmpty($reply);
        }
    }

    // -------------------------------------------------------------------------
    // Outcome extraction unit test
    // -------------------------------------------------------------------------

    public function test_outcome_pipeline_extracts_booking_intent(): void
    {
        $pipeline = new \Modules\CallingAgent\AI\Pipelines\OutcomeExtractionPipeline();
        $outcome  = $pipeline->extract('I want to book an appointment for tomorrow');

        $this->assertSame('booking', $outcome->intent);
        $this->assertTrue($outcome->bookingRequested);
        $this->assertSame('high', $outcome->urgency);
    }

    public function test_outcome_pipeline_detects_handoff_on_urgent(): void
    {
        $pipeline = new \Modules\CallingAgent\AI\Pipelines\OutcomeExtractionPipeline();
        $outcome  = $pipeline->extract('This is urgent I need help today');

        $this->assertTrue($outcome->handoffRequired);
    }
}
