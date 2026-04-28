<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Messages;

final class AssistantMessage
{
    public string $role = 'assistant';

    public function __construct(
        public string $content = '',
        public array $toolCalls = [],
    ) {}

    public function hasToolCalls(): bool
    {
        return ! empty($this->toolCalls);
    }

    public function toArray(): array
    {
        $data = [
            'role' => 'assistant',
            'content' => $this->content,
        ];

        if (! empty($this->toolCalls)) {
            $data['tool_calls'] = $this->toolCalls;
        }

        return $data;
    }
}
