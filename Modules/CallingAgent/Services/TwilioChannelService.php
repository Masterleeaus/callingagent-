<?php
namespace Modules\CallingAgent\Services;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;
class TwilioChannelService
{
    public function client(): Client { return new Client(config('services.twilio.sid', env('TWILIO_ACCOUNT_SID')), config('services.twilio.token', env('TWILIO_AUTH_TOKEN'))); }
    public function sendSms(string $to, string $body, ?string $from=null): array { $m=$this->client()->messages->create($to,['from'=>$from ?: env('TWILIO_FROM_NUMBER'),'body'=>$body]); return $this->messageProperties($m); }
    public function sendWhatsapp(string $to, string $body, ?string $from=null): array { $to=str_starts_with($to,'whatsapp:')?$to:'whatsapp:'.$to; $from=$from ?: env('TWILIO_WHATSAPP_FROM', env('TWILIO_FROM_NUMBER')); $from=str_starts_with($from,'whatsapp:')?$from:'whatsapp:'.$from; $m=$this->client()->messages->create($to,['from'=>$from,'body'=>$body]); return $this->messageProperties($m); }
    public function placeCall(string $to, string $url, ?string $from=null, ?string $statusCallback=null): array { $payload=['from'=>$from ?: env('TWILIO_FROM_NUMBER'),'url'=>$url]; if($statusCallback){$payload['statusCallback']=$statusCallback;$payload['statusCallbackEvent']=['initiated','ringing','answered','completed'];} $c=$this->client()->calls->create($to,$payload); return ['sid'=>$c->sid,'status'=>$c->status,'to'=>$c->to,'from'=>$c->from]; }
    public function receptionistTwiML(string $say, string $gatherUrl, array $opts=[]): string { $r=new VoiceResponse(); $g=$r->gather(['input'=>'speech dtmf','action'=>$gatherUrl,'method'=>'POST','speechTimeout'=>'auto','timeout'=>$opts['timeout']??5,'numDigits'=>$opts['numDigits']??1]); $g->say($say, ['voice'=>$opts['voice']??'Polly.Amy','language'=>$opts['language']??'en-US']); $r->say($opts['fallback'] ?? 'I did not hear anything. Please call again or leave a message after the tone.'); if(!empty($opts['record_url'])){$r->record(['action'=>$opts['record_url'],'maxLength'=>120]);} return $r->asXML(); }
    public function sayAndHangup(string $message): string { $r=new VoiceResponse(); $r->say($message); $r->hangup(); return $r->asXML(); }
    private function messageProperties($m): array { return ['sid'=>$m->sid,'status'=>$m->status,'to'=>$m->to,'from'=>$m->from,'body'=>$m->body,'direction'=>$m->direction,'price'=>$m->price ?? null]; }
}
