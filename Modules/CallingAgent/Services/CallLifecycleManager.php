<?php
namespace Modules\CallingAgent\Services;

use Modules\CallingAgent\ValueObjects\CallOutcome;

final class CallLifecycleManager
{
    public function started(array $payload): array { return $this->event('started', $payload); }
    public function missed(array $payload): array { return $this->event('missed', $payload + ['action' => 'send_sms_fallback']); }
    public function voicemail(array $payload): array { return $this->event('voicemail', $payload + ['action' => 'transcribe_and_summarize']); }
    public function completed(array $payload, ?CallOutcome $outcome = null): array { return $this->event('completed', $payload + ['outcome' => $outcome?->toArray()]); }
    private function event(string $type, array $payload): array { return ['type' => $type, 'payload' => $payload, 'at' => date(DATE_ATOM)]; }
}
