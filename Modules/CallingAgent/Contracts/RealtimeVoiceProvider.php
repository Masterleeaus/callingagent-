<?php
namespace Modules\CallingAgent\Contracts;

interface RealtimeVoiceProvider
{
    public function openSession(array $context): array;
    public function receiveMediaFrame(array $frame): array;
    public function closeSession(string $sessionId): array;
}
