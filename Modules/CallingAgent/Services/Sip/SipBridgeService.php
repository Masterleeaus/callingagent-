<?php
namespace Modules\CallingAgent\Services\Sip;

final class SipBridgeService
{
    public function normalizeUri(string $destination): string
    {
        return str_starts_with($destination, 'sip:') ? $destination : 'sip:' . ltrim($destination);
    }

    public function bridgePlan(string $callSid, string $destination, array $options = []): array
    {
        return ['call_sid' => $callSid, 'sip_uri' => $this->normalizeUri($destination), 'options' => $options, 'mode' => 'sip_bridge'];
    }
}
