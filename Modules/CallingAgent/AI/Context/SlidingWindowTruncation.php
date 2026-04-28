<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Context;

use Modules\CallingAgent\AI\Messages\SystemMessage;

final class SlidingWindowTruncation implements TruncationStrategy
{
    public function truncate(ChatHistoryInterface $history, int $limit, array $context = []): void
    {
        if ($history->count() <= $limit) {
            return;
        }

        $all = $history->getMessages();

        $systemMessages = array_filter($all, static fn ($m) => $m instanceof SystemMessage);
        $nonSystem = array_values(array_filter($all, static fn ($m) => ! ($m instanceof SystemMessage)));

        $keepCount = max(0, $limit - count($systemMessages));
        $kept = array_slice($nonSystem, -$keepCount);

        $history->clear();

        foreach (array_merge(array_values($systemMessages), $kept) as $msg) {
            $history->addMessage($msg);
        }
    }
}
