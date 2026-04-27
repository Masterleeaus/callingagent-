<?php
namespace Modules\CallingAgent\Services\Channels;

final class UnifiedChannelRouter
{
    public function route(array $message): string
    {
        if (($message['type'] ?? null) === 'call') return 'twilio_voice';
        if (str_starts_with((string)($message['from'] ?? ''), 'whatsapp:')) return 'whatsapp';
        return $message['channel'] ?? 'web_voice';
    }
}
