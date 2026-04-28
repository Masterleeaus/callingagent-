<?php
namespace Modules\CallingAgent\Services\Realtime;

final class TwilioMediaStreamRelay
{
    public function normalize(array $event): array
    {
        return [
            'event'      => $event['event'] ?? null,
            'stream_sid' => $event['streamSid'] ?? $event['stream_sid'] ?? null,
            'sequence'   => $event['sequenceNumber'] ?? $event['sequence'] ?? null,
            'payload'    => $event['media']['payload'] ?? null,
            'raw'        => $event,
        ];
    }

    public function shouldFlush(AudioFrameBuffer $buffer, int $threshold = 20): bool
    {
        return $buffer->count() >= $threshold;
    }
}
