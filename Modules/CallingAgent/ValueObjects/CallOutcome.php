<?php
namespace Modules\CallingAgent\ValueObjects;

final class CallOutcome
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $summary = null,
        public readonly ?string $intent = null,
        public readonly ?int $leadScore = null,
        public readonly array $tags = [],
        public readonly array $actions = [],
    ) {}

    public function toArray(): array { return get_object_vars($this); }
}
