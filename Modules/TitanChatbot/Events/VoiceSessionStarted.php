<?php

namespace Modules\TitanChatbot\Events;

class VoiceSessionStarted
{
    public function __construct(
        public readonly int $conversationId,
        public readonly mixed $chatbot = null,
    ) {}
}
