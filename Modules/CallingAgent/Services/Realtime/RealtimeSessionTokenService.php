<?php

namespace Modules\CallingAgent\Services\Realtime;

/**
 * Generates and validates short-lived HMAC-based session tokens for
 * authenticating Twilio Media Stream WebSocket connections.
 *
 * Token format: base64url( sessionId + ':' + timestamp + ':' + hmac )
 *
 * The HMAC is SHA-256 of "sessionId|timestamp" keyed with the application's
 * APP_KEY, so tokens are only valid in the generating environment.
 */
final class RealtimeSessionTokenService
{
    private const ALGO = 'sha256';

    /** Token TTL in seconds (default: 1 hour). */
    private int $ttl;

    public function __construct(int $ttlSeconds = 3600)
    {
        $this->ttl = $ttlSeconds;
    }

    /**
     * Generate a signed token for a given session ID.
     */
    public function generate(string $sessionId): string
    {
        $ts   = time();
        $hmac = $this->hmac($sessionId, $ts);
        return base64_encode($sessionId . '|' . $ts . '|' . $hmac);
    }

    /**
     * Validate a token against the expected session ID.
     * Returns false if the token is malformed, expired, or the HMAC fails.
     */
    public function validate(string $sessionId, string $token): bool
    {
        $decoded = base64_decode($token, strict: true);
        if ($decoded === false) {
            return false;
        }

        $parts = explode('|', $decoded, 3);
        if (count($parts) !== 3) {
            return false;
        }

        [$tokenSessionId, $ts, $tokenHmac] = $parts;

        // Session ID must match
        if (!hash_equals($sessionId, $tokenSessionId)) {
            return false;
        }

        // Must not be expired
        if (time() - (int) $ts > $this->ttl) {
            return false;
        }

        // HMAC must be valid
        $expected = $this->hmac($sessionId, (int) $ts);
        return hash_equals($expected, $tokenHmac);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function hmac(string $sessionId, int $timestamp): string
    {
        $key     = config('app.key', env('APP_KEY', 'base64:changeme'));
        $payload = $sessionId . '|' . $timestamp;
        return hash_hmac(self::ALGO, $payload, $key);
    }
}
