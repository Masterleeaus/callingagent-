<?php

namespace Modules\CallingAgent\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CallingAgent\AI\Pipelines\OutcomeExtractionPipeline;
use Modules\CallingAgent\Models\CallingAgentCall;
use Modules\CallingAgent\Models\CallingAgentCallOutcome;
use Modules\CallingAgent\Models\CallingAgentTranscript;

class SummarizeCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public readonly string $callSid) {}

    public function handle(): void
    {
        $call = CallingAgentCall::where('call_sid', $this->callSid)->first();

        if (!$call) {
            return;
        }

        $transcripts = CallingAgentTranscript::where('calling_agent_call_id', $call->id)
            ->orderBy('id')
            ->pluck('text')
            ->toArray();

        if (empty($transcripts)) {
            return;
        }

        $pipeline = new OutcomeExtractionPipeline();
        $outcome  = $pipeline->extract($transcripts);

        CallingAgentCallOutcome::updateOrCreate(
            ['call_sid' => $this->callSid],
            array_merge($outcome->toArray(), ['raw' => $outcome->toArray()])
        );

        // Update call summary on parent record
        $call->update(['summary' => $outcome->summary]);
    }
}
