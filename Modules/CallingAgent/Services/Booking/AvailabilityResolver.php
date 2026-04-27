<?php
namespace Modules\CallingAgent\Services\Booking;

final class AvailabilityResolver
{
    public function slots(array $calendar, string $date, int $durationMinutes = 30): array
    {
        $open = $calendar['open'] ?? '09:00'; $close = $calendar['close'] ?? '17:00';
        $slots = []; $cursor = strtotime("$date $open"); $end = strtotime("$date $close");
        while ($cursor + $durationMinutes * 60 <= $end) {
            $slots[] = date('c', $cursor); $cursor += $durationMinutes * 60;
        }
        return $slots;
    }
}
