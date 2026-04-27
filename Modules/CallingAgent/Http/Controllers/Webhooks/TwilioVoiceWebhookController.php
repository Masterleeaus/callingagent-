<?php
namespace Modules\CallingAgent\Http\Controllers\Webhooks;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CallingAgent\Services\{TwilioChannelService,ReceptionistOrchestrator};
class TwilioVoiceWebhookController extends Controller
{
    public function __construct(private TwilioChannelService $twilio, private ReceptionistOrchestrator $orchestrator) {}
    public function incoming(Request $request)
    {
        $call=$this->orchestrator->startInbound($request->all()); $this->orchestrator->touchActive($call,$request->all());
        $agent=$this->orchestrator->resolveByNumber($request->input('To'));
        $first=$agent?->first_message ?: 'Hello, you have reached the AI front desk. How can I help you today?';
        $xml=$this->twilio->receptionistTwiML($first, url('/calling-agent/webhooks/twilio/voice/gather'), ['record_url'=>url('/calling-agent/webhooks/twilio/voice/status')]);
        return response($xml,200)->header('Content-Type','text/xml');
    }
    public function gather(Request $request)
    {
        $call=$this->orchestrator->startInbound($request->all());
        $speech=$request->input('SpeechResult') ?: $request->input('Digits') ?: '';
        $reply=$speech ? $this->orchestrator->answer($call,$speech) : 'I did not catch that. Could you please repeat?';
        $xml=$this->twilio->receptionistTwiML($reply.' You can ask another question, say transfer, or leave a message.', url('/calling-agent/webhooks/twilio/voice/gather'), ['record_url'=>url('/calling-agent/webhooks/twilio/voice/status')]);
        return response($xml,200)->header('Content-Type','text/xml');
    }
    public function status(Request $request) { if($request->input('CallSid')) $this->orchestrator->complete($request->input('CallSid'),$request->all()); return response('',204); }
}
