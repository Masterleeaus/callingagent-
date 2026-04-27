<?php
namespace Modules\CallingAgent\Services\Calendar;

use Modules\CallingAgent\Contracts\CalendarProvider;

final class CalDavCalendarProvider implements CalendarProvider
{
    public function availability(array $query): array { return ['provider' => 'caldav', 'slots' => $query['slots'] ?? []]; }
    public function book(array $payload): array { return ['provider' => 'caldav', 'booking_id' => $payload['booking_id'] ?? uniqid('caldav_', true), 'payload' => $payload]; }
    public function cancel(string $bookingId, array $context = []): bool { return true; }
}
