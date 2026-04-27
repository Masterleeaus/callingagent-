<?php
namespace Modules\CallingAgent\Contracts;

interface CalendarProvider
{
    public function availability(array $query): array;
    public function book(array $payload): array;
    public function cancel(string $bookingId, array $context = []): bool;
}
