<?php
namespace Modules\CallingAgent\AI\Memory;

final class ConversationEmbeddingStore
{
    /** @var array<int,array{conversation_id:string,text:string,metadata:array,embedding:array}> */
    private array $items = [];

    public function add(string $conversationId, string $text, array $embedding = [], array $metadata = []): void
    {
        $this->items[] = compact('conversationId', 'text', 'embedding', 'metadata') + ['conversation_id' => $conversationId];
    }

    public function recentFor(string $conversationId, int $limit = 8): array
    {
        $matches = array_values(array_filter($this->items, fn ($item) => ($item['conversation_id'] ?? null) === $conversationId));
        return array_slice(array_reverse($matches), 0, $limit);
    }

    public function searchByKeyword(string $keyword, int $limit = 8): array
    {
        $keyword = strtolower($keyword);
        $matches = array_values(array_filter($this->items, fn ($item) => str_contains(strtolower($item['text']), $keyword)));
        return array_slice($matches, 0, $limit);
    }
}
