<?php
namespace Modules\CallingAgent\Workflows\Steps;

use Modules\CallingAgent\AI\Tools\CallSummarizerTool;

final class SummarizeCallStep
{
    public function handle(array $context): array
    {
        $context['summary'] = (new CallSummarizerTool())->summarize($context['turns'] ?? []);
        return $context;
    }
}
