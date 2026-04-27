<?php
namespace Modules\CallingAgent\ValueObjects;

final readonly class TransferDecision
{
    public function __construct(
        public string $route,
        public string $reason,
        public int $priority = 50,
        public array $targets = [],
        public array $fallbacks = [],
    ) {}

    public function toArray(): array
    {
        return [
            'route' => $this->route,
            'reason' => $this->reason,
            'priority' => $this->priority,
            'targets' => $this->targets,
            'fallbacks' => $this->fallbacks,
        ];
    }
}
