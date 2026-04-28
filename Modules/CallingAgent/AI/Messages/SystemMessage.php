<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Messages;

final readonly class SystemMessage
{
    public string $role;

    public function __construct(public string $content)
    {
        $this->role = 'system';
    }

    public function toArray(): array
    {
        return [
            'role' => 'system',
            'content' => $this->content,
        ];
    }
}
