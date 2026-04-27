<?php
namespace Modules\CallingAgent\DTOs;

final readonly class CallerProfileData
{
    public function __construct(
        public ?string $phone = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $company = null,
        public array $tags = [],
        public array $preferences = [],
        public array $lastOutcome = [],
        public ?string $lastSeenAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            phone: $data['phone'] ?? $data['caller_phone'] ?? null,
            name: $data['name'] ?? $data['caller_name'] ?? null,
            email: $data['email'] ?? null,
            company: $data['company'] ?? null,
            tags: array_values($data['tags'] ?? []),
            preferences: $data['preferences'] ?? [],
            lastOutcome: $data['last_outcome'] ?? $data['lastOutcome'] ?? [],
            lastSeenAt: $data['last_seen_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'phone' => $this->phone,
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->company,
            'tags' => $this->tags,
            'preferences' => $this->preferences,
            'last_outcome' => $this->lastOutcome,
            'last_seen_at' => $this->lastSeenAt,
        ];
    }
}
