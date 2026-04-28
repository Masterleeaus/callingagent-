<?php

namespace Modules\TitanChatbot\AI\Memory;

use Illuminate\Support\Facades\Cache;
use Modules\TitanChatbot\Events\AI\MemorySaved;
use Modules\TitanChatbot\Events\AI\MemoryLoaded;
use Illuminate\Support\Facades\Event;

class ConversationMemoryStore
{
    private const PREFIX = 'titan_chatbot_memory:';
    private const TTL    = 3600;

    private ?StorageManager $manager = null;

    public function useDriver(string $driver): static
    {
        $this->manager = new StorageManager($driver);
        return $this;
    }

    public function remember(string $sessionId, string $role, string $content): void
    {
        if ($this->manager) {
            $this->manager->push($sessionId, $role, $content);
        } else {
            $key      = self::PREFIX . $sessionId;
            $messages = Cache::get($key, []);
            $messages[] = ['role' => $role, 'content' => $content];
            Cache::put($key, $messages, self::TTL);
        }

        try {
            if (class_exists(MemorySaved::class)) {
                Event::dispatch(new MemorySaved($sessionId, $role, $content));
            }
        } catch (\Throwable) {}
    }

    public function recall(string $sessionId, int $limit = 10): array
    {
        if ($this->manager) {
            $messages = $this->manager->all($sessionId, $limit);
        } else {
            $messages = Cache::get(self::PREFIX . $sessionId, []);
            if (count($messages) > $limit) {
                $messages = array_slice($messages, -$limit);
            }
        }

        try {
            if (class_exists(MemoryLoaded::class)) {
                Event::dispatch(new MemoryLoaded($sessionId, count($messages)));
            }
        } catch (\Throwable) {}

        return $messages;
    }

    public function forget(string $sessionId): void
    {
        if ($this->manager) {
            $this->manager->forget($sessionId);
        } else {
            Cache::forget(self::PREFIX . $sessionId);
        }
    }
}
