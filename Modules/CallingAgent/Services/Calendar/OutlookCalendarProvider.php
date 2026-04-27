<?php
namespace Modules\CallingAgent\Services\Calendar;

use Modules\CallingAgent\Contracts\CalendarProvider;

final class OutlookCalendarProvider implements CalendarProvider
{
    public function availability(array $query): array { return ['provider' => 'outlook', 'slots' => $query['slots'] ?? []]; }
    public function book(array $payload): array { return ['provider' => 'outlook', 'booking_id' => $payload['booking_id'] ?? uniqid('outlook_', true), 'payload' => $payload]; }
    public function cancel(string $bookingId, array $context = []): bool { return true; }
}
