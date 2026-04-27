<?php

namespace Modules\TitanChatbot\Events;

use Modules\TitanChatbot\DTOs\MessagePayload;

class ConversationStarted
{
    public function __construct(
        public readonly MessagePayload $payload,
        public readonly mixed $chatbot = null,
    ) {}
}
