<?php

namespace Modules\TitanChatbot\Automation\Triggers;

use Modules\TitanChatbot\Events\ConversationEscalated;

class ConversationEscalatedTrigger
{
    public function name(): string
    {
        return 'conversation_escalated';
    }

    public function description(): string
    {
        return 'Fires when a conversation is escalated to a human agent.';
    }

    public function eventClass(): string
    {
        return ConversationEscalated::class;
    }
}
