<?php
namespace Modules\CallingAgent\Services\Booking;

use Modules\CallingAgent\DTOs\BookingIntentData;

final class BookingIntentResolver
{
    public function fromTranscript(string $text, array $lead = []): BookingIntentData
    {
        $service = str_contains(strtolower($text), 'consult') ? 'consultation' : null;
        return BookingIntentData::fromArray($lead + ['service' => $service, 'raw_text' => $text]);
    }
}
