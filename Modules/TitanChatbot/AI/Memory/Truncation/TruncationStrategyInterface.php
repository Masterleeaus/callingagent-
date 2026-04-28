<?php
namespace Modules\TitanChatbot\AI\Memory\Truncation;

interface TruncationStrategyInterface
{
    /**
     * Truncate/summarize messages to fit within limit.
     * @param array<array{role: string, content: string}> $messages
     * @return array<array{role: string, content: string}>
     */
    public function truncate(array $messages, int $limit): array;
}
