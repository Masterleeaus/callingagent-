<?php

namespace Modules\TitanChatbot\AI\Tools;

use Modules\TitanChatbot\AI\Attributes\Tool;
use ReflectionClass;

class ToolRegistry
{
    /** @var array<string, array> */
    private array $tools = [];

    public function register(string $name, array $schema): void
    {
        $this->tools[$name] = $schema;
    }

    public function all(): array
    {
        return array_values($this->tools);
    }

    public function get(string $name): ?array
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Discover tool methods on an agent (methods annotated with #[Tool]).
     */
    public function discoverFromAgent(object $agent): array
    {
        return app(SchemaGenerator::class)->generateFromAgent($agent);
    }

    public function clear(): void
    {
        $this->tools = [];
    }
}
