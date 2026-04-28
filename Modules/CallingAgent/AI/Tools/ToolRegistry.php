<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Tools;

use Modules\CallingAgent\AI\Tools\Attributes\Tool as ToolAttribute;
use ReflectionMethod;

final class ToolRegistry
{
    /** @var ToolDefinition[] */
    private array $tools = [];

    public function register(ToolDefinition $tool): self
    {
        $this->tools[$tool->name] = $tool;

        return $this;
    }

    public function registerFromObject(object $toolObject): self
    {
        $reflection = new \ReflectionClass($toolObject);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attrs = $method->getAttributes(ToolAttribute::class);

            if (! empty($attrs)) {
                $this->register(ToolDefinition::fromMethod($toolObject, $method->getName()));
            }
        }

        return $this;
    }

    public function get(string $name): ?ToolDefinition
    {
        return $this->tools[$name] ?? null;
    }

    /** @return ToolDefinition[] */
    public function all(): array
    {
        return array_values($this->tools);
    }

    public function toOpenAiArray(): array
    {
        return array_values(array_map(
            static fn (ToolDefinition $tool) => $tool->toOpenAiArray(),
            $this->tools,
        ));
    }
}
