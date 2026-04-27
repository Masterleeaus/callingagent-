<?php

namespace Modules\TitanChatbot\Billing\Meters;

use Illuminate\Support\Facades\Cache;

class ConversationMeter
{
    public function record(int $chatbotId, int $tenantId = 0): void
    {
        $key = $this->buildKey($tenantId);
        Cache::increment($key);
    }

    public function getCount(int $tenantId, ?string $date = null): int
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

        return "billing_meter:conversations:{$tenantId}:{$date}";
    }
}
