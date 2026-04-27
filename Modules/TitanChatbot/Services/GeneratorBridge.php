<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeneratorBridge
{
    private mixed $chatbot = null;
    private mixed $conversation = null;

    public function generate(string $prompt, array $context = []): string
    {
        $provider = config('titan-chatbot.ai.provider', 'openai');

        if ($provider !== 'openai' && $this->hasLegacyGeneratorService()) {
            return $this->generateViaLegacyService($prompt);
        }

        return $this->generateViaOpenAi($prompt, $context);
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
