<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Context;

use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Messages\SystemMessage;
use Modules\CallingAgent\AI\Messages\ToolResultMessage;
use Modules\CallingAgent\AI\Messages\UserMessage;

interface ChatHistoryInterface
{
    public function addMessage(SystemMessage|UserMessage|AssistantMessage|ToolResultMessage $msg): void;

    /** @return array<SystemMessage|UserMessage|AssistantMessage|ToolResultMessage> */
    public function getMessages(): array;

    public function count(): int;

    public function clear(): void;

    public function truncate(int $limit): void;

    public function toMessageCollection(): MessageCollection;
}
