<?php

namespace Modules\TitanChatbot\Billing\Meters;

use Illuminate\Support\Facades\Cache;

class VoiceSecondsMeter
{
    public function record(float $seconds, array $context = []): void
    {
        $this->recordSeconds((int) ($context['tenant_id'] ?? 0), (int) ceil($seconds));
    }

    public function recordSeconds(int $tenantId, int $seconds): void
    {
        $key = $this->buildKey($tenantId);
        Cache::increment($key, $seconds);
    }

    public function getSeconds(int $tenantId, ?string $date = null): int
    {
        return (int) Cache::get($this->buildKey($tenantId, $date), 0);
    }

    public function reset(int $tenantId): void
    {
        Cache::forget($this->buildKey($tenantId));
    }

    private function buildKey(int $tenantId, ?string $date = null): string
    {
        $date = $date ?? now()->toDateString();

        return "billing_meter:voice_seconds:{$tenantId}:{$date}";
    }
}
