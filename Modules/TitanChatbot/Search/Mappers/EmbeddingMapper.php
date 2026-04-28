<?php

namespace Modules\TitanChatbot\Search\Mappers;

class EmbeddingMapper
{
    /**
     * Map an embedding record to a search-indexable document array.
     *
     * @param  mixed  $embedding
     * @return array<string, mixed>
     */
    public function map(mixed $embedding): array
    {
        return [
            'id'           => $embedding->id,
            'chatbot_id'   => $embedding->chatbot_id,
            'tenant_id'    => $embedding->tenant_id ?? null,
            'source_type'  => $embedding->source_type ?? null,
            'source_id'    => $embedding->source_id ?? null,
            'content'      => $embedding->content ?? null,
            'vector'       => $embedding->vector ?? [],
            'model'        => $embedding->model ?? null,
            'created_at'   => optional($embedding->created_at)->toIso8601String(),
        ];
    }
}
