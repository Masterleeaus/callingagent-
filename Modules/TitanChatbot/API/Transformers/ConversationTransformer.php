<?php

namespace Modules\TitanChatbot\API\Transformers;

class ConversationTransformer
{
    public function transform($conversation): array
    {
        return [
            'id'             => $conversation->id,
            'chatbot_id'     => $conversation->chatbot_id,
            'session_id'     => $conversation->session_id,
            'channel'        => $conversation->channel,
            'messages_count' => $conversation->messages_count ?? $conversation->messages?->count() ?? 0,
            'started_at'     => $conversation->started_at?->toIso8601String(),
            'ended_at'       => $conversation->ended_at?->toIso8601String(),
        ];
    }

    public function transformCollection(iterable $conversations): array
    {
        $result = [];
        foreach ($conversations as $conversation) {
            $result[] = $this->transform($conversation);
        }

        return $result;
    }
}
