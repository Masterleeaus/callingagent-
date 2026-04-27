<?php
namespace Modules\CallingAgent\Services;
use Modules\CallingAgent\AI\Agents\ReceptionistAgent;
use Modules\CallingAgent\Models\{CallingAgent,CallingAgentCall,CallingAgentActiveCall,CallingAgentTranscript,CallingAgentPhoneNumber};
class ReceptionistOrchestrator
{
    public function __construct(public ReceptionistAgent $agent) {}
    public function resolveByNumber(?string $to): ?CallingAgent { if(!$to)return null; $pn=CallingAgentPhoneNumber::where('number',$to)->first(); return $pn?->calling_agent_id ? CallingAgent::find($pn->calling_agent_id) : CallingAgent::where('phone_number',$to)->first(); }
    public function startInbound(array $payload): CallingAgentCall { return CallingAgentCall::updateOrCreate(['call_sid'=>$payload['CallSid']??null],['provider'=>'twilio','direction'=>'inbound','from'=>$payload['From']??null,'to'=>$payload['To']??null,'status'=>'ringing','started_at'=>now(),'metadata'=>$payload]); }
    public function touchActive(CallingAgentCall $call, array $payload): void { CallingAgentActiveCall::updateOrCreate(['call_sid'=>$call->call_sid],['calling_agent_call_id'=>$call->id,'from'=>$call->from,'to'=>$call->to,'state'=>$payload['CallStatus']??'ringing','last_seen_at'=>now(),'context'=>$payload]); }
    public function answer(CallingAgentCall $call, string $speech, array $context=[]): string { CallingAgentTranscript::create(['calling_agent_call_id'=>$call->id,'role'=>'user','text'=>$speech,'source'=>'twilio-speech']); $reply=$this->agent->respond($speech,$context); CallingAgentTranscript::create(['calling_agent_call_id'=>$call->id,'role'=>'assistant','text'=>$reply,'source'=>'ai']); return $reply; }
    public function complete(string $callSid, array $payload): void { $call=CallingAgentCall::where('call_sid',$callSid)->first(); if($call){$call->update(['status'=>$payload['CallStatus']??'completed','duration'=>(int)($payload['CallDuration']??$call->duration),'ended_at'=>now(),'metadata'=>array_merge($call->metadata??[],$payload)]);} CallingAgentActiveCall::where('call_sid',$callSid)->delete(); }
}
