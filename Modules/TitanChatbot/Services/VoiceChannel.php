<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Contracts\ChannelDriver;

class VoiceChannel implements ChannelDriver
{
    public function handle(array $payload): string
    {
        $message = $payload['message'] ?? '';
        $chatbot = $payload['chatbot'] ?? null;

        // Integrate with VoiceConversationPipeline when available
        if (class_exists(\Modules\TitanChatbot\AI\Pipelines\VoiceConversationPipeline::class)) {
            return $this->handleViaPipeline($payload);
        }

        return $this->handleDirect($message, $chatbot);
    }

    private function handleViaPipeline(array $payload): string
    {
        try {
            /** @var \Modules\TitanChatbot\AI\Pipelines\VoiceConversationPipeline $pipeline */
            $pipeline = app(\Modules\TitanChatbot\AI\Pipelines\VoiceConversationPipeline::class);

            return (string) $pipeline->process($payload);
        } catch (\Throwable $e) {
            Log::error('VoiceChannel: pipeline failed.', ['error' => $e->getMessage()]);

            return $this->handleDirect($payload['message'] ?? '', $payload['chatbot'] ?? null);
        }
    }

    private function handleDirect(string $message, mixed $chatbot): string
    {
        /** @var GeneratorBridge $generator */
        $generator = app(GeneratorBridge::class);

        return $generator
            ->setChatbot($chatbot)
            ->generate($message, [
                ['role' => 'system', 'content' => 'You are a voice assistant. Keep responses concise and spoken-word-friendly.'],
            ]);
    }
}
