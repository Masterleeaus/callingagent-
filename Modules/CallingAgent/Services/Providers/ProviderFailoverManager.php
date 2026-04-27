<?php
namespace Modules\CallingAgent\Services\Providers;

final class ProviderFailoverManager
{
    public function choose(array $providers, array $health = [], ?string $preferred = null): ?string
    {
        $ordered = $preferred ? array_values(array_unique([$preferred, ...$providers])) : $providers;
        foreach ($ordered as $provider) {
            if (($health[$provider]['ok'] ?? true) === true && ($health[$provider]['quota_available'] ?? true) === true) {
                return $provider;
            }
        }
        return $ordered[0] ?? null;
    }

    public function plan(array $layers, array $health): array
    {
        $selection = [];
        foreach ($layers as $layer => $providers) {
            $selection[$layer] = $this->choose($providers, $health[$layer] ?? []);
        }
        return $selection;
    }
}
