<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Usage;

interface UsageStorageInterface
{
    public function store(
        UsageRecord $record,
        ?string $callSid = null,
        ?int $agentId = null,
        ?int $tenantId = null,
    ): void;

    public function totalsFor(string $provider, string $model, ?string $since = null): array;
}
