<?php

namespace Modules\CallingAgent\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CallingAgent\Billing\Usage\CallUsageRecorder;
use Modules\CallingAgent\Services\TwilioChannelService;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\Models\CallingAgentCall;
use Modules\CallingAgent\Models\CallingAgentActiveCall;
use Twilio\TwiML\VoiceResponse;

class TwilioVoiceWebhookController extends Controller
{
    public function __construct(
        private TwilioChannelService $twilio,
        private ReceptionistOrchestrator $orchestrator,
        private CallUsageRecorder $usageRecorder,
    ) {}

    public function incoming(Request $request)
    {
        $call = $this->orchestrator->startInbound($request->all());
        $this->orchestrator->touchActive($call, $request->all());
        $agent = $this->orchestrator->resolveByNumber($request->input('To'));
        $first = $agent?->first_message ?: 'Hello, you have reached our AI receptionist. How can I help you today?';

        $xml = $this->twilio->receptionistTwiML(
            $first,
            route('calling-agent.webhooks.voice.gather'),
            ['record_url' => route('calling-agent.webhooks.voice.status')]
        );

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function gather(Request $request)
    {
        $call   = $this->orchestrator->startInbound($request->all());
        $speech = $request->input('SpeechResult') ?: $request->input('Digits') ?: '';
        $reply  = $speech
            ? $this->orchestrator->answer($call, $speech)
            : 'I did not catch that. Could you please repeat?';

        $xml = $this->twilio->receptionistTwiML(
            $reply . ' You can ask another question, say transfer, or leave a message.',
            route('calling-agent.webhooks.voice.gather'),
            ['record_url' => route('calling-agent.webhooks.voice.status')]
        );

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function status(Request $request)
    {
        if ($callSid = $request->input('CallSid')) {
            $this->orchestrator->complete($callSid, $request->all());

            // Billing: record voice seconds once, idempotently
            $call = CallingAgentCall::where('call_sid', $callSid)->first();
            if ($call && in_array($request->input('CallStatus'), ['completed', 'failed', 'busy', 'no-answer'], true)) {
                try {
                    $this->usageRecorder->record($call);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
        return response('', 204);
    }

    public function recording(Request $request)
    {
        $callSid      = $request->input('CallSid');
        $recordingSid = $request->input('RecordingSid');
        $recordingUrl = $request->input('RecordingUrl');
        $duration     = (int) $request->input('RecordingDuration', 0);

        if (!$callSid || !$recordingSid) {
            return response('', 204);
        }

        // Idempotency: skip if already processed
        if ($this->orchestrator->isDuplicate('recording:' . $recordingSid, 'twilio')) {
            return response('', 204);
        }

        CallingAgentCall::where('call_sid', $callSid)
            ->update(['recording_url' => $recordingUrl]);

        try {
            \DB::table('calling_agent_recordings')->insert([
                'call_sid'      => $callSid,
                'recording_sid' => $recordingSid,
                'recording_url' => $recordingUrl,
                'duration'      => $duration,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // Skip if table not yet migrated; report other errors.
            $isMissing = ($e instanceof \Illuminate\Database\QueryException
                && in_array((string) $e->getCode(), ['1146', '42P01', 'HY000'], true))
                || str_contains(strtolower($e->getMessage()), "doesn't exist")
                || str_contains(strtolower($e->getMessage()), 'no such table');
            if (!$isMissing) {
                report($e);
            }
        }

        return response('', 204);
    }

    public function voicemail(Request $request)
    {
        $callSid      = $request->input('CallSid');
        $recordingUrl = $request->input('RecordingUrl');
        $recordingSid = $request->input('RecordingSid');

        $r = new VoiceResponse();

        if ($request->input('RecordingStatus') === 'completed' && $callSid) {
            // Idempotency: skip if already processed
            if ($recordingSid && $this->orchestrator->isDuplicate('voicemail:' . $recordingSid, 'twilio')) {
                return response($r->asXML(), 200)->header('Content-Type', 'text/xml');
            }

            CallingAgentCall::where('call_sid', $callSid)
                ->update([
                    'status'        => 'voicemail',
                    'recording_url' => $recordingUrl,
                    'ended_at'      => now(),
                ]);
            CallingAgentActiveCall::where('call_sid', $callSid)->delete();
        } else {
            // Prompt to leave voicemail
            $r->say('Please leave your message after the tone. Press any key when finished.');
            $r->record([
                'action'      => route('calling-agent.webhooks.voice.voicemail'),
                'method'      => 'POST',
                'maxLength'   => 120,
                'finishOnKey' => '#',
                'transcribe'  => false,
            ]);
            $r->say('We did not receive your message. Goodbye.');
            $r->hangup();
        }

        return response($r->asXML(), 200)->header('Content-Type', 'text/xml');
    }
}
