<?php

namespace Modules\TitanChatbot\Search\Transformers;

class ConversationSearchTransformer
{
    /**
     * Transform a raw search hit into a structured result array.
     *
     * @param  array<string, mixed>  $hit
     * @return array<string, mixed>
     */
    public function transform(array $hit): array
    {
        $source = $hit['_source'] ?? $hit;

        return [
            'id'            => $source['id'] ?? null,
            'chatbot_id'    => $source['chatbot_id'] ?? null,
            'tenant_id'     => $source['tenant_id'] ?? null,
            'channel'       => $source['channel'] ?? null,
            'status'        => $source['status'] ?? null,
            'intent'        => $source['intent'] ?? null,
            'message_count' => (int) ($source['message_count'] ?? 0),
            'started_at'    => $source['started_at'] ?? null,
            'ended_at'      => $source['ended_at'] ?? null,
            'score'         => $hit['_score'] ?? null,
        ];
    }
}
