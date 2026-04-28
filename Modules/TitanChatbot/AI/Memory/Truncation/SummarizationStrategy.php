<?php
namespace Modules\TitanChatbot\AI\Memory\Truncation;

use Illuminate\Support\Facades\Log;

class SummarizationStrategy implements TruncationStrategyInterface
{
    private int $keepRecent;

    public function __construct(int $keepRecent = 10)
    {
        $this->keepRecent = $keepRecent;
    }

    /**
     * Keep last $keepRecent messages; summarize older ones into a system note.
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

        $toSummarize = array_slice($rest, 0, -$this->keepRecent);
        $recent      = array_slice($rest, -$this->keepRecent);

        $summary = $this->buildSummary($toSummarize);

        $summaryMsg = [
            'role'    => 'system',
            'content' => '[Conversation summary: ' . $summary . ']',
        ];

        return array_merge($system, [$summaryMsg], $recent);
    }

    private function buildSummary(array $messages): string
    {
        try {
            if (class_exists(\Modules\TitanChatbot\Services\GeneratorBridge::class)) {
                $bridge = app(\Modules\TitanChatbot\Services\GeneratorBridge::class);
                $text   = implode("\n", array_map(fn($m) => ($m['role'] ?? 'user') . ': ' . ($m['content'] ?? ''), $messages));
                $prompt = 'Summarize the following conversation in 2-3 sentences:\n\n' . $text;
                return $bridge->generate($prompt, []);
            }
        } catch (\Throwable $e) {
            Log::debug('SummarizationStrategy: could not summarize via generator.', ['error' => $e->getMessage()]);
        }

        $count = count($messages);
        $first = $messages[0]['content'] ?? '(start of conversation)';
        return "Earlier in this conversation ({$count} messages), the user started with: \"{$first}\"";
    }
}
