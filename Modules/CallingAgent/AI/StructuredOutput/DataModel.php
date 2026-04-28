<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\StructuredOutput;

abstract class DataModel
{
    abstract public static function schema(): array;

    abstract public static function fromArray(array $data): static;

    abstract public function toArray(): array;

    public static function validate(array $data): bool
    {
        $schema = static::schema();
        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];

        foreach ($required as $field) {
            if (! array_key_exists($field, $data)) {
                return false;
            }
        }

        foreach ($properties as $field => $def) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $type = $def['type'] ?? null;
            $value = $data[$field];

            $valid = match ($type) {
                'string' => is_string($value),
                'integer', 'int' => is_int($value),
                'number' => is_int($value) || is_float($value),
                'boolean', 'bool' => is_bool($value),
                'array' => is_array($value),
                'object' => is_array($value) || is_object($value),
                default => true,
            };

            if (! $valid) {
                return false;
            }

            if (isset($def['enum']) && is_array($def['enum']) && ! in_array($value, $def['enum'], true)) {
                return false;
            }
        }

        return true;
    }

    public static function jsonSchema(): array
    {
        $schema = static::schema();

        return [
            'type' => 'object',
            'properties' => $schema['properties'] ?? [],
            'required' => $schema['required'] ?? [],
        ];
    }
}
