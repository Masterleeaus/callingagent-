<?php
namespace Modules\TitanChatbot\Billing\Limits;

class VoiceSecondsLimit
{
    public function key(): string
    {
        return 'voice_call_seconds';
    }
}
