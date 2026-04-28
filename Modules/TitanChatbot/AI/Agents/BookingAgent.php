<?php

namespace Modules\TitanChatbot\AI\Agents;

use Modules\TitanChatbot\AI\Memory\ConversationMemoryStore;
use Modules\TitanChatbot\Services\GeneratorBridge;
use Throwable;

/**
 * Booking-specific agent — extends TitanAgent via ConversationAgent.
 * Adds structured booking-detail extraction on top of the base conversation flow.
 */
class BookingAgent extends ConversationAgent
{
    public string $system_prompt = 'You are a booking assistant. Collect the customer\'s name, preferred appointment date, time, and contact number. Confirm details before finalising. Be concise and friendly.';

    public string $escalation_strategy = 'handoff';

    /** @var array<string, string|null> */
    private array $bookingBuffer = [];

    public function collectBookingDetails(string $message, array $context = []): array
    {
        $sessionId = $context['session_id'] ?? 'default';
        $memory    = app(ConversationMemoryStore::class);

        $existing = $context['booking'] ?? [];
        $details  = $this->extractDetails($message, $existing);

        $memory->remember($sessionId, 'user', $message);

        if ($this->isComplete($details)) {
            $reply = 'Thank you! I\'ve got all your details. Your appointment is confirmed.';
            $memory->remember($sessionId, 'assistant', $reply);

            return array_merge($details, ['complete' => true, 'reply' => $reply]);
        }

        $reply = $this->askForMissing($details);

        try {
            $history    = $memory->recall($sessionId);
            $builtCtx   = array_merge(
                [['role' => 'system', 'content' => $this->system_prompt]],
                $history,
            );
            $reply = app(GeneratorBridge::class)->generate($message, $builtCtx);
        } catch (Throwable $e) {
            report($e);
        }

        $memory->remember($sessionId, 'assistant', $reply);

        return array_merge($details, ['complete' => false, 'reply' => $reply]);
    }

    private function extractDetails(string $message, array $existing = []): array
    {
        $details = array_merge(['name' => null, 'date' => null, 'time' => null, 'contact' => null], $existing);

        if (! $details['name'] && preg_match('/(?:my name is|i\'?m|name[:\s]+)\s*([A-Z][a-z]+(?: [A-Z][a-z]+)?)/i', $message, $m)) {
            $details['name'] = trim($m[1]);
        }

        if (! $details['date'] && preg_match('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}|(?:monday|tuesday|wednesday|thursday|friday|saturday|sunday|tomorrow|today))\b/i', $message, $m)) {
            $details['date'] = trim($m[1]);
        }

        if (! $details['time'] && preg_match('/\b(\d{1,2}(?::\d{2})?\s*(?:am|pm))\b/i', $message, $m)) {
            $details['time'] = trim($m[1]);
        }

        if (! $details['contact'] && preg_match('/\b(\+?[\d\s\-]{7,15})\b/', $message, $m)) {
            $details['contact'] = trim($m[1]);
        }

        return $details;
    }

    private function isComplete(array $details): bool
    {
        return ! empty($details['name'])
            && ! empty($details['date'])
            && ! empty($details['time'])
            && ! empty($details['contact']);
    }

    private function askForMissing(array $details): string
    {
        if (empty($details['name']))    return 'Could I get your name please?';
        if (empty($details['date']))    return 'What date works best for you?';
        if (empty($details['time']))    return 'What time would you prefer?';
        if (empty($details['contact'])) return 'And your best contact number?';

        return 'Let me confirm your details — could you repeat that?';
    }
}
