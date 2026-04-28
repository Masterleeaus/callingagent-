<?php

namespace Modules\CallingAgent\Billing\Meters;

use Illuminate\Support\Facades\DB;

/**
 * Records voice-second usage to calling_agent_usage_records.
 * Uses an idempotency key (call_sid) to ensure each call is billed once only.
 */
final class VoiceSecondsMeter
{
    /**
     * Record billable seconds for a completed call.
     * If a record for this call_sid + meter already exists, does nothing (idempotent).
     *
     * @param  string    $callSid     Unique call identifier used as idempotency key.
     * @param  int       $rawSeconds  Actual call duration in seconds.
     * @param  int|null  $agentId     CallingAgent ID (nullable when unknown).
     * @param  int|null  $tenantId    Tenant/team ID (nullable for non-tenanted apps).
     */
    public function record(
        string $callSid,
        int $rawSeconds,
        ?int $agentId = null,
        ?int $tenantId = null
    ): void {
        if ($rawSeconds <= 0) {
            return;
        }

        try {
            DB::table('calling_agent_usage_records')->insertOrIgnore([
                'meter'              => 'voice_seconds',
                'idempotency_key'    => 'voice:' . $callSid,
                'calling_agent_id'   => $agentId,
                'tenant_id'          => $tenantId,
                'quantity'           => $rawSeconds,
                'unit'               => 'second',
                'metadata'           => json_encode(['call_sid' => $callSid]),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // Table may not yet be migrated — log but don't crash
            report($e);
        }
    }
}
