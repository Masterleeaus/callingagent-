<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrainingPipeline
{
    /**
     * Ingest content into chatbot embeddings.
     *
     * @param  int    $chatbotId
     * @param  string $sourceType  text|qa|pdf|url
     * @param  string $content
     * @param  array  $metadata    Optionally: title, source_url, engine
     * @return int    Number of chunks created
     */
    public function ingest(int $chatbotId, string $sourceType, string $content, array $metadata = []): int
    {
        if (! Schema::hasTable('ext_chatbot_embeddings')) {
            return 0;
        }

        $chunks = match ($sourceType) {
            'qa'    => $this->chunkQa($content),
            default => $this->chunkText($content),
        };

        if (empty($chunks)) {
            return 0;
        }

        $engine    = $metadata['engine']     ?? 'default';
        $title     = $metadata['title']      ?? null;
        $sourceUrl = $metadata['source_url'] ?? null;
        $now       = now();

        foreach ($chunks as $chunk) {
            DB::table('ext_chatbot_embeddings')->insert([
                'chatbot_id' => $chatbotId,
                'engine'     => $engine,
                'title'      => $title,
                'url'        => $sourceUrl,
                'content'    => $chunk,
                'type'       => $sourceType,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return count($chunks);
    }

    /**
     * Split plain text into overlapping chunks.
     *
     * @param  string $text
     * @param  int    $chunkSize  Target word count per chunk
     * @return array<string>
     */
    public function chunkText(string $text, int $chunkSize = 500): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $words  = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $total  = count($words);
        $chunks = [];
        $step   = (int) ($chunkSize * 0.8); // 20 % overlap

        for ($i = 0; $i < $total; $i += $step) {
            $slice = array_slice($words, $i, $chunkSize);
            $chunks[] = implode(' ', $slice);

            if ($i + $chunkSize >= $total) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Parse Q: / A: formatted content into individual Q+A chunks.
     *
     * @param  string $content
     * @return array<string>
     */
    public function chunkQa(string $content): array
    {
        $chunks = [];
        $lines  = preg_split('/\r?\n/', $content);

        $currentQ = null;
        $currentA = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^Q:\s*(.*)/i', $line, $m)) {
                if ($currentQ !== null && ! empty($currentA)) {
                    $chunks[] = 'Q: ' . $currentQ . "\nA: " . implode(' ', $currentA);
                }
                $currentQ = trim($m[1]);
                $currentA = [];
                continue;
            }

            if (preg_match('/^A:\s*(.*)/i', $line, $m)) {
                $currentA[] = trim($m[1]);
                continue;
            }

            // Continuation of an answer
            if ($currentQ !== null && $line !== '') {
                $currentA[] = $line;
            }
        }

        if ($currentQ !== null && ! empty($currentA)) {
            $chunks[] = 'Q: ' . $currentQ . "\nA: " . implode(' ', $currentA);
        }

        return array_filter($chunks);
    }
}
