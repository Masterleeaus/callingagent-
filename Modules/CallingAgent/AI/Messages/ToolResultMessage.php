<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Messages;

final readonly class ToolResultMessage
{
    public string $role;

    public function __construct(
        public string $toolCallId,
        public string $content,
    ) {
        $this->role = 'tool';
    }

    public function toArray(): array
    {
        return [
            'role' => 'tool',
            'tool_call_id' => $this->toolCallId,
            'content' => $this->content,
        ];
    }
}
