<?php

namespace Modules\TitanChatbot\DTOs;

class MessagePayload
{
    public function __construct(
        public readonly int|string $chatbotId,
        public readonly string $sessionId,
        public readonly string $channel,
        public readonly string $message,
        public readonly int|string|null $customerId = null,
        public readonly int|string|null $tenantId = null,
        public readonly array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            chatbotId: $data['chatbot_id'] ?? $data['chatbotId'],
            sessionId: $data['session_id'] ?? $data['sessionId'],
            channel: $data['channel'],
            message: $data['message'],
            customerId: $data['customer_id'] ?? $data['customerId'] ?? null,
            tenantId: $data['tenant_id'] ?? $data['tenantId'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'chatbot_id'  => $this->chatbotId,
            'session_id'  => $this->sessionId,
            'channel'     => $this->channel,
            'message'     => $this->message,
            'customer_id' => $this->customerId,
            'tenant_id'   => $this->tenantId,
            'metadata'    => $this->metadata,
        ];
    }
}
