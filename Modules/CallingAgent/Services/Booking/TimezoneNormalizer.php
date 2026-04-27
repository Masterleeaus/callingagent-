<?php
namespace Modules\CallingAgent\Services\Booking;

use DateTimeImmutable;
use DateTimeZone;

final class TimezoneNormalizer
{
    public function normalize(string $time, string $fromTz, string $toTz = 'UTC'): string
    {
        return (new DateTimeImmutable($time, new DateTimeZone($fromTz)))->setTimezone(new DateTimeZone($toTz))->format(DATE_ATOM);
    }
}
