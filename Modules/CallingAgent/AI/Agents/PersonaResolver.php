<?php
namespace Modules\CallingAgent\AI\Agents;

final class PersonaResolver
{
    public function resolve(array $agent, array $caller = [], array $context = []): array
    {
        $industry = strtolower($agent['industry'] ?? $context['industry'] ?? 'front_desk');
        $library = require __DIR__ . '/../Prompts/reception_personas.php';
        $persona = $library[$industry] ?? $library['front_desk'];

        if (!empty($caller['name'])) {
            $persona['opening'] = "Welcome back {$caller['name']}. " . $persona['opening'];
        }

        return $persona + ['industry' => $industry];
    }
}
