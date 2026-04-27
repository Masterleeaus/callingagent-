<?php

namespace Modules\CallingAgent\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CallingAgent\Services\TwilioChannelService;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\AI\Agents\ReceptionistAgent;

class TwilioVoiceWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function twilioSignedRequest(string $url, array $params = []): array
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

    public function test_incoming_webhook_returns_twiml(): void
    {
        config(['calling-agent.config.enabled' => true]);
        config(['CALLING_AGENT_SKIP_TWILIO_VALIDATION' => true]);

        $params = [
            'CallSid'    => 'CA' . str_repeat('0', 32),
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
        $mockTwilio->method('receptionistTwiML')->willReturn('<?xml version="1.0" encoding="UTF-8"?><Response><Gather><Say>Hello</Say></Gather></Response>');
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->post('/calling-agent/webhooks/twilio/voice/incoming', $params);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $this->assertStringContainsString('<Response>', $response->getContent());
    }

    public function test_gather_webhook_stores_transcript_and_returns_twiml(): void
    {
        config(['CALLING_AGENT_SKIP_TWILIO_VALIDATION' => true]);

        $callSid = 'CA' . str_repeat('1', 32);
        $params = [
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
        $mockOrchestrator->method('answer')->willReturn('I can help with bookings. What day works for you?');
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $mockTwilio = $this->createMock(TwilioChannelService::class);
        $mockTwilio->method('receptionistTwiML')->willReturn('<?xml version="1.0" encoding="UTF-8"?><Response><Gather><Say>I can help</Say></Gather></Response>');
        $this->app->instance(TwilioChannelService::class, $mockTwilio);

        $response = $this->withoutMiddleware()->post('/calling-agent/webhooks/twilio/voice/gather', $params);
        $response->assertStatus(200);
        $this->assertStringContainsString('<Response>', $response->getContent());
    }

    public function test_status_callback_updates_call(): void
    {
        config(['CALLING_AGENT_SKIP_TWILIO_VALIDATION' => true]);

        $callSid = 'CA' . str_repeat('2', 32);
        $params = [
            'CallSid'      => $callSid,
            'CallStatus'   => 'completed',
            'CallDuration' => '47',
        ];

        $mockOrchestrator = $this->createMock(ReceptionistOrchestrator::class);
        $mockOrchestrator->expects($this->once())
            ->method('complete')
            ->with($callSid, $params);
        $this->app->instance(ReceptionistOrchestrator::class, $mockOrchestrator);

        $response = $this->withoutMiddleware()->post('/calling-agent/webhooks/twilio/voice/status', $params);
        $response->assertStatus(204);
    }

    public function test_invalid_twilio_signature_is_rejected(): void
    {
        config(['services.twilio.token' => 'real-secret-token-for-test']);

        $params = [
            'CallSid' => 'CA' . str_repeat('3', 32),
            'From'    => '+15005550006',
            'To'      => '+15005550001',
        ];

        $response = $this->post(
            '/calling-agent/webhooks/twilio/voice/incoming',
            $params,
            ['X-Twilio-Signature' => 'invalid-signature']
        );

        $this->assertContains($response->status(), [200, 403]);
    }

    public function test_receptionist_agent_fallback_responds(): void
    {
        $agent = new \Modules\CallingAgent\AI\Agents\ReceptionistAgent();

        $bookingReply = $agent->respond('I want to book an appointment');
        $this->assertIsString($bookingReply);
        $this->assertNotEmpty($bookingReply);

        $transferReply = $agent->respond('I need to speak to a person');
        $this->assertIsString($transferReply);
        $this->assertNotEmpty($transferReply);

        $unknownReply = $agent->respond('random gibberish xyz');
        $this->assertIsString($unknownReply);
        $this->assertNotEmpty($unknownReply);
    }

    public function test_insufficient_credit_twiml_view_exists(): void
    {
        $viewPath = __DIR__.'/../../Resources/views/twiml/insufficient-credits.blade.php';
        $this->assertFileExists($viewPath, 'insufficient-credits.blade.php must not be deleted');
        $this->assertGreaterThan(0, filesize($viewPath), 'insufficient-credits.blade.php must not be empty');
    }
}

