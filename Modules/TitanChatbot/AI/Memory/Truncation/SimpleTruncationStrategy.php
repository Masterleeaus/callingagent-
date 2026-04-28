<?php
namespace Modules\TitanChatbot\AI\Memory\Truncation;

class SimpleTruncationStrategy implements TruncationStrategyInterface
{
    /**
     * Always keep the last $limit messages.
     * Preserves the system message if it exists.
     */
    public function truncate(array $messages, int $limit): array
    {
        if (count($messages) <= $limit) {
            return $messages;
        }

        $system = [];
        $rest   = [];
        foreach ($messages as $msg) {
            if (($msg['role'] ?? '') === 'system') {
                $system[] = $msg;
            } else {
                $rest[] = $msg;
            }
        }

        $keep = array_slice($rest, -$limit);

        return array_merge($system, $keep);
    }
}
