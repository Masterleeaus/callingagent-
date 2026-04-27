<?php
namespace Modules\CallingAgent\ValueObjects;

final readonly class StructuredCallOutcome
{
    public function __construct(
        public string $intent = 'unknown',
        public string $urgency = 'normal',
        public string $leadQuality = 'unknown',
        public bool $handoffRequired = false,
        public bool $bookingRequested = false,
        public ?string $sentiment = null,
        public array $entities = [],
        public array $nextActions = [],
        public ?string $summary = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            intent: (string)($data['intent'] ?? 'unknown'),
            urgency: (string)($data['urgency'] ?? 'normal'),
            leadQuality: (string)($data['lead_quality'] ?? $data['leadQuality'] ?? 'unknown'),
            handoffRequired: (bool)($data['handoff_required'] ?? $data['handoffRequired'] ?? false),
            bookingRequested: (bool)($data['booking_requested'] ?? $data['bookingRequested'] ?? false),
            sentiment: $data['sentiment'] ?? null,
            entities: $data['entities'] ?? [],
            nextActions: $data['next_actions'] ?? $data['nextActions'] ?? [],
            summary: $data['summary'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'urgency' => $this->urgency,
            'lead_quality' => $this->leadQuality,
            'handoff_required' => $this->handoffRequired,
            'booking_requested' => $this->bookingRequested,
            'sentiment' => $this->sentiment,
            'entities' => $this->entities,
            'next_actions' => $this->nextActions,
            'summary' => $this->summary,
        ];
    }
}
