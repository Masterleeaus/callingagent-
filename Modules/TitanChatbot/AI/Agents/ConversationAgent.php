<?php

namespace Modules\TitanChatbot\AI\Agents;

use Modules\TitanChatbot\AI\Memory\ConversationMemoryStore;
use Modules\TitanChatbot\Services\GeneratorBridge;
use Throwable;

class ConversationAgent
{
    public string $system_prompt = 'You are a helpful conversational assistant.';

    public array $toolset = [];

    public string $memory_policy = 'session';

    public string $escalation_strategy = 'none';

    public function respond(string $message, array $context = []): string
    {
        $sessionId = $context['session_id'] ?? 'default';
        $memory    = app(ConversationMemoryStore::class);

        $history = $memory->recall($sessionId);

        $builtContext = array_merge(
            [['role' => 'system', 'content' => $context['system'] ?? $this->system_prompt]],
            $history,
        );

        try {
            $reply = app(GeneratorBridge::class)->generate($message, $builtContext);
        } catch (Throwable $e) {
            report($e);
            $reply = $this->ruleBasedFallback($message);
        }

        $memory->remember($sessionId, 'user', $message);
        $memory->remember($sessionId, 'assistant', $reply);

        return $reply;
    }

    protected function ruleBasedFallback(string $message): string
    {
        $m = strtolower($message);

        if (str_contains($m, 'hello') || str_contains($m, 'hi')) {
            return 'Hello! How can I help you today?';
        }

        if (str_contains($m, 'bye') || str_contains($m, 'goodbye')) {
            return 'Goodbye! Have a great day.';
        }

        return 'I\'m here to help. Could you tell me more about what you need?';
    }
}
