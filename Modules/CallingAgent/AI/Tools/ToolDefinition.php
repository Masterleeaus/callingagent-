<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Tools;

use Modules\CallingAgent\AI\Tools\Attributes\Tool as ToolAttribute;
use ReflectionMethod;

final readonly class ToolDefinition
{
    public function __construct(
        public string $name,
        public string $description,
        public array $parameters = [],
        public array $required = [],
        /** @var callable|null */
        public mixed $callable = null,
    ) {}

    public function toOpenAiArray(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name,
                'description' => $this->description,
                'parameters' => [
                    'type' => 'object',
                    'properties' => $this->parameters,
                    'required' => $this->required,
                ],
            ],
        ];
    }

    public static function fromMethod(object $instance, string $method): self
    {
        $reflection = new ReflectionMethod($instance, $method);

        $attrs = $reflection->getAttributes(ToolAttribute::class);

        $name = $method;
        $description = '';

        if (! empty($attrs)) {
            /** @var ToolAttribute $attr */
            $attr = $attrs[0]->newInstance();
            $name = $attr->name !== '' ? $attr->name : $method;
            $description = $attr->description;
        }

        // Parse parameter info from docblock if description is empty
        if ($description === '') {
            $doc = $reflection->getDocComment();
            if ($doc !== false) {
                if (preg_match('/@description\s+(.+)/m', $doc, $m)) {
                    $description = trim($m[1]);
                } elseif (preg_match('/\*\s+([^@\n][^\n]+)/m', $doc, $m)) {
                    $description = trim($m[1]);
                }
            }
        }

        $parameters = [];
        $required = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $typeName = $type !== null ? (string) $type : 'string';

            $jsonType = match ($typeName) {
                'int', 'float' => 'number',
                'bool' => 'boolean',
                'array' => 'array',
                default => 'string',
            };

            $parameters[$param->getName()] = ['type' => $jsonType];

            if (! $param->isOptional()) {
                $required[] = $param->getName();
            }
        }

        return new self(
            name: $name,
            description: $description,
            parameters: $parameters,
            required: $required,
            callable: [$instance, $method],
        );
    }
}
