<?php
namespace Modules\CallingAgent\Services;

use Modules\CallingAgent\ValueObjects\StructuredCallOutcome;
use Modules\CallingAgent\Workflows\Transitions\TransferDecisionTree;

final class TransferRoutingService
{
    public function __construct(private readonly TransferDecisionTree $tree = new TransferDecisionTree()) {}

    public function route(StructuredCallOutcome|array $outcome, array $routing = [], array $caller = []): array
    {
        return $this->tree->decide($outcome, $routing, $caller)->toArray();
    }
}
