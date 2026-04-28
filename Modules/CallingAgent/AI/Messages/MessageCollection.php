<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Messages;

final class MessageCollection
{
    private array $messages = [];

    private function __construct(array $messages = [])
    {
        foreach ($messages as $msg) {
            $this->messages[] = $msg;
        }
    }

    public static function make(array $messages = []): self
    {
        return new self($messages);
    }

    public function add(SystemMessage|UserMessage|AssistantMessage|ToolResultMessage $msg): self
    {
        $clone = clone $this;
        $clone->messages[] = $msg;

        return $clone;
    }

    public function addSystem(string $content): self
    {
        return $this->add(new SystemMessage($content));
    }

    public function addUser(string|array $content): self
    {
        return $this->add(new UserMessage($content));
    }

    public function addAssistant(string $content, array $toolCalls = []): self
    {
        return $this->add(new AssistantMessage($content, $toolCalls));
    }

    public function addToolResult(string $toolCallId, string $content): self
    {
        return $this->add(new ToolResultMessage($toolCallId, $content));
    }

    public function all(): array
    {
        return $this->messages;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function toOpenAiArray(): array
    {
        return array_map(static fn ($msg) => $msg->toArray(), $this->messages);
    }

    public function last(): SystemMessage|UserMessage|AssistantMessage|ToolResultMessage|null
    {
        if (empty($this->messages)) {
            return null;
        }

        return end($this->messages) ?: null;
    }
}
