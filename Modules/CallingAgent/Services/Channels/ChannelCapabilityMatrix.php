<?php
namespace Modules\CallingAgent\Services\Channels;

final class ChannelCapabilityMatrix
{
    public function all(): array
    {
        return [
            'twilio_voice' => ['voice','recording','transfer','sms_fallback','media_streams'],
            'twilio_sms' => ['text','delivery_status'],
            'whatsapp' => ['text','media','templates'],
            'telegram' => ['text','media'],
            'messenger' => ['text','handoff'],
            'web_voice' => ['voice','transcript','agent_embed'],
        ];
    }
}
