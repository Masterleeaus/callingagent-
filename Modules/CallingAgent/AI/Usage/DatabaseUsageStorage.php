<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Usage;

final class DatabaseUsageStorage implements UsageStorageInterface
{
    private string $table = 'calling_agent_usage_records';

    public function store(
        UsageRecord $record,
        ?string $callSid = null,
        ?int $agentId = null,
        ?int $tenantId = null,
    ): void {
        if (! class_exists(\Illuminate\Support\Facades\DB::class)) {
            return;
        }

        try {
            $idempotencyKey = md5(
                $record->provider . $record->model . ($callSid ?? '') . (string) microtime(true),
            );

            \Illuminate\Support\Facades\DB::table($this->table)->insertOrIgnore([
                'idempotency_key' => $idempotencyKey,
                'provider' => $record->provider,
                'model' => $record->model,
                'prompt_tokens' => $record->promptTokens,
                'completion_tokens' => $record->completionTokens,
                'total_tokens' => $record->totalTokens,
                'cost_usd' => $record->costUsd,
                'call_sid' => $callSid,
                'agent_id' => $agentId,
                'tenant_id' => $tenantId,
                'metadata' => $record->metadata ? json_encode($record->metadata) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently skip if table is absent or DB is unavailable
        }
    }

    public function totalsFor(string $provider, string $model, ?string $since = null): array
    {
        if (! class_exists(\Illuminate\Support\Facades\DB::class)) {
            return [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'cost_usd' => 0.0,
                'record_count' => 0,
            ];
        }

        try {
            $query = \Illuminate\Support\Facades\DB::table($this->table)
                ->where('provider', $provider)
                ->where('model', $model);

            if ($since !== null) {
                $query->where('created_at', '>=', $since);
            }

            $result = $query->selectRaw(
                'SUM(prompt_tokens) as prompt_tokens, SUM(completion_tokens) as completion_tokens, SUM(total_tokens) as total_tokens, SUM(cost_usd) as cost_usd, COUNT(*) as record_count',
            )->first();

            return [
                'prompt_tokens' => (int) ($result->prompt_tokens ?? 0),
                'completion_tokens' => (int) ($result->completion_tokens ?? 0),
                'total_tokens' => (int) ($result->total_tokens ?? 0),
                'cost_usd' => (float) ($result->cost_usd ?? 0.0),
                'record_count' => (int) ($result->record_count ?? 0),
            ];
        } catch (\Throwable) {
            return [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'cost_usd' => 0.0,
                'record_count' => 0,
            ];
        }
    }
}
