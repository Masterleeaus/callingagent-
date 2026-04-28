<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Messages;

final readonly class UserMessage
{
    public string $role;

    public function __construct(public string|array $content)
    {
        $this->role = 'user';
    }

    public function toArray(): array
    {
        return [
            'role' => 'user',
            'content' => $this->content,
        ];
    }
}
