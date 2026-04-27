<?php
namespace Modules\CallingAgent\Notifications;

final class BookingConfirmationNotification
{
    public function message(array $booking): string
    {
        return sprintf('Your appointment%s is confirmed for %s.', isset($booking['service']) ? ' for '.$booking['service'] : '', $booking['time'] ?? 'the selected time');
    }
}
