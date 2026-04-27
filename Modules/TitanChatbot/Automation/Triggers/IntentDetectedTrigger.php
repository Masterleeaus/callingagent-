<?php

namespace Modules\TitanChatbot\Automation\Triggers;

use Modules\TitanChatbot\Events\IntentDetected;

class IntentDetectedTrigger
{
    public function __construct(
        public readonly string $intentName = '',
    ) {}

    public function name(): string
    {
        return 'intent_detected';
    }

    public function description(): string
    {
        return 'Fires when a specific intent is detected in the conversation.';
    }

    public function eventClass(): string
    {
        return IntentDetected::class;
    }
}
