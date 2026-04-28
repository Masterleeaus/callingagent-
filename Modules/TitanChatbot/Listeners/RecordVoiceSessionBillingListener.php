<?php

namespace Modules\TitanChatbot\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter;
use Modules\TitanChatbot\Events\VoiceSessionDurationRecorded;

class RecordVoiceSessionBillingListener
{
    public function __construct(private readonly VoiceSecondsMeter $meter) {}

    public function handle(VoiceSessionDurationRecorded $event): void
    {
        $this->meter->record($event->durationSeconds, [
            'session_id'    => $event->sessionId,
            'message_count' => $event->messageCount,
        ]);

        $logContext = [
            'session_id'       => $event->sessionId,
            'duration_seconds' => $event->durationSeconds,
            'message_count'    => $event->messageCount,
        ];
        try {
            Log::channel('titan_chatbot')->info('RecordVoiceSessionBillingListener: recorded voice seconds', $logContext);
        } catch (\Throwable $e) {
            Log::info('RecordVoiceSessionBillingListener: recorded voice seconds', $logContext);
        }
    }
}
