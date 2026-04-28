<?php
namespace Modules\TitanChatbot\AI\Memory;

class StorageManager
{
    private MemoryDriverInterface $driver;

    public function __construct(string $driver = 'cache')
    {
        $this->driver = $this->makeDriver($driver);
    }

    public function driver(string $name): static
    {
        $clone = clone $this;
        $clone->driver = $this->makeDriver($name);
        return $clone;
    }

    public function get(string $sessionId): array
    {
        return $this->driver->get($sessionId);
    }

    public function push(string $sessionId, string $role, string $content): void
    {
        $this->driver->push($sessionId, $role, $content);
    }

    public function forget(string $sessionId): void
    {
        $this->driver->forget($sessionId);
    }

    public function all(string $sessionId, int $limit = 0): array
    {
        $messages = $this->driver->get($sessionId);
        if ($limit > 0 && count($messages) > $limit) {
            $messages = array_slice($messages, -$limit);
        }
        return $messages;
    }

    private function makeDriver(string $name): MemoryDriverInterface
    {
        return match ($name) {
            'in_memory' => new Drivers\InMemoryMemoryDriver(),
            'file'      => new Drivers\FileMemoryDriver(),
            'database'  => new Drivers\DatabaseMemoryDriver(),
            default     => new Drivers\CacheMemoryDriver(),
        };
    }
}
