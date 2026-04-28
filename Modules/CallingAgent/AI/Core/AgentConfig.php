<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Core;

final readonly class AgentConfig
{
    public function __construct(
        public string $driver = 'null',
        public string $model = 'gpt-4o',
        public string $systemPrompt = '',
        public int $maxTokens = 1024,
        public float $temperature = 0.7,
        public array $tools = [],
        public bool $structured = false,
        public string $history = 'array',
        public int $historyLimit = 10,
        public string $truncationStrategy = 'sliding',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            driver: (string) ($data['driver'] ?? 'null'),
            model: (string) ($data['model'] ?? 'gpt-4o'),
            systemPrompt: (string) ($data['systemPrompt'] ?? $data['system_prompt'] ?? ''),
            maxTokens: (int) ($data['maxTokens'] ?? $data['max_tokens'] ?? 1024),
            temperature: (float) ($data['temperature'] ?? 0.7),
            tools: (array) ($data['tools'] ?? []),
            structured: (bool) ($data['structured'] ?? false),
            history: (string) ($data['history'] ?? 'array'),
            historyLimit: (int) ($data['historyLimit'] ?? $data['history_limit'] ?? 10),
            truncationStrategy: (string) ($data['truncationStrategy'] ?? $data['truncation_strategy'] ?? 'sliding'),
        );
    }

    public function toArray(): array
    {
        return [
            'driver' => $this->driver,
            'model' => $this->model,
            'systemPrompt' => $this->systemPrompt,
            'maxTokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'tools' => $this->tools,
            'structured' => $this->structured,
            'history' => $this->history,
            'historyLimit' => $this->historyLimit,
            'truncationStrategy' => $this->truncationStrategy,
        ];
    }
}
