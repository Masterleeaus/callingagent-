<?php

namespace Modules\TitanChatbot\AI\Pipelines;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\AI\Tools\EmbeddingResolver;

class RagPipeline
{
    public function run(string $query, int $chatbotId, int $limit = 5): array
    {
        $embedding = app(EmbeddingResolver::class)->resolve($query);

        try {
            if (! empty($embedding)) {
                return $this->queryWithEmbedding($embedding, $chatbotId, $limit);
            }

            return $this->queryByKeyword($query, $chatbotId, $limit);
        } catch (\Throwable $e) {
            Log::warning('RagPipeline: query failed.', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function queryWithEmbedding(array $embedding, int $chatbotId, int $limit): array
    {
        $vector = '[' . implode(',', $embedding) . ']';

        $rows = DB::select(
            'SELECT content, (embedding <=> ?) AS score FROM chatbot_embeddings WHERE chatbot_id = ? ORDER BY score ASC LIMIT ?',
            [$vector, $chatbotId, $limit],
        );

        return array_map(fn ($r) => [
            'content' => $r->content,
            'score'   => (float) ($r->score ?? 0.0),
        ], $rows);
    }

    private function queryByKeyword(string $query, int $chatbotId, int $limit): array
    {
        $rows = DB::select(
            'SELECT content FROM chatbot_embeddings WHERE chatbot_id = ? AND content LIKE ? LIMIT ?',
            [$chatbotId, '%' . $query . '%', $limit],
        );

        return array_map(fn ($r) => [
            'content' => $r->content,
            'score'   => 1.0,
        ], $rows);
    }
}
