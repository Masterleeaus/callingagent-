<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ConversationSessionManager
{
    private const PREFIX = 'titan_chatbot_session:';
    private const DEFAULT_TTL = 3600;

    public function getSession(string $sessionId, int|string $chatbotId): ?array
    {
        return Cache::get($this->cacheKey($sessionId, $chatbotId));
    }

    public function createSession(string $sessionId, int|string $chatbotId, array $attributes = []): array
    {
        $session = array_merge([
            'session_id'  => $sessionId,
            'chatbot_id'  => $chatbotId,
            'token'       => Str::uuid()->toString(),
            'started_at'  => now()->toIso8601String(),
            'last_seen_at'=> now()->toIso8601String(),
        ], $attributes);

        Cache::put($this->cacheKey($sessionId, $chatbotId), $session, self::DEFAULT_TTL);

        return $session;
    }

    public function findOrCreate(string $sessionId, int|string $chatbotId, array $attributes = []): array
    {
        return $this->getSession($sessionId, $chatbotId)
            ?? $this->createSession($sessionId, $chatbotId, $attributes);
    }

    public function touchSession(string $sessionId, int|string $chatbotId): void
    {
        $session = $this->getSession($sessionId, $chatbotId);
        if ($session) {
            $session['last_seen_at'] = now()->toIso8601String();
            Cache::put($this->cacheKey($sessionId, $chatbotId), $session, self::DEFAULT_TTL);
        }
    }

    public function endSession(string $sessionId, int|string $chatbotId): void
    {
        Cache::forget($this->cacheKey($sessionId, $chatbotId));
    }

    private function cacheKey(string $sessionId, int|string $chatbotId): string
    {
        return self::PREFIX . $chatbotId . ':' . $sessionId;
    }
}
