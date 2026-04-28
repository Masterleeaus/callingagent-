<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Context;

use Modules\CallingAgent\AI\Messages\SystemMessage;
use Modules\CallingAgent\AI\Tools\CallSummarizerTool;

final class SummarizationTruncation implements TruncationStrategy
{
    public function truncate(ChatHistoryInterface $history, int $limit, array $context = []): void
    {
        if ($history->count() <= $limit) {
            return;
        }

        try {
            $all = $history->getMessages();

            $systemMessages = array_values(array_filter($all, static fn ($m) => $m instanceof SystemMessage));
            $nonSystem = array_values(array_filter($all, static fn ($m) => ! ($m instanceof SystemMessage)));

            $keepCount = max(1, (int) floor($limit / 2));
            $toSummarize = array_slice($nonSystem, 0, -$keepCount);
            $toKeep = array_slice($nonSystem, -$keepCount);

            if (empty($toSummarize)) {
                (new SlidingWindowTruncation())->truncate($history, $limit, $context);
                return;
            }

            $turns = array_map(static fn ($m) => $m->toArray(), $toSummarize);
            $tool = new CallSummarizerTool();
            $result = $tool->summarize($turns);

            $summaryText = 'Summary of earlier conversation: ' . ($result['summary'] ?? '');

            $history->clear();

            foreach ($systemMessages as $msg) {
                $history->addMessage($msg);
            }

            $history->addMessage(new SystemMessage($summaryText));

            foreach ($toKeep as $msg) {
                $history->addMessage($msg);
            }
        } catch (\Throwable) {
            (new SlidingWindowTruncation())->truncate($history, $limit, $context);
        }
    }
}
