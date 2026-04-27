<?php
namespace Modules\CallingAgent\Services\Calendar;

use Modules\CallingAgent\Contracts\CalendarProvider;

final class GoogleCalendarProvider implements CalendarProvider
{
    public function availability(array $query): array { return ['provider' => 'google', 'slots' => $query['slots'] ?? []]; }
    public function book(array $payload): array { return ['provider' => 'google', 'booking_id' => $payload['booking_id'] ?? uniqid('gcal_', true), 'payload' => $payload]; }
    public function cancel(string $bookingId, array $context = []): bool { return true; }
}
