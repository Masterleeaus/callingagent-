<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Context;

final readonly class SessionIdentity
{
    public function __construct(
        public string $sessionId,
        public ?string $callSid = null,
        public ?int $tenantId = null,
        public ?int $agentId = null,
        public array $metadata = [],
    ) {}

    public static function fromCallSid(string $callSid, array $meta = []): self
    {
        return new self(
            sessionId: 'call_' . $callSid,
            callSid: $callSid,
            metadata: $meta,
        );
    }

    public static function generate(array $meta = []): self
    {
        $sessionId = self::generateUuid();

        return new self(
            sessionId: $sessionId,
            metadata: $meta,
        );
    }

    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'call_sid' => $this->callSid,
            'tenant_id' => $this->tenantId,
            'agent_id' => $this->agentId,
            'metadata' => $this->metadata,
        ];
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
