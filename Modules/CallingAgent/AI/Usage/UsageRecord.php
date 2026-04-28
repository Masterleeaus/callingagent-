<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Usage;

final readonly class UsageRecord
{
    public function __construct(
        public string $provider,
        public string $model,
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $totalTokens = 0,
        public ?float $costUsd = null,
        public array $metadata = [],
    ) {}

    public static function fromOpenAiResponse(array $response, string $model): self
    {
        $usage = $response['usage'] ?? [];

        return new self(
            provider: 'openai',
            model: $model,
            promptTokens: (int) ($usage['prompt_tokens'] ?? 0),
            completionTokens: (int) ($usage['completion_tokens'] ?? 0),
            totalTokens: (int) ($usage['total_tokens'] ?? 0),
        );
    }

    public static function empty(string $provider = 'null', string $model = 'none'): self
    {
        return new self(provider: $provider, model: $model);
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'model' => $this->model,
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->totalTokens,
            'cost_usd' => $this->costUsd,
            'metadata' => $this->metadata,
        ];
    }
}
