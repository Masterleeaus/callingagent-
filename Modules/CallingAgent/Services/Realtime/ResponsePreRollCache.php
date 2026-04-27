<?php
namespace Modules\CallingAgent\Services\Realtime;

final class ResponsePreRollCache
{
    private array $cache = [];

    public function put(string $key, string $audioOrText): void { $this->cache[$key] = ['value' => $audioOrText, 'at' => microtime(true)]; }
    public function get(string $key): ?string { return $this->cache[$key]['value'] ?? null; }

    public function warm(array $phrases): void
    {
        foreach ($phrases as $key => $phrase) $this->put((string)$key, (string)$phrase);
    }
}
