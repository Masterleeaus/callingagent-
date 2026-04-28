<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Context;

use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Messages\SystemMessage;
use Modules\CallingAgent\AI\Messages\ToolResultMessage;
use Modules\CallingAgent\AI\Messages\UserMessage;

final class ArrayChatHistory implements ChatHistoryInterface
{
    /** @var array<SystemMessage|UserMessage|AssistantMessage|ToolResultMessage> */
    private array $messages = [];

    public function addMessage(SystemMessage|UserMessage|AssistantMessage|ToolResultMessage $msg): void
    {
        $this->messages[] = $msg;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function clear(): void
    {
        $this->messages = [];
    }

    public function truncate(int $limit): void
    {
        if ($this->count() <= $limit) {
            return;
        }

        // Preserve system messages at the start
        $systemMessages = [];
        $nonSystemMessages = [];

        foreach ($this->messages as $msg) {
            if ($msg instanceof SystemMessage) {
                $systemMessages[] = $msg;
            } else {
                $nonSystemMessages[] = $msg;
            }
        }

        $keepCount = max(0, $limit - count($systemMessages));
        $trimmed = array_slice($nonSystemMessages, -$keepCount);

        $this->messages = array_merge($systemMessages, $trimmed);
    }

    public function toMessageCollection(): MessageCollection
    {
        $collection = MessageCollection::make();

        foreach ($this->messages as $msg) {
            $collection = $collection->add($msg);
        }

        return $collection;
    }
}
