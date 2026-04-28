<?php

namespace Modules\TitanChatbot\AI\Core;

use Modules\TitanChatbot\AI\Memory\ConversationMemoryStore;
use Modules\TitanChatbot\AI\Tools\ToolRegistry;
use Modules\TitanChatbot\Events\AI\BeforeSend;
use Modules\TitanChatbot\Events\AI\AfterSend;
use Modules\TitanChatbot\Events\AI\EngineError;
use Illuminate\Support\Facades\Event;
use Throwable;

abstract class TitanAgent
{
    /** System instructions for this agent */
    protected string $instructions = 'You are a helpful assistant.';

    /** LLM model to use (can be overridden per-agent) */
    protected string $model = '';

    /** Provider: 'openai', 'legacy', or 'fake' */
    protected string $provider = '';

    /** History strategy: 'cache', 'database', 'in_memory', or 'file' */
    protected string $historyDriver = 'cache';

    /** Max messages to keep in history before truncation */
    protected int $maxHistoryMessages = 20;

    /** Whether this agent supports tool calling */
    protected bool $toolsEnabled = false;

    // State
    private string $resolvedSessionId = 'default';

    // ── Public fluent API ────────────────────────────────────────────────────

    public function forSession(string $sessionId): static
    {
        $this->resolvedSessionId = $sessionId;
        return $this;
    }

    public function instructions(): string
    {
        return $this->instructions;
    }

    public function withInstructions(string $instructions): static
    {
        $this->instructions = $instructions;
        return $this;
    }

    // ── Main respond method ──────────────────────────────────────────────────

    public function respond(string $message, array $context = []): string
    {
        $sessionId = $context['session_id'] ?? $this->resolvedSessionId;

        if (class_exists(BeforeSend::class)) {
            try { Event::dispatch(new BeforeSend($message, $sessionId, static::class)); } catch (Throwable) {}
        }

        $history = $this->getMemory()->recall($sessionId, $this->maxHistoryMessages);

        $builtContext = array_merge(
            [['role' => 'system', 'content' => $context['system'] ?? $this->instructions()]],
            $history,
        );

        try {
            $reply = $this->generate($message, $builtContext, $context);
        } catch (Throwable $e) {
            if (class_exists(EngineError::class)) {
                try { Event::dispatch(new EngineError($e, static::class, $sessionId)); } catch (Throwable) {}
            }
            report($e);
            $reply = $this->fallback($message);
        }

        $this->getMemory()->remember($sessionId, 'user', $message);
        $this->getMemory()->remember($sessionId, 'assistant', $reply);

        if (class_exists(AfterSend::class)) {
            try { Event::dispatch(new AfterSend($message, $reply, $sessionId, static::class)); } catch (Throwable) {}
        }

        return $reply;
    }

    // ── Override hooks ───────────────────────────────────────────────────────

    /**
     * Call the generator. Override for custom providers.
     */
    protected function generate(string $message, array $builtContext, array $rawContext = []): string
    {
        return app(\Modules\TitanChatbot\Services\GeneratorBridge::class)
            ->generate($message, $builtContext);
    }

    /**
     * Rule-based fallback when generation fails.
     */
    protected function fallback(string $message): string
    {
        $m = strtolower($message);
        if (str_contains($m, 'hello') || str_contains($m, 'hi')) {
            return 'Hello! How can I help you?';
        }
        if (str_contains($m, 'bye')) {
            return 'Goodbye! Have a great day.';
        }
        return "I'm here to help. Could you tell me more?";
    }

    // ── Memory accessor ───────────────────────────────────────────────────────

    protected function getMemory(): ConversationMemoryStore
    {
        return app(ConversationMemoryStore::class);
    }

    // ── Tool registry accessor ────────────────────────────────────────────────

    protected function getToolRegistry(): ToolRegistry
    {
        return app(ToolRegistry::class);
    }

    // ── Tool discovery (methods annotated with #[Tool] in subclasses) ─────────

    /**
     * Discover tool methods on this agent via reflection.
     * Returns array of OpenAI-compatible tool schemas.
     */
    public function discoverTools(): array
    {
        if (!$this->toolsEnabled) {
            return [];
        }
        return $this->getToolRegistry()->discoverFromAgent($this);
    }
}
