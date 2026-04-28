<?php

namespace Modules\TitanChatbot\Search\Mappers;

class ConversationMapper
{
    /**
     * Map a Conversation model to a search-indexable document array.
     *
     * @param  mixed  $conversation
     * @return array<string, mixed>
     */
    public function map(mixed $conversation): array
    {
        return [
            'id'             => $conversation->id,
            'chatbot_id'     => $conversation->chatbot_id,
            'tenant_id'      => $conversation->tenant_id ?? null,
            'channel'        => $conversation->channel ?? null,
            'status'         => $conversation->status ?? null,
            'intent'         => $conversation->last_intent ?? null,
            'message_count'  => $conversation->message_count ?? 0,
            'started_at'     => optional($conversation->started_at)->toIso8601String(),
            'ended_at'       => optional($conversation->ended_at)->toIso8601String(),
            'created_at'     => optional($conversation->created_at)->toIso8601String(),
        ];
    }
}
