<?php
namespace Modules\CallingAgent\AI\Pipelines;

use Modules\CallingAgent\ValueObjects\StructuredCallOutcome;

final class OutcomeExtractionPipeline
{
    public function extract(array|string $transcript, array $context = []): StructuredCallOutcome
    {
        $text = is_array($transcript) ? implode(' ', array_map(fn ($row) => is_array($row) ? ($row['text'] ?? '') : (string)$row, $transcript)) : $transcript;
        $lower = strtolower($text);

        $intent = match (true) {
            str_contains($lower, 'book') || str_contains($lower, 'appointment') || str_contains($lower, 'schedule') => 'booking',
            str_contains($lower, 'price') || str_contains($lower, 'quote') || str_contains($lower, 'cost') => 'sales',
            str_contains($lower, 'support') || str_contains($lower, 'problem') || str_contains($lower, 'issue') => 'support',
            str_contains($lower, 'cancel') || str_contains($lower, 'refund') => 'retention',
            default => $context['default_intent'] ?? 'general',
        };

        $urgency = match (true) {
            str_contains($lower, 'urgent') || str_contains($lower, 'emergency') || str_contains($lower, 'today') => 'urgent',
            str_contains($lower, 'tomorrow') || str_contains($lower, 'soon') => 'high',
            default => 'normal',
        };

        return new StructuredCallOutcome(
            intent: $intent,
            urgency: $urgency,
            leadQuality: $this->leadQuality($lower, $intent),
            handoffRequired: str_contains($lower, 'human') || str_contains($lower, 'manager') || $urgency === 'urgent',
            bookingRequested: $intent === 'booking',
            sentiment: $this->sentiment($lower),
            entities: $this->entities($text),
            nextActions: $this->nextActions($intent, $urgency),
            summary: trim(mb_substr($text, 0, 600)),
        );
    }

    private function leadQuality(string $lower, string $intent): string
    {
        if (str_contains($lower, 'ready') || str_contains($lower, 'buy') || str_contains($lower, 'today')) return 'high';
        if (in_array($intent, ['sales','booking'], true)) return 'medium';
        return 'unknown';
    }

    private function sentiment(string $lower): string
    {
        if (str_contains($lower, 'angry') || str_contains($lower, 'frustrated') || str_contains($lower, 'terrible')) return 'negative';
        if (str_contains($lower, 'thanks') || str_contains($lower, 'great') || str_contains($lower, 'perfect')) return 'positive';
        return 'neutral';
    }

    private function entities(string $text): array
    {
        preg_match_all('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text, $emails);
        preg_match_all('/(?:\+?\d[\d\s().-]{7,}\d)/', $text, $phones);
        return ['emails' => array_values(array_unique($emails[0] ?? [])), 'phones' => array_values(array_unique($phones[0] ?? []))];
    }

    private function nextActions(string $intent, string $urgency): array
    {
        $actions = match ($intent) {
            'booking' => ['resolve_availability', 'offer_time_slots', 'send_confirmation'],
            'sales' => ['qualify_lead', 'send_quote_followup'],
            'support' => ['open_ticket', 'route_to_support'],
            default => ['send_call_summary'],
        };
        if ($urgency === 'urgent') array_unshift($actions, 'escalate_immediately');
        return $actions;
    }
}
