<?php

namespace Modules\TitanChatbot\AI\Tools;

use Modules\TitanChatbot\AI\Attributes\Tool;
use Modules\TitanChatbot\AI\Attributes\Desc;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class SchemaGenerator
{
    /**
     * Generate OpenAI-compatible tool schemas from #[Tool]-annotated methods on an agent.
     *
     * @return array<int, array{type: 'function', function: array}>
     */
    public function generateFromAgent(object $agent): array
    {
        $schemas = [];
        $ref     = new ReflectionClass($agent);

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attrs = $method->getAttributes(Tool::class);
            if (empty($attrs)) {
                continue;
            }

            /** @var Tool $toolAttr */
            $toolAttr = $attrs[0]->newInstance();

            $name        = $toolAttr->name ?: $method->getName();
            $description = $toolAttr->description ?: $method->getName();
            $properties  = [];
            $required    = [];

            foreach ($method->getParameters() as $param) {
                $paramSchema = $this->paramToSchema($param);
                $properties[$param->getName()] = $paramSchema;

                $descAttrs = $param->getAttributes(Desc::class);
                if (!empty($descAttrs)) {
                    /** @var Desc $descAttr */
                    $descAttr = $descAttrs[0]->newInstance();
                    $properties[$param->getName()]['description'] = $descAttr->description;
                    if ($descAttr->required) {
                        $required[] = $param->getName();
                    }
                } elseif (!$param->isOptional()) {
                    $required[] = $param->getName();
                }
            }

            $schemas[] = [
                'type'     => 'function',
                'function' => [
                    'name'        => $name,
                    'description' => $description,
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => $properties,
                        'required'   => $required,
                    ],
                ],
            ];
        }

        return $schemas;
    }

    private function paramToSchema(ReflectionParameter $param): array
    {
        $type = $param->getType();
        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
            $jsonType = match ($typeName) {
                'int', 'float' => 'number',
                'bool'         => 'boolean',
                'array'        => 'array',
                default        => 'string',
            };
        } else {
            $jsonType = 'string';
        }

        return ['type' => $jsonType];
    }
}
