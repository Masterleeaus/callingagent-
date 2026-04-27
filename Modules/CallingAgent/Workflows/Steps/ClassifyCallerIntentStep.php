<?php
namespace Modules\CallingAgent\Workflows\Steps;

use Modules\CallingAgent\AI\Tools\IntentClassifierTool;

final class ClassifyCallerIntentStep
{
    public function handle(array $context): array
    {
        $context['intent'] = (new IntentClassifierTool())->classify($context['utterance'] ?? '');
        return $context;
    }
}
