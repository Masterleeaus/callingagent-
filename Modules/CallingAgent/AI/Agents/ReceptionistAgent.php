<?php
namespace Modules\CallingAgent\AI\Agents;
use Throwable;
class ReceptionistAgent
{
    public function respond(string $utterance, array $context=[]): string
    {
        $prompt = trim(($context['system'] ?? config('calling-agent.ai.receptionist_prompt', 'You are a helpful receptionist.'))."
Caller: ".$utterance);
        try {
            if (class_exists('App\Extensions\Chatbot\System\Services\GeneratorService') && !empty($context['chatbot'])) {
                return app('App\Extensions\Chatbot\System\Services\GeneratorService')->setChatbot($context['chatbot'])->setPrompt($utterance)->generate();
            }
        } catch (Throwable $e) { report($e); }
        return $this->ruleBasedFallback($utterance);
    }
    private function ruleBasedFallback(string $utterance): string
    {
        $u=strtolower($utterance);
        if(str_contains($u,'book')||str_contains($u,'appointment')) return 'I can help with bookings. Please tell me your name, preferred day, and best phone number.';
        if(str_contains($u,'human')||str_contains($u,'person')||str_contains($u,'reception')) return 'I can connect you with the front desk. Please hold while I transfer you.';
        if(str_contains($u,'hours')) return 'Our team can confirm current opening hours. Would you like me to take a message or transfer you?';
        return config('calling-agent.ai.fallback_message', 'I can take a message or connect you with the team.');
    }
}
