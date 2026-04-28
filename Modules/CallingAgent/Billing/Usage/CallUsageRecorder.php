<?php

namespace Modules\CallingAgent\Billing\Usage;

use Modules\CallingAgent\Billing\Meters\VoiceSecondsMeter;
use Modules\CallingAgent\Billing\Meters\MinuteRoundingPolicy;
use Modules\CallingAgent\Models\CallingAgentCall;

/**
 * Records billable usage for a completed call, idempotently.
 *
 * Should be called exactly once per call (from the status callback).
 * Safe to call multiple times for the same call_sid — subsequent calls are
 * no-ops because VoiceSecondsMeter uses insertOrIgnore with an idempotency key.
 */
final class CallUsageRecorder
{
    public function __construct(
        private VoiceSecondsMeter $meter,
        private MinuteRoundingPolicy $rounding,
    ) {}

    /**
     * Record all billable usage for a completed call.
     *
     * @param  CallingAgentCall  $call  The completed call model.
     * @param  int|null  $tenantId      Tenant/team ID for multi-tenanted apps.
     */
    public function record(CallingAgentCall $call, ?int $tenantId = null): void
    {
        if (!$call->call_sid) {
            return;
        }

        $rawSeconds      = max(0, (int) $call->duration);
        $billableSeconds = $this->rounding->billableSeconds($rawSeconds);

        $this->meter->record(
            callSid:   $call->call_sid,
            rawSeconds: $billableSeconds,
            agentId:   $call->calling_agent_id ?? null,
            tenantId:  $tenantId,
        );
    }
}
