<?php
namespace Modules\CallingAgent\Automation\Pipelines;

final class MissedCallRecoveryPipeline
{
    public function build(array $call, array $policy = []): array
    {
        $phone = $call['from'] ?? $call['caller_phone'] ?? null;
        $steps = [];
        if ($phone && ($policy['sms_enabled'] ?? true)) {
            $steps[] = ['type' => 'sms', 'to' => $phone, 'template' => $policy['sms_template'] ?? 'Sorry we missed your call. Reply here or book a callback.'];
        }
        if ($policy['callback_enabled'] ?? true) {
            $steps[] = ['type' => 'callback', 'delay_minutes' => $policy['callback_delay_minutes'] ?? 15, 'priority' => $call['priority'] ?? 'normal'];
        }
        if (!empty($call['voicemail_url'])) {
            $steps[] = ['type' => 'voicemail_summary', 'url' => $call['voicemail_url']];
        }
        return ['call' => $call, 'steps' => $steps, 'created_at' => date(DATE_ATOM)];
    }
}
