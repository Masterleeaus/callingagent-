<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Context;

interface TruncationStrategy
{
    public function truncate(ChatHistoryInterface $history, int $limit, array $context = []): void;
}
