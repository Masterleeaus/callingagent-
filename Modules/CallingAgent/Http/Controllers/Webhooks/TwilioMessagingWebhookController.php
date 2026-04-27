<?php
namespace Modules\CallingAgent\Http\Controllers\Webhooks;
use Illuminate\Http\Request; use Illuminate\Routing\Controller; use Modules\CallingAgent\Models\CallingAgentMessage; use Modules\CallingAgent\Services\TwilioChannelService; use Modules\CallingAgent\AI\Agents\ReceptionistAgent;
class TwilioMessagingWebhookController extends Controller
{ public function __construct(private TwilioChannelService $twilio, private ReceptionistAgent $agent) {}
  public function incoming(Request $r){$body=(string)$r->input('Body','');$from=(string)$r->input('From');$to=(string)$r->input('To');$channel=str_starts_with($from,'whatsapp:')?'whatsapp':'sms';CallingAgentMessage::create(['provider'=>'twilio','channel'=>$channel,'message_sid'=>$r->input('MessageSid') ?: $r->input('SmsSid'),'from'=>$from,'to'=>$to,'body'=>$body,'direction'=>'inbound','status'=>'received','metadata'=>$r->all()]);$reply=$this->agent->respond($body); if($channel==='whatsapp'){$this->twilio->sendWhatsapp($from,$reply,$to);}else{$this->twilio->sendSms($from,$reply,$to);} return response('',204);} }
