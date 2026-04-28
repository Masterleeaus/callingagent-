<?php

namespace Modules\TitanChatbot\Events;

class IntentDetected
{
    public function __construct(
        public readonly string $intent,
        public readonly float $confidence = 1.0,
        public readonly int $conversationId = 0,
        public readonly mixed $chatbot = null,
    ) {}
}
