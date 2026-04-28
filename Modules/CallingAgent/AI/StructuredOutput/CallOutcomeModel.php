<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\StructuredOutput;

use Modules\CallingAgent\ValueObjects\StructuredCallOutcome;

final class CallOutcomeModel extends DataModel
{
    public function __construct(
        public string $intent = 'unknown',
        public string $urgency = 'normal',
        public string $leadQuality = 'unknown',
        public bool $handoffRequired = false,
        public bool $bookingRequested = false,
        public string $sentiment = 'neutral',
        public string $summary = '',
        public array $entities = [],
        public array $nextActions = [],
    ) {}

    public static function schema(): array
    {
        return [
            'properties' => [
                'intent' => [
                    'type' => 'string',
                    'enum' => ['unknown', 'booking', 'inquiry', 'complaint', 'transfer', 'cancellation', 'follow_up', 'other'],
                ],
                'urgency' => [
                    'type' => 'string',
                    'enum' => ['low', 'normal', 'high', 'critical'],
                ],
                'leadQuality' => [
                    'type' => 'string',
                    'enum' => ['unknown', 'cold', 'warm', 'hot', 'qualified'],
                ],
                'handoffRequired' => ['type' => 'boolean'],
                'bookingRequested' => ['type' => 'boolean'],
                'sentiment' => [
                    'type' => 'string',
                    'enum' => ['positive', 'neutral', 'negative', 'frustrated', 'satisfied'],
                ],
                'summary' => ['type' => 'string'],
                'entities' => ['type' => 'array'],
                'nextActions' => ['type' => 'array'],
            ],
            'required' => [
                'intent',
                'urgency',
                'leadQuality',
                'handoffRequired',
                'bookingRequested',
                'sentiment',
                'summary',
            ],
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            intent: (string) ($data['intent'] ?? 'unknown'),
            urgency: (string) ($data['urgency'] ?? 'normal'),
            leadQuality: (string) ($data['leadQuality'] ?? $data['lead_quality'] ?? 'unknown'),
            handoffRequired: (bool) ($data['handoffRequired'] ?? $data['handoff_required'] ?? false),
            bookingRequested: (bool) ($data['bookingRequested'] ?? $data['booking_requested'] ?? false),
            sentiment: (string) ($data['sentiment'] ?? 'neutral'),
            summary: (string) ($data['summary'] ?? ''),
            entities: (array) ($data['entities'] ?? []),
            nextActions: (array) ($data['nextActions'] ?? $data['next_actions'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'urgency' => $this->urgency,
            'leadQuality' => $this->leadQuality,
            'handoffRequired' => $this->handoffRequired,
            'bookingRequested' => $this->bookingRequested,
            'sentiment' => $this->sentiment,
            'summary' => $this->summary,
            'entities' => $this->entities,
            'nextActions' => $this->nextActions,
        ];
    }

    public function toStructuredCallOutcome(): StructuredCallOutcome
    {
        return new StructuredCallOutcome(
            intent: $this->intent,
            urgency: $this->urgency,
            leadQuality: $this->leadQuality,
            handoffRequired: $this->handoffRequired,
            bookingRequested: $this->bookingRequested,
            sentiment: $this->sentiment,
            entities: $this->entities,
            nextActions: $this->nextActions,
            summary: $this->summary,
        );
    }

    public static function fromStructuredCallOutcome(StructuredCallOutcome $outcome): static
    {
        return new self(
            intent: $outcome->intent,
            urgency: $outcome->urgency,
            leadQuality: $outcome->leadQuality,
            handoffRequired: $outcome->handoffRequired,
            bookingRequested: $outcome->bookingRequested,
            sentiment: $outcome->sentiment ?? 'neutral',
            summary: $outcome->summary ?? '',
            entities: $outcome->entities,
            nextActions: $outcome->nextActions,
        );
    }

    public static function validate(array $data): bool
    {
        $schema = static::schema();
        $required = $schema['required'] ?? [];

        foreach ($required as $field) {
            if (! array_key_exists($field, $data)) {
                return false;
            }
        }

        $enumFields = [
            'intent' => ['unknown', 'booking', 'inquiry', 'complaint', 'transfer', 'cancellation', 'follow_up', 'other'],
            'urgency' => ['low', 'normal', 'high', 'critical'],
            'leadQuality' => ['unknown', 'cold', 'warm', 'hot', 'qualified'],
            'sentiment' => ['positive', 'neutral', 'negative', 'frustrated', 'satisfied'],
        ];

        foreach ($enumFields as $field => $allowed) {
            if (isset($data[$field]) && ! in_array($data[$field], $allowed, true)) {
                return false;
            }
        }

        return true;
    }
}
