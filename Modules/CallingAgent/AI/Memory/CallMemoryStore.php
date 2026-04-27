<?php
namespace Modules\CallingAgent\AI\Memory;

final class CallMemoryStore
{
    private array $memory = [];
    public function put(string $callId, string $key, mixed $value): void { $this->memory[$callId][$key] = $value; }
    public function get(string $callId, ?string $key = null): mixed { return $key ? ($this->memory[$callId][$key] ?? null) : ($this->memory[$callId] ?? []); }
}
