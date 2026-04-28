<?php

namespace Modules\TitanChatbot\AI\Agents;

use Modules\TitanChatbot\AI\Core\TitanAgent;

class ConversationAgent extends TitanAgent
{
    protected string $instructions = 'You are a helpful conversational assistant.';

    // Legacy public properties kept for backward compatibility
    public string $system_prompt = 'You are a helpful conversational assistant.';
    public array $toolset = [];
    public string $memory_policy = 'session';
    public string $escalation_strategy = 'none';

    /**
     * Use legacy $system_prompt when set, otherwise fall back to $instructions.
     */
    public function instructions(): string
    {
        return $this->system_prompt ?: $this->instructions;
    }

    protected function fallback(string $message): string
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

    /**
     * Backward-compatible alias for fallback().
     */
    protected function ruleBasedFallback(string $message): string
    {
        return $this->fallback($message);
    }
}
