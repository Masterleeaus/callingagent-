<?php
namespace Modules\CallingAgent\Services;
use Illuminate\Support\Facades\Http;
class ElevenLabsAgentService
{
    public function createOrUpdateAgent(array $agent): array { return ['status'=>'stub','message'=>'Wire to App\Services\Ai\ElevenLabsService when available','agent'=>$agent]; }
    public function textToSpeech(string $text, ?string $voiceId=null): ?string { $key=env('ELEVENLABS_API_KEY'); if(!$key||!$voiceId)return null; return Http::withHeaders(['xi-api-key'=>$key])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}",['text'=>$text,'model_id'=>'eleven_turbo_v2_5'])->body(); }
}
