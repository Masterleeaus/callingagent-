<?php

namespace Modules\TitanChatbot\Billing\Meters;

use Illuminate\Support\Facades\Cache;

class EmbeddingMeter
{
    public function recordTokens(int $tenantId, int $tokens): void
    {
        $key = $this->buildKey($tenantId);
        Cache::increment($key, $tokens);
    }

    /**
     * Alias accepted by automation listeners: record(int $tokens, array $context).
     */
    public function record(int $tokens, array $context = []): void
    {
        $this->recordTokens((int) ($context['tenant_id'] ?? 0), $tokens);
    }

    public function getTokenCount(int $tenantId, ?string $date = null): int
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

        return "billing_meter:embeddings:{$tenantId}:{$date}";
    }
}
