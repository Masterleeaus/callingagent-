<?php
namespace Modules\CallingAgent\DTOs;

final class RealtimeTurnData
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $speaker,
        public readonly string $text,
        public readonly bool $isFinal = false,
        public readonly array $meta = [],
    ) {}

    public function toArray(): array { return get_object_vars($this); }
}
