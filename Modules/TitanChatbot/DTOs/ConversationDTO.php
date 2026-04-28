<?php

namespace Modules\TitanChatbot\DTOs;

class ConversationDTO
{
    public function __construct(
        public readonly int|string $chatbotId,
        public readonly int|string $conversationId,
        public readonly string $sessionId,
        public readonly string $channel,
        public readonly array $messages = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            chatbotId: $data['chatbot_id'] ?? $data['chatbotId'],
            conversationId: $data['conversation_id'] ?? $data['conversationId'],
            sessionId: $data['session_id'] ?? $data['sessionId'],
            channel: $data['channel'],
            messages: $data['messages'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'chatbot_id'      => $this->chatbotId,
            'conversation_id' => $this->conversationId,
            'session_id'      => $this->sessionId,
            'channel'         => $this->channel,
            'messages'        => $this->messages,
        ];
    }

    public function withMessage(array $message): self
    {
        return new self(
            chatbotId: $this->chatbotId,
            conversationId: $this->conversationId,
            sessionId: $this->sessionId,
            channel: $this->channel,
            messages: [...$this->messages, $message],
        );
    }
}
