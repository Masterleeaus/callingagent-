<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Tools;

use Modules\CallingAgent\AI\Messages\ToolResultMessage;
use Modules\CallingAgent\Events\AI\AfterToolExecution;
use Modules\CallingAgent\Events\AI\BeforeToolExecution;

final class ToolExecutor
{
    public function __construct(private ToolRegistry $registry) {}

    public function execute(string $toolName, array $arguments): mixed
    {
        $tool = $this->registry->get($toolName);

        if ($tool === null) {
            throw new \RuntimeException("Tool '{$toolName}' not found in registry.");
        }

        if (class_exists(\Illuminate\Support\Facades\Event::class)) {
            \Illuminate\Support\Facades\Event::dispatch(
                new BeforeToolExecution($tool, $arguments),
            );
        }

        $result = ($tool->callable)(...array_values($arguments));

        if (class_exists(\Illuminate\Support\Facades\Event::class)) {
            \Illuminate\Support\Facades\Event::dispatch(
                new AfterToolExecution($tool, $arguments, $result),
            );
        }

        return $result;
    }

    public function executeFromToolCall(array $toolCall): ToolResultMessage
    {
        $id = $toolCall['id'] ?? '';
        $name = $toolCall['function']['name'] ?? '';
        $rawArgs = $toolCall['function']['arguments'] ?? '{}';

        $arguments = is_array($rawArgs) ? $rawArgs : (json_decode($rawArgs, true) ?? []);

        $result = $this->execute($name, $arguments);

        $content = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE);

        return new ToolResultMessage($id, $content ?? '');
    }
}
