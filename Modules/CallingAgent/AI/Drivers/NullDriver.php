<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Drivers;

use Modules\CallingAgent\AI\Core\AgentConfig;
use Modules\CallingAgent\AI\Core\AgentEngine;
use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Usage\UsageRecord;

final class NullDriver implements DriverInterface, AgentEngine
{
    public function send(array $messages, array $config = []): array
    {
        return [
            'id' => 'null-' . uniqid(),
            'object' => 'chat.completion',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => '',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ],
        ];
    }

    public function sendStream(array $messages, array $config, callable $onChunk): void
    {
        // No-op: null driver produces no stream output
    }

    public function getUsage(): UsageRecord
    {
        return UsageRecord::empty('null', 'none');
    }

    public function getName(): string
    {
        return 'null';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    // AgentEngine implementation
    public function generate(MessageCollection $messages, AgentConfig $config): AssistantMessage
    {
        return new AssistantMessage('');
    }

    public function supports(string $driver): bool
    {
        return true;
    }
}
