<?php
namespace Modules\CallingAgent\Workflows\Transitions;

use Modules\CallingAgent\ValueObjects\StructuredCallOutcome;
use Modules\CallingAgent\ValueObjects\TransferDecision;

final class TransferDecisionTree
{
    public function decide(StructuredCallOutcome|array $outcome, array $routing = [], array $caller = []): TransferDecision
    {
        $outcome = is_array($outcome) ? StructuredCallOutcome::fromArray($outcome) : $outcome;
        $routes = $routing['routes'] ?? [];

        if ($outcome->urgency === 'urgent') {
            return new TransferDecision('urgent_handoff', 'Urgent caller intent or emergency language detected.', 100, $routes['urgent'] ?? [], $routes['fallback'] ?? []);
        }

        if (($caller['vip'] ?? false) === true) {
            return new TransferDecision('vip', 'VIP caller routing policy matched.', 95, $routes['vip'] ?? [], $routes['fallback'] ?? []);
        }

        $route = match ($outcome->intent) {
            'booking' => 'booking_team',
            'sales' => 'sales_team',
            'support' => 'support_team',
            'retention' => 'retention_team',
            default => 'front_desk',
        };

        return new TransferDecision($route, "Matched intent: {$outcome->intent}", $outcome->handoffRequired ? 80 : 40, $routes[$route] ?? [], $routes['fallback'] ?? []);
    }
}
