<?php
namespace Modules\CallingAgent\DTOs;

final class BookingIntentData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $service = null,
        public readonly ?string $preferredTime = null,
        public readonly ?string $timezone = null,
        public readonly array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self($data['name'] ?? null, $data['phone'] ?? null, $data['email'] ?? null, $data['service'] ?? null, $data['preferred_time'] ?? null, $data['timezone'] ?? null, $data);
    }
}
