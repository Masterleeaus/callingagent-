<?php
namespace Modules\CallingAgent\Workflows\Definitions;

final class ReceptionistCallWorkflow
{
    public function definition(): array
    {
        return [
            'start' => 'answer',
            'steps' => ['answer', 'identify_intent', 'resolve_or_route', 'summarize', 'aftercare'],
            'transitions' => [
                'answer' => 'identify_intent',
                'identify_intent' => 'resolve_or_route',
                'resolve_or_route' => 'summarize',
                'summarize' => 'aftercare',
            ],
        ];
    }
}
