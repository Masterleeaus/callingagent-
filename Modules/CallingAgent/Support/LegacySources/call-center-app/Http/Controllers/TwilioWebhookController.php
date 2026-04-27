<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhoneNumber;
use App\Models\ActiveCall;
use App\Events\CallStatusChanged;
use Twilio\TwiML\VoiceResponse;

class TwilioWebhookController extends Controller
{
    public function voice(Request $request)
    {
        $to = $request->input('To');
        $from = $request->input('From');
        $callSid = $request->input('CallSid');

        $phoneNumber = PhoneNumber::where('number', $to)->first();
        
        if (!$phoneNumber) {
            return response('Number not recognized', 404);
        }

        // Track active call
        ActiveCall::updateOrCreate(
            ['sid' => $callSid],
            [
                'tenant_id' => $phoneNumber->tenant_id,
                'started_at' => now(),
            ]
        );

        // Notify frontend via Reverb
        broadcast(new CallStatusChanged($phoneNumber->tenant_id, [
            'sid' => $callSid,
            'from' => $from,
            'status' => 'ringing',
        ]))->toOthers();

        $response = new VoiceResponse();
        
        // Low Latency Optimization: Start Media Stream
        // $response->connect()->stream(['url' => 'wss://' . $request->getHost() . '/voice-stream']);
        
        $response->say('Connecting you to the AI helper.', ['voice' => 'Polly.Amy']);
        $response->dial('+1234567890'); // Placeholder for routing logic

        return response($response)->header('Content-Type', 'text/xml');
    }

    public function statusCallback(Request $request)
    {
        $callSid = $request->input('CallSid');
        $status = $request->input('CallStatus');

        $activeCall = ActiveCall::where('sid', $callSid)->first();

        if ($activeCall) {
            if (in_array($status, ['completed', 'failed', 'busy', 'no-answer'])) {
                // Broadcast completion
                broadcast(new CallStatusChanged($activeCall->tenant_id, [
                    'sid' => $callSid,
                    'status' => 'completed',
                    'duration' => $request->input('CallDuration'),
                ]))->toOthers();

                $activeCall->delete();
            }
        }

        return response('OK');
    }
}
