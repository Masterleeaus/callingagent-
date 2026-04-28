<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Billing\Usage\UsageRecord;
use Modules\TitanChatbot\Billing\Usage\UsageTracker;
use Modules\TitanChatbot\Events\AI\EngineError;

class GeneratorBridge
{
    private mixed  $chatbot           = null;
    private mixed  $conversation      = null;
    private string $fakeResponse      = '';
    private array  $fallbackProviders = [];
    private string $fallbackMessage   = "Sorry, I can't answer right now.";

    public function fake(string $response = ''): self
    {
        $this->fakeResponse = $response !== '' ? $response : 'This is a fake AI response for testing.';
        return $this;
    }

    public function withFallbackProviders(array $providers): self
    {
        $this->fallbackProviders = $providers;
        return $this;
    }

    public function generate(string $prompt, array $context = []): string
    {
        if (!empty($this->fakeResponse)) {
            return $this->fakeResponse;
        }

        $provider = config('titan-chatbot.ai.provider', 'openai');

        // Try primary provider
        try {
            if ($provider !== 'openai' && $this->hasLegacyGeneratorService()) {
                return $this->generateViaLegacyService($prompt);
            }
            return $this->generateViaOpenAi($prompt, $context);
        } catch (\Throwable $e) {
            Log::warning('GeneratorBridge: primary provider failed.', ['error' => $e->getMessage()]);
            Event::dispatch(new EngineError($e, static::class, ''));
        }

        // Try each fallback provider in sequence
        foreach ($this->fallbackProviders as $fallback) {
            try {
                if ($fallback === 'openai') {
                    return $this->generateViaOpenAi($prompt, $context);
                }
                if ($fallback === 'legacy' && $this->hasLegacyGeneratorService()) {
                    return $this->generateViaLegacyService($prompt);
                }
            } catch (\Throwable $e) {
                Log::warning("GeneratorBridge: fallback provider '{$fallback}' failed.", ['error' => $e->getMessage()]);
                Event::dispatch(new EngineError($e, static::class, ''));
            }
        }

        return $this->fallbackMessage;
    }

    public function generateWithUsageTracking(
        string $prompt,
        array  $context    = [],
        string $sessionId  = '',
        int    $chatbotId  = 0,
        int    $tenantId   = 0
    ): array {
        if (!empty($this->fakeResponse)) {
            $reply  = $this->fakeResponse;
            $usage  = new UsageRecord(
                provider:  'fake',
                sessionId: $sessionId,
                chatbotId: $chatbotId,
                tenantId:  $tenantId,
            );
            return ['reply' => $reply, 'usage' => $usage->normalize()];
        }

        $provider = config('titan-chatbot.ai.provider', 'openai');
        $model    = config('titan-chatbot.ai.model', 'gpt-4o-mini');

        $reply          = $this->fallbackMessage;
        $promptTokens   = 0;
        $completionTokens = 0;
        $totalTokens    = 0;
        $usedProvider   = $provider;

        try {
            if ($provider !== 'openai' && $this->hasLegacyGeneratorService()) {
                $reply = $this->generateViaLegacyService($prompt);
                $usedProvider = 'legacy';
            } else {
                [$reply, $promptTokens, $completionTokens, $totalTokens] = $this->generateViaOpenAiWithUsage($prompt, $context);
                $usedProvider = 'openai';
            }
        } catch (\Throwable $e) {
            Log::warning('GeneratorBridge: primary provider failed in usage tracking.', ['error' => $e->getMessage()]);
            Event::dispatch(new EngineError($e, static::class, $sessionId));
        }

        $usage = new UsageRecord(
            provider:         $usedProvider,
            model:            $model,
            promptTokens:     $promptTokens,
            completionTokens: $completionTokens,
            totalTokens:      $totalTokens,
            tenantId:         $tenantId,
            chatbotId:        $chatbotId,
            sessionId:        $sessionId,
        );

        $normalized = $usage->normalize();

        app(UsageTracker::class)->record($normalized);

        return ['reply' => $reply, 'usage' => $normalized];
    }

    public function setChatbot(mixed $chatbot): self
    {
        $this->chatbot = $chatbot;

        return $this;
    }

    public function setConversation(mixed $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    private function hasLegacyGeneratorService(): bool
    {
        return class_exists('App\Extensions\Chatbot\System\Services\GeneratorService');
    }

    private function generateViaLegacyService(string $prompt): string
    {
        try {
            /** @var \App\Extensions\Chatbot\System\Services\GeneratorService $service */
            $service = app('App\Extensions\Chatbot\System\Services\GeneratorService');

            $service->setPrompt($prompt);

            if ($this->chatbot) {
                $service->setChatbot($this->chatbot);
            }

            if ($this->conversation) {
                $service->setConversation($this->conversation);
            }

            return (string) $service->generate();
        } catch (\Throwable $e) {
            Log::warning('GeneratorBridge: legacy service failed, falling back to OpenAI.', [
                'error' => $e->getMessage(),
            ]);

            return $this->generateViaOpenAi($prompt);
        }
    }

    private function generateViaOpenAi(string $prompt, array $context = []): string
    {
        $apiKey = config('titan-chatbot.ai.openai_api_key', config('openai.api_key', env('OPENAI_API_KEY')));
        $model  = config('titan-chatbot.ai.model', 'gpt-4o-mini');

        $messages = [];

        if ($systemPrompt = $this->buildSystemPrompt()) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($context as $ctx) {
            $messages[] = $ctx;
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = Http::withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $model,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            Log::error('GeneratorBridge: OpenAI request failed.', ['status' => $response->status()]);

            return "Sorry, I can't answer right now.";
        }

        return $response->json('choices.0.message.content', "Sorry, I can't answer right now.");
    }

    private function generateViaOpenAiWithUsage(string $prompt, array $context = []): array
    {
        $apiKey = config('titan-chatbot.ai.openai_api_key', config('openai.api_key', env('OPENAI_API_KEY')));
        $model  = config('titan-chatbot.ai.model', 'gpt-4o-mini');

        $messages = [];

        if ($systemPrompt = $this->buildSystemPrompt()) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($context as $ctx) {
            $messages[] = $ctx;
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = Http::withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $model,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            Log::error('GeneratorBridge: OpenAI request failed.', ['status' => $response->status()]);
            return [$this->fallbackMessage, 0, 0, 0];
        }

        $json             = $response->json();
        $reply            = $json['choices'][0]['message']['content'] ?? $this->fallbackMessage;
        $promptTokens     = $json['usage']['prompt_tokens'] ?? 0;
        $completionTokens = $json['usage']['completion_tokens'] ?? 0;
        $totalTokens      = $json['usage']['total_tokens'] ?? 0;

        return [$reply, $promptTokens, $completionTokens, $totalTokens];
    }

    private function buildSystemPrompt(): ?string
    {
        if (! $this->chatbot) {
            return null;
        }

        if (is_object($this->chatbot) && method_exists($this->chatbot, 'getAttribute')) {
            return $this->chatbot->getAttribute('instructions')
                ?? $this->chatbot->getAttribute('prompt')
                ?? null;
        }

        if (is_array($this->chatbot)) {
            return $this->chatbot['instructions'] ?? $this->chatbot['prompt'] ?? null;
        }

        return null;
    }
}
