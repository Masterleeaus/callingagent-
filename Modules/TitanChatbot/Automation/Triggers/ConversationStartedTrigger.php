<?php

namespace Modules\TitanChatbot\Automation\Triggers;

use Modules\TitanChatbot\Events\ConversationStarted;

class ConversationStartedTrigger
{
    public function name(): string
    {
        return 'conversation_started';
    }

    public function description(): string
    {
        return 'Fires when a new conversation is initiated by a user.';
    }

    public function eventClass(): string
    {
        return ConversationStarted::class;
    }
}
