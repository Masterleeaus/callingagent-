<?php
namespace Modules\CallingAgent\Services;

use Illuminate\Support\Facades\DB;
use Modules\CallingAgent\AI\Agents\ReceptionistAgent;
use Modules\CallingAgent\AI\Pipelines\OutcomeExtractionPipeline;
use Modules\CallingAgent\Jobs\SummarizeCallJob;
use Modules\CallingAgent\Models\CallingAgent;
use Modules\CallingAgent\Models\CallingAgentActiveCall;
use Modules\CallingAgent\Models\CallingAgentCall;
use Modules\CallingAgent\Models\CallingAgentCallerProfile;
use Modules\CallingAgent\Models\CallingAgentCallOutcome;
use Modules\CallingAgent\Models\CallingAgentPhoneNumber;
use Modules\CallingAgent\Models\CallingAgentTranscript;

class ReceptionistOrchestrator
{
    public function __construct(public ReceptionistAgent $agent) {}

    // -------------------------------------------------------------------------
    // Agent resolution
    // -------------------------------------------------------------------------

    public function resolveByNumber(?string $to): ?CallingAgent
    {
        if (!$to) {
            return null;
        }
        $pn = CallingAgentPhoneNumber::where('number', $to)->first();
        return $pn?->calling_agent_id
            ? CallingAgent::find($pn->calling_agent_id)
            : CallingAgent::where('phone_number', $to)->first();
    }

    // -------------------------------------------------------------------------
    // Call lifecycle
    // -------------------------------------------------------------------------

    public function startInbound(array $payload): CallingAgentCall
    {
        $callSid = $payload['CallSid'] ?? null;

        return CallingAgentCall::updateOrCreate(
            ['call_sid' => $callSid],
            [
                'provider'   => 'twilio',
                'direction'  => 'inbound',
                'from'       => $payload['From'] ?? null,
                'to'         => $payload['To'] ?? null,
                'status'     => $payload['CallStatus'] ?? 'ringing',
                'started_at' => now(),
                'metadata'   => $payload,
            ]
        );
    }

    public function touchActive(CallingAgentCall $call, array $payload): void
    {
        CallingAgentActiveCall::updateOrCreate(
            ['call_sid' => $call->call_sid],
            [
                'calling_agent_call_id' => $call->id,
                'from'                  => $call->from,
                'to'                    => $call->to,
                'state'                 => $payload['CallStatus'] ?? 'ringing',
                'last_seen_at'          => now(),
                'context'               => $payload,
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Speech/Gather turn
    // -------------------------------------------------------------------------

    public function answer(CallingAgentCall $call, string $speech, array $context = []): string
    {
        // Persist caller turn
        CallingAgentTranscript::create([
            'calling_agent_call_id' => $call->id,
            'role'                  => 'user',
            'text'                  => $speech,
            'source'                => 'twilio-speech',
        ]);

        // Enrich context with caller profile memory
        $profile = $this->recallCallerProfile($call->from);
        if ($profile) {
            $context['caller_profile'] = $profile->toArray();
        }

        $reply = $this->agent->respond($speech, $context);

        // Persist assistant turn
        CallingAgentTranscript::create([
            'calling_agent_call_id' => $call->id,
            'role'                  => 'assistant',
            'text'                  => $reply,
            'source'                => 'ai',
        ]);

        return $reply;
    }

    // -------------------------------------------------------------------------
    // Call completion + outcome
    // -------------------------------------------------------------------------

    public function complete(string $callSid, array $payload): void
    {
        $call = CallingAgentCall::where('call_sid', $callSid)->first();

        if ($call) {
            $call->update([
                'status'   => $payload['CallStatus'] ?? 'completed',
                'duration' => (int) ($payload['CallDuration'] ?? $call->duration),
                'ended_at' => now(),
                'metadata' => array_merge($call->metadata ?? [], $payload),
            ]);

            // Extract outcome from transcript
            $this->extractAndPersistOutcome($call);

            // Update caller profile
            $this->updateCallerProfile($call);

            // Schedule missed-call recovery if unanswered
            $status = $payload['CallStatus'] ?? '';
            if (in_array($status, ['no-answer', 'busy', 'failed'], true)) {
                $this->scheduleMissedCallRecovery($call);
            }
        }

        CallingAgentActiveCall::where('call_sid', $callSid)->delete();
    }

    // -------------------------------------------------------------------------
    // Idempotency guard for webhook events
    // -------------------------------------------------------------------------

    /**
     * Returns true if the event was already processed (duplicate / replay).
     * Marks the event as processed on first call.
     */
    public function isDuplicate(string $eventId, string $source = 'twilio'): bool
    {
        try {
            $inserted = DB::table('calling_agent_webhook_idempotency')->insertOrIgnore([
                'event_id'     => $eventId,
                'source'       => $source,
                'processed_at' => now(),
                'payload_hash' => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            // insertOrIgnore returns affected rows: 0 = already existed (duplicate)
            return $inserted === 0;
        } catch (\Throwable $e) {
            // Table absent (pre-migration): treat as non-duplicate, log error
            report($e);
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Caller profile memory
    // -------------------------------------------------------------------------

    public function recallCallerProfile(?string $phone): ?CallingAgentCallerProfile
    {
        if (!$phone) {
            return null;
        }
        return CallingAgentCallerProfile::where('phone', $phone)->first();
    }

    private function updateCallerProfile(CallingAgentCall $call): void
    {
        if (!$call->from) {
            return;
        }

        try {
            CallingAgentCallerProfile::updateOrCreate(
                ['phone' => $call->from],
                [
                    'last_call_at' => now(),
                    'call_count'   => DB::raw('COALESCE(call_count, 0) + 1'),
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    // -------------------------------------------------------------------------
    // Outcome extraction
    // -------------------------------------------------------------------------

    private function extractAndPersistOutcome(CallingAgentCall $call): void
    {
        try {
            $transcripts = CallingAgentTranscript::where('calling_agent_call_id', $call->id)
                ->orderBy('id')
                ->pluck('text')
                ->toArray();

            if (empty($transcripts)) {
                return;
            }

            $pipeline = new OutcomeExtractionPipeline();
            $outcome = $pipeline->extract($transcripts);

            CallingAgentCallOutcome::updateOrCreate(
                ['call_sid' => $call->call_sid],
                array_merge($outcome->toArray(), ['raw' => $outcome->toArray()])
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    // -------------------------------------------------------------------------
    // Missed-call recovery
    // -------------------------------------------------------------------------

    private function isMissingTableException(\Throwable $e): bool
    {
        if ($e instanceof \Illuminate\Database\QueryException) {
            // MySQL: 1146, SQLite: general "no such table", PostgreSQL: 42P01
            $code = (string) $e->getCode();
            if (in_array($code, ['1146', '42P01', 'HY000'], true)) {
                return true;
            }
        }
        $msg = strtolower($e->getMessage());
        return str_contains($msg, "doesn't exist")
            || str_contains($msg, 'no such table')
            || str_contains($msg, 'does not exist');
    }

    private function scheduleMissedCallRecovery(CallingAgentCall $call): void
    {
        try {
            DB::table('calling_agent_missed_call_recovery_tasks')->insertOrIgnore([
                'calling_agent_call_id' => $call->id,
                'call_sid'              => $call->call_sid,
                'phone'                 => $call->from,
                'channel'               => 'sms',
                'status'                => 'pending',
                'attempts'              => 0,
                'scheduled_at'          => now()->addMinutes(5),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        } catch (\Throwable $e) {
            if (!$this->isMissingTableException($e)) {
                report($e);
            }
        }
    }
}

