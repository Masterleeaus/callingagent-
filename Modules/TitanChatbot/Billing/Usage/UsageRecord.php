<?php

namespace Modules\TitanChatbot\Billing\Usage;

class UsageRecord
{
    public function __construct(
        public readonly string $provider          = '',
        public readonly string $model             = '',
        public readonly int    $promptTokens      = 0,
        public readonly int    $completionTokens  = 0,
        public readonly int    $totalTokens       = 0,
        public readonly string $channel           = '',
        public readonly int    $tenantId          = 0,
        public readonly int    $chatbotId         = 0,
        public readonly string $sessionId         = '',
        public readonly string $recordedAt        = '',
    ) {}

    public function toArray(): array
    {
        return [
            'provider'          => $this->provider,
            'model'             => $this->model,
            'prompt_tokens'     => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens'      => $this->totalTokens,
            'channel'           => $this->channel,
            'tenant_id'         => $this->tenantId,
            'chatbot_id'        => $this->chatbotId,
            'session_id'        => $this->sessionId,
            'recorded_at'       => $this->recordedAt,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            provider:         $data['provider'] ?? '',
            model:            $data['model'] ?? '',
            promptTokens:     $data['prompt_tokens'] ?? 0,
            completionTokens: $data['completion_tokens'] ?? 0,
            totalTokens:      $data['total_tokens'] ?? 0,
            channel:          $data['channel'] ?? '',
            tenantId:         $data['tenant_id'] ?? 0,
            chatbotId:        $data['chatbot_id'] ?? 0,
            sessionId:        $data['session_id'] ?? '',
            recordedAt:       $data['recorded_at'] ?? '',
        );
    }

    public function normalize(): static
    {
        $total = $this->totalTokens > 0
            ? $this->totalTokens
            : $this->promptTokens + $this->completionTokens;

        return new static(
            provider:         $this->provider,
            model:            $this->model,
            promptTokens:     $this->promptTokens,
            completionTokens: $this->completionTokens,
            totalTokens:      $total,
            channel:          $this->channel,
            tenantId:         $this->tenantId,
            chatbotId:        $this->chatbotId,
            sessionId:        $this->sessionId,
            recordedAt:       $this->recordedAt ?: date('c'),
        );
    }
}
