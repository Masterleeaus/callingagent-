<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Contracts\ChannelDriver;
use Modules\TitanChatbot\Models\Chatbot;
use Modules\TitanChatbot\Models\Conversation;

class WebchatChannel implements ChannelDriver
{
    public function __construct(
        private readonly GeneratorBridge $generator,
        private readonly ConversationStateStore $stateStore,
    ) {}

    public function handle(array $payload): string
    {
        $chatbotId = $payload['chatbot_id'];
        $sessionId = $payload['session_id'];
        $message   = $payload['message'];

        $chatbot = $payload['chatbot'] instanceof Chatbot
            ? $payload['chatbot']
            : Chatbot::find($chatbotId);

        $conversation = $this->findOrCreateConversation($chatbotId, $sessionId, $payload);

        $this->storeMessage($sessionId, 'user', $message);

        $history = $this->buildContext($sessionId);

        $response = $this->generator
            ->setChatbot($chatbot)
            ->setConversation($conversation)
            ->generate($message, $history);

        $this->storeMessage($sessionId, 'assistant', $response);

        $this->persistHistory($conversation, $message, $response);

        return $response;
    }

    private function findOrCreateConversation(int|string $chatbotId, string $sessionId, array $payload): ?Conversation
    {
        try {
            return Conversation::firstOrCreate(
                ['session_id' => $sessionId, 'chatbot_id' => $chatbotId],
                [
                    'channel'     => 'website',
                    'customer_id' => $payload['customer_id'] ?? null,
                    'tenant_id'   => $payload['tenant_id'] ?? null,
                    'started_at'  => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('WebchatChannel: could not persist conversation.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function buildContext(string $sessionId): array
    {
        $stored = $this->stateStore->get($sessionId, 'history') ?? [];

        return array_map(fn ($item) => [
            'role'    => $item['role'],
            'content' => $item['content'],
        ], $stored);
    }

    private function storeMessage(string $sessionId, string $role, string $content): void
    {
        $history   = $this->stateStore->get($sessionId, 'history') ?? [];
        $history[] = ['role' => $role, 'content' => $content, 'at' => now()->toIso8601String()];

        // Keep last 40 messages to stay within token budget
        if (count($history) > 40) {
            $history = array_slice($history, -40);
        }

        $this->stateStore->set($sessionId, 'history', $history);
    }

    private function persistHistory(?Conversation $conversation, string $userMessage, string $assistantResponse): void
    {
        if (! $conversation) {
            return;
        }

        try {
            $existing = $conversation->getAttribute('messages') ?? [];
            $existing[] = ['role' => 'user', 'content' => $userMessage, 'at' => now()->toIso8601String()];
            $existing[] = ['role' => 'assistant', 'content' => $assistantResponse, 'at' => now()->toIso8601String()];
            $conversation->fill(['messages' => $existing])->save();
        } catch (\Throwable $e) {
            Log::debug('WebchatChannel: history persist skipped.', ['error' => $e->getMessage()]);
        }
    }
}
